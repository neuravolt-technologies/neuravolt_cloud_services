<?php

namespace App\Services\System\Communication;

use App\Enums\System\ConversationMessageStatusEnum;
use App\Models\Contact;
use App\Models\Conversation;
use App\Models\Gateway;
use App\Models\User;
use App\Services\Warmup\ImapClient;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

/**
 * v5.0 — Automated IMAP Inbound Sync for Unified Inbox.
 *
 * Connects to IMAP-configured mailboxes, fetches inbound customer/lead replies,
 * and automatically ingests them into Conversations & Messages in the Unified Inbox.
 */
class InboxImapSyncService
{
    protected ChatService $chatService;

    public function __construct(?ChatService $chatService = null)
    {
        $this->chatService = $chatService ?: new ChatService();
    }

    /**
     * Sync unread inbound emails across all active IMAP email gateways
     */
    public function syncAll(): array
    {
        $gateways = Gateway::where('channel', 'email')
            ->where('status', 'active')
            ->whereNotNull('meta_data->imap->host')
            ->get();

        $syncedCount = 0;
        $processedGateways = 0;
        $errors = [];

        foreach ($gateways as $gateway) {
            try {
                $count = $this->syncGateway($gateway);
                $syncedCount += $count;
                $processedGateways++;
            } catch (Exception $e) {
                Log::warning("IMAP sync failed for gateway #{$gateway->id} ({$gateway->address}): " . $e->getMessage());
                $errors[$gateway->id] = $e->getMessage();
            }
        }

        return [
            'gateways_processed' => $processedGateways,
            'messages_synced'    => $syncedCount,
            'errors'             => $errors,
        ];
    }

    /**
     * Sync unread emails for a specific gateway
     */
    public function syncGateway(Gateway $gateway): int
    {
        $imapConfig = Arr::get($gateway->meta_data, 'imap');
        if (empty($imapConfig['host']) || empty($imapConfig['username']) || empty($imapConfig['password'])) {
            return 0;
        }

        $password = $imapConfig['password'];
        try {
            $password = \Illuminate\Support\Facades\Crypt::decryptString($password);
        } catch (\Exception $e) {
            // Already plaintext fallback
        }

        $client = new ImapClient(
            host:       $imapConfig['host'],
            port:       (int) ($imapConfig['port'] ?? 993),
            encryption: $imapConfig['encryption'] ?? 'ssl',
        );

        $client->connect();
        $client->login($imapConfig['username'], $password);
        $client->selectFolder('INBOX');

        // Search for unread messages
        $uids = $client->search('UNSEEN');
        if (empty($uids)) {
            $client->disconnect();
            return 0;
        }

        $synced = 0;
        $user = $gateway->user;

        foreach ($uids as $uid) {
            try {
                $rawHeaders = $client->fetchHeader($uid);
                
                // Skip warmup messages (these belong to WarmupImapService, not customer leads)
                if (stripos($rawHeaders, 'X-Warmup-Id') !== false || stripos($rawHeaders, 'Subject: Re: [Warmup]') !== false) {
                    continue;
                }

                $from = $this->parseFromAddress($rawHeaders);
                if (!$from || strtolower($from) === strtolower($gateway->address)) {
                    continue; // Skip own emails or malformed from
                }

                $subject   = $this->parseHeader($rawHeaders, 'Subject') ?: 'New Email Message';
                $messageId = $this->parseHeader($rawHeaders, 'Message-ID');
                $body      = $client->fetchBodyText($uid) ?: 'Email reply received.';

                // Resolve or create contact for this sender
                $contact = Contact::where('email_contact', $from)
                    ->when($user, fn($q) => $q->where('user_id', $user->id))
                    ->first();

                if (!$contact && $user) {
                    $fromName = $this->parseFromName($rawHeaders) ?: explode('@', $from)[0];
                    $contact = Contact::create([
                        'user_id'            => $user->id,
                        'email_contact'      => $from,
                        'first_name'         => $fromName,
                        'status'             => \App\Enums\Common\Status::ACTIVE->value,
                        'email_verification' => 'verified',
                    ]);
                }

                if (!$contact) {
                    continue;
                }

                // Ingest into Conversation & Message in Unified Inbox
                $conversation = $this->chatService->createOrUpdateConversation(
                    contact:    $contact,
                    channel:    'email',
                    gateway_id: $gateway->id,
                    metaData:   ['subject' => $subject, 'last_channel' => 'email'],
                    user:       $user
                );

                $msg = $this->chatService->createMessage(
                    conversation:   $conversation,
                    messageContent: $body,
                    type:           'email',
                    user:           null // Inbound from contact
                );

                $this->chatService->createMessageParticipants(
                    message:        $msg,
                    senderId:       $contact->id,
                    senderType:     Contact::class,
                    receiverId:     $user?->id ?: 0,
                    receiverType:   User::class
                );

                $this->chatService->createMessageStatus(
                    message:  $msg,
                    status:   ConversationMessageStatusEnum::DELIVERED,
                    additionalData: ['message_id' => $messageId]
                );

                $this->chatService->updateConversation($conversation);

                // Mark seen so we don't ingest again
                $client->markAsRead($uid);
                $synced++;

            } catch (Exception $e) {
                Log::info("Could not process IMAP message UID {$uid}: " . $e->getMessage());
            }
        }

        $client->disconnect();
        return $synced;
    }

    protected function parseFromAddress(string $headers): ?string
    {
        if (preg_match('/From:[^<]*<([^>]+)>/i', $headers, $m)) {
            return trim($m[1]);
        }
        if (preg_match('/From:\s*([^\r\n]+)/i', $headers, $m)) {
            $addr = trim($m[1]);
            return filter_var($addr, FILTER_VALIDATE_EMAIL) ? $addr : null;
        }
        return null;
    }

    protected function parseFromName(string $headers): ?string
    {
        if (preg_match('/From:\s*"?([^"<]+)"?\s*</i', $headers, $m)) {
            return trim($m[1]);
        }
        return null;
    }

    protected function parseHeader(string $headers, string $headerName): ?string
    {
        if (preg_match('/' . preg_quote($headerName, '/') . ':\s*([^\r\n]+)/i', $headers, $m)) {
            return trim($m[1]);
        }
        return null;
    }
}
