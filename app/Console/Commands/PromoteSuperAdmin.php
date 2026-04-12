<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class PromoteSuperAdmin extends Command
{
    protected $signature = 'admin:promote {email}';

    protected $description = 'Promote a user to super admin';

    public function handle(): int
    {
        $user = User::where('email', $this->argument('email'))->first();

        if (! $user) {
            $this->error("User not found: {$this->argument('email')}");

            return self::FAILURE;
        }

        $user->update(['is_super_admin' => true]);

        $this->info("✓ {$user->name} ({$user->email}) is now a super admin.");

        return self::SUCCESS;
    }
}
