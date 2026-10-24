<?php

namespace App\Console\Commands;

use App\Services\System\Communication\InboxImapSyncService;
use Illuminate\Console\Command;

class InboxImapSyncCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inbox:sync-imap';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch inbound customer lead emails from IMAP-enabled mailboxes into Unified Inbox';

    /**
     * Execute the console command.
     */
    public function handle(InboxImapSyncService $syncService): int
    {
        $this->info('Starting Unified Inbox IMAP synchronization...');
        
        $result = $syncService->syncAll();

        $this->info("Gateways processed: {$result['gateways_processed']}");
        $this->info("Inbound messages synced: {$result['messages_synced']}");

        if (!empty($result['errors'])) {
            $this->warn('Errors encountered on some gateways:');
            foreach ($result['errors'] as $id => $err) {
                $this->warn("Gateway #{$id}: {$err}");
            }
        }

        return Command::SUCCESS;
    }
}
