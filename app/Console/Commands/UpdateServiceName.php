<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CarWashService;
use App\Models\CarWashJob;
use Illuminate\Support\Facades\DB;

class UpdateServiceName extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'service:update-name {old_name} {new_name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update service name in database (services and jobs)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $oldName = $this->argument('old_name');
        $newName = strtoupper($this->argument('new_name'));
        
        $updatedServices = 0;
        $updatedJobs = 0;
        
        // Update in services table (case-insensitive, partial match)
        $services = CarWashService::whereRaw('LOWER(label) = ?', [strtolower($oldName)])
            ->orWhereRaw('LOWER(label) LIKE ?', ['%' . strtolower($oldName) . '%'])
            ->get();
        
        foreach ($services as $service) {
            $originalLabel = $service->label;
            $service->update(['label' => $newName]);
            $updatedServices++;
            $this->line("Updated service ID {$service->id}: '{$originalLabel}' -> '{$newName}'");
        }
        
        // Update in jobs table (case-insensitive, partial match)
        $jobs = CarWashJob::whereRaw('LOWER(service_name) = ?', [strtolower($oldName)])
            ->orWhereRaw('LOWER(service_name) LIKE ?', ['%' . strtolower($oldName) . '%'])
            ->get();
        
        foreach ($jobs as $job) {
            $job->update(['service_name' => $newName]);
            $updatedJobs++;
        }
        
        if ($updatedServices > 0 || $updatedJobs > 0) {
            $this->info("Successfully updated {$updatedServices} service(s) and {$updatedJobs} job(s)");
        } else {
            $this->warn("No records found with name containing '{$oldName}'");
            $this->line("\nAvailable services:");
            CarWashService::select('id', 'label')->get()->each(function($s) {
                $this->line("  ID {$s->id}: {$s->label}");
            });
        }
        
        return 0;
    }
}
