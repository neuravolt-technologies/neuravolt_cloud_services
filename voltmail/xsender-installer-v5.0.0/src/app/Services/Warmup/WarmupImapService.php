<?php

namespace App\Services\Warmup;

use App\Models\Gateway;
use App\Models\WarmupDispatch;
use App\Models\WarmupSchedule;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class WarmupImapService
{
    protected WarmupMailboxService $mailboxService;
    protected ?ImapClient $injectedClient;

    public function __construct(
        ?WarmupMailboxService $mailboxService = null,
        $client = null
    ) {
        $this->mailboxService = $mailboxService ?: new WarmupMailboxService();
        $this->injectedClient = $client instanceof ImapClient ? $client : null;
    }

    /**
     * Run IMAP verification across all active schedules that have IMAP configured
     */
    public function verifyActiveSchedules(): array
    {
        $schedules = WarmupSchedule::with('gateway')
            ->where('status', 'active')
            ->get();

        $results = [];
        foreach ($schedules as $schedule) {
            if (!$schedule->gateway || !$this->mailboxService->hasImapConfig($schedule->gateway)) {
                continue;
            }

            try {
                $results[$schedule->id] = $this->verifyDispatchesForSchedule($schedule);
            } catch (Exception $e) {
                Log::warning("Warmup IMAP verification failed for schedule #{$schedule->id}: " . $e->getMessage());
                $results[$schedule->id] = ['error' => $e->getMessage()];
            }
        }

        return $results;
    }

    /**
     * Verify pending dispatches for a specific schedule
     */
    public function verifyDispatchesForSchedule(WarmupSchedule $schedule, ?ImapClient $clientOverride = null): array
    {
        $gateway = $schedule->gateway;
        $imapConfig = $this->mailboxService->getImapConfig($gateway);

        if (!$imapConfig) {
            return ['status' => 'skipped_no_imap', 'verified' => 0];
        }

        $pendingDispatches = WarmupDispatch::where(function($query) use ($schedule, $gateway) {
                $query->where('warmup_schedule_id', $schedule->id)
                      ->orWhere('recipient_email', $gateway->address);
            })
            ->whereIn('status', [WarmupDispatch::STATUS_SENT, WarmupDispatch::STATUS_DELIVERED])
            ->whereNull('verified_at')
            ->latest('sent_at')
            ->limit(30)
            ->get();

        if ($pendingDispatches->isEmpty()) {
            return ['status' => 'no_pending_dispatches', 'verified' => 0];
        }

        // Initialize client
        $client = $clientOverride ?: $this->injectedClient ?: new ImapClient(
            host: $imapConfig['host'],
            port: $imapConfig['port'],
            encryption: $imapConfig['encryption']
        );

        $client->connect();
        $client->login($imapConfig['username'], $imapConfig['password']);

        $verifiedInbox = 0;
        $verifiedSpam  = 0;
        $markedUnknown = 0;

        try {
            // 1. Check INBOX
            $inboxMeta = $client->selectFolder('INBOX');
            $this->validateAndPersistWatermark($schedule, 'INBOX', $inboxMeta['uidvalidity'] ?? null);

            foreach ($pendingDispatches as $dispatch) {
                $uids = $client->searchMessageId($dispatch->message_id);
                if (empty($uids) && !empty($dispatch->subject)) {
                    $uids = $client->searchSubject($dispatch->subject);
                }

                if (!empty($uids)) {
                    $dispatch->status          = WarmupDispatch::STATUS_DELIVERED;
                    $dispatch->inbox_placement = WarmupDispatch::PLACEMENT_INBOX;
                    $dispatch->verified_at     = Carbon::now();
                    $dispatch->save();
                    $verifiedInbox++;

                    // Automate: Mark as Read & Move to Warmup folder (Zero Inbox Clutter)
                    foreach ($uids as $uid) {
                        try {
                            $client->markAsRead($uid);
                            $client->moveToFolder($uid, 'Warmup');
                        } catch (\Exception $e) {
                            Log::info("Could not auto-move UID {$uid} to Warmup folder: " . $e->getMessage());
                        }
                    }

                    // Automate: Send contextual business auto-reply
                    $this->sendAutoReply($dispatch, $gateway);
                }
            }

            // 2. For remaining unverified messages, check Spam folders & RESCUE to Inbox
            $unverified = $pendingDispatches->filter(fn($d) => $d->verified_at === null);
            if ($unverified->isNotEmpty()) {
                $spamFolders = ['[Gmail]/Spam', 'Spam', 'Junk', 'INBOX.Spam', 'INBOX.Junk'];
                foreach ($spamFolders as $folder) {
                    try {
                        $client->selectFolder($folder);
                        foreach ($unverified as $dispatch) {
                            if ($dispatch->verified_at !== null) continue;

                            $uids = $client->searchMessageId($dispatch->message_id);
                            if (empty($uids) && !empty($dispatch->subject)) {
                                $uids = $client->searchSubject($dispatch->subject);
                            }

                            if (!empty($uids)) {
                                $dispatch->status          = WarmupDispatch::STATUS_DELIVERED;
                                $dispatch->inbox_placement = WarmupDispatch::PLACEMENT_SPAM;
                                $dispatch->verified_at     = Carbon::now();
                                $dispatch->save();
                                $verifiedSpam++;

                                // Spam Rescue: Mark read and move out of spam to Warmup
                                foreach ($uids as $uid) {
                                    try {
                                        $client->markAsRead($uid);
                                        $client->moveToFolder($uid, 'Warmup');
                                    } catch (\Exception $e) {
                                        // continue
                                    }
                                }

                                $this->sendAutoReply($dispatch, $gateway);
                            }
                        }
                    } catch (\Exception $e) {
                        continue;
                    }
                }
            }

            // 3. Stale cutoff
            $staleCutoff = Carbon::now()->subHours(24);
            foreach ($pendingDispatches as $dispatch) {
                if ($dispatch->verified_at === null && $dispatch->sent_at && $dispatch->sent_at < $staleCutoff) {
                    $dispatch->inbox_placement = WarmupDispatch::PLACEMENT_UNKNOWN;
                    $dispatch->verified_at     = Carbon::now();
                    $dispatch->save();
                    $markedUnknown++;
                }
            }

        } finally {
            $client->disconnect();
        }

        return [
            'status'         => 'success',
            'verified_inbox' => $verifiedInbox,
            'verified_spam'  => $verifiedSpam,
            'marked_unknown' => $markedUnknown,
            'total_checked'  => $pendingDispatches->count(),
        ];
    }

    /**
     * Validate and record folder UIDVALIDITY watermark.
     * If UIDVALIDITY has changed, resets watermark safely.
     */
    public function validateAndPersistWatermark(WarmupSchedule $schedule, string $folder, ?int $serverUidValidity): bool
    {
        if (!$serverUidValidity) {
            return false;
        }

        $watermark = $schedule->imap_watermark ?? [];
        $storedValidity = Arr::get($watermark, "{$folder}.uidvalidity");

        if ($storedValidity !== null && (int) $storedValidity !== $serverUidValidity) {
            // UIDVALIDITY changed: mailbox was recreated or moved.
            Log::warning("IMAP UIDVALIDITY changed on schedule #{$schedule->id} folder {$folder}: was {$storedValidity}, now {$serverUidValidity}. Resetting watermark.");
            $watermark[$folder] = [
                'uidvalidity' => $serverUidValidity,
                'last_uid'    => 0,
                'reset_at'    => Carbon::now()->toISOString(),
            ];
            $schedule->imap_watermark = $watermark;
            $schedule->save();
            return false; // Watermark was reset
        }

        $watermark[$folder] = [
            'uidvalidity' => $serverUidValidity,
            'updated_at'  => Carbon::now()->toISOString(),
        ];
        $schedule->imap_watermark = $watermark;
        $schedule->save();

        return true;
    }

    /**
     * Send realistic conversational business reply back to sender
     */
    protected function sendAutoReply(WarmupDispatch $dispatch, ?Gateway $recipientGateway = null): void
    {
        $meta = $dispatch->meta_data ?? [];
        if (!empty($meta['auto_replied_at'])) {
            return;
        }

        $senderAddress = $dispatch->gateway?->address;
        if (!$senderAddress) {
            return;
        }

        // Find gateway matching the recipient mailbox to send reply from
        $replyGateway = $recipientGateway;
        if (!$replyGateway || $replyGateway->address !== $dispatch->recipient_email) {
            $replyGateway = Gateway::where('channel', 'email')
                ->where('status', 'active')
                ->where('address', $dispatch->recipient_email)
                ->first();
        }

        if (!$replyGateway) {
            return;
        }

        $dynamic = $this->generateDynamicReply($dispatch->subject);
        $replyText = $dynamic['text'];
        $replySource = $dynamic['source'];
        $replySubject = "Re: " . preg_replace('/^Re:\s*/i', '', $dispatch->subject);

        try {
            $sendMail = new \App\Http\Utility\SendMail();
            $sent = $sendMail->send(
                gateway: $replyGateway,
                to: $senderAddress,
                subject: $replySubject,
                mailBody: "<p>{$replyText}</p>"
            );

            if ($sent) {
                $meta['auto_replied_at'] = Carbon::now()->toIso8601String();
                $meta['reply_source']    = $replySource;
                $dispatch->meta_data     = $meta;
                $dispatch->save();
                Log::info("Sent warmup auto-reply ({$replySource}) from {$replyGateway->address} to {$senderAddress} for dispatch #{$dispatch->id}");
            }
        } catch (\Exception $e) {
            Log::warning("Warmup auto-reply error for dispatch #{$dispatch->id}: " . $e->getMessage());
        }
    }

    /**
     * Generate dynamic AI response or rich spintax reply for warmup emails
     */
    protected function generateDynamicReply(string $subject, ?string $body = null): array
    {
        $cleanSubject = preg_replace('/^Re:\s*/i', '', $subject);
        $aiModels = json_decode((string) site_settings('ai_models'), true) ?: [];
        $aiStatus = Arr::get($aiModels, 'open_ai_text.status', \App\Enums\StatusEnum::FALSE->status());
        $apiKey   = (string) Arr::get($aiModels, 'open_ai_text.key', '');
        $model    = (string) Arr::get($aiModels, 'open_ai_text.model', 'gpt-4o-mini');
        $baseUrl  = (string) Arr::get($aiModels, 'open_ai_text.base_url', '');

        // 1. Try AI Generation if configured in site_settings
        if ($aiStatus != \App\Enums\StatusEnum::FALSE->status() && !empty($apiKey) && $apiKey !== '###') {
            try {
                if (!empty($baseUrl)) {
                    $client = \OpenAI::factory()
                        ->withApiKey($apiKey)
                        ->withBaseUri($baseUrl)
                        ->make();
                } else {
                    $client = \OpenAI::client($apiKey);
                }

                $prompt = "You are a business professional replying to a colleague. The incoming email subject is: \"{$cleanSubject}\". Write a natural, concise 1 to 2 sentence human reply acknowledging receipt, expressing interest, or agreeing to connect. Do NOT include placeholders, quotes, or bracketed text. Keep it completely human and professional.";

                $result = $client->chat()->create([
                    'model'       => $model ?: 'gpt-4o-mini',
                    'messages'    => [
                        ['role' => 'system', 'content' => 'You are a professional email assistant. Output ONLY the email reply text without quotation marks or placeholders.'],
                        ['role' => 'user',   'content' => $prompt],
                    ],
                    'temperature' => 0.7,
                    'max_tokens'  => 80,
                ]);

                $reply = trim((string) Arr::get($result, 'choices.0.message.content', ''));
                if (strlen($reply) >= 15 && strlen($reply) <= 300) {
                    return [
                        'text'   => $reply,
                        'source' => 'ai',
                    ];
                }
            } catch (\Throwable $e) {
                Log::info("Warmup AI reply generation failed, falling back to spintax: " . $e->getMessage());
            }
        }

        // 2. Fallback: Rich Spintax Engine
        $spintaxTemplates = [
            "{Hi|Hello|Hi there}, {thanks for reaching out|appreciate the email|good to hear from you}! {Let me review this with our team and get back to you shortly|We are taking a look at this and will follow up with you soon|Sounds very interesting, let's connect sometime next week|Everything looks good on our end, will keep you posted}. {Best regards|Regards|Thanks again}!",
            "{Thanks for following up|Got your note|Appreciate the quick update}! {I'm currently reviewing the details and will share thoughts shortly|Looks great so far, let me loop in our team|Could you also share a bit more context when you get a chance?} {Warm regards|Best|Have a great day}!",
            "{Hello|Good day}, {thanks for passing this along|received your message}. {I will go through this today and get back to you with any feedback|Let's pencil in a quick sync to review next steps}. {Kind regards|Best wishes}!"
        ];

        $template = $spintaxTemplates[array_rand($spintaxTemplates)];
        $replyText = function_exists('textSpinner') ? (string) textSpinner($template) : preg_replace_callback('/\{([^{}]+)\}/', function ($matches) {
            $options = explode('|', $matches[1]);
            return $options[array_rand($options)];
        }, $template);

        return [
            'text'   => $replyText,
            'source' => 'spintax',
        ];
    }
}
