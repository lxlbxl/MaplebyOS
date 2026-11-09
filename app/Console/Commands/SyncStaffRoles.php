<?php

namespace App\Console\Commands;

use App\Support\Access;
use Illuminate\Console\Command;

class SyncStaffRoles extends Command
{
    protected $signature = 'staff:sync-roles';

    protected $description = 'Create or refresh the staff roles and permissions defined in App\Support\Access';

    public function handle(): int
    {
        Access::syncRoles();
        $this->info('Staff roles synced: ' . implode(', ', array_keys(Access::ROLE_LABELS)));

        return self::SUCCESS;
    }
}
