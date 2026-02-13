<?php

namespace App\Console\Commands;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Console\Command;

class SyncAdminToBranches extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'branches:sync-admin
                            {--dry-run : Show what would be done without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Connect all admin users to all already saved branches (branch_user table). Run this to give admin role access to existing branches.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->warn('Running in DRY-RUN mode. No changes will be saved.');
        }

        $admins = User::where('role', 'admin')->get();
        $branches = Branch::all();

        if ($admins->isEmpty()) {
            $this->warn('No admin users found. Create users with role=admin first.');
            return self::FAILURE;
        }

        if ($branches->isEmpty()) {
            $this->warn('No branches found. Create branches first.');
            return self::FAILURE;
        }

        $this->info('Found ' . $admins->count() . ' admin(s) and ' . $branches->count() . ' branch(es).');
        $this->newLine();

        $attached = 0;
        $skipped = 0;

        foreach ($admins as $admin) {
            foreach ($branches as $branch) {
                $already = $branch->users()->where('user_id', $admin->id)->exists();

                if ($already) {
                    $skipped++;
                    $this->line("  [skip] Admin \"{$admin->name}\" already linked to \"{$branch->branch_name}\"");
                    continue;
                }

                if (!$dryRun) {
                    $branch->users()->attach($admin->id, ['role' => 'admin']);
                }
                $attached++;
                $this->info("  [attach] Admin \"{$admin->name}\" -> \"{$branch->branch_name}\" (role: admin)");
            }
        }

        $this->newLine();
        if ($dryRun) {
            $this->info("Dry-run: would attach {$attached} link(s), skip {$skipped} existing.");
        } else {
            $this->info("Done. Attached: {$attached}, Skipped (already linked): {$skipped}.");
        }

        return self::SUCCESS;
    }
}
