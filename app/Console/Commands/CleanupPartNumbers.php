<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PartNumber;
use App\Models\Item;
use App\Models\VehicalType;
use Illuminate\Support\Facades\DB;

class CleanupPartNumbers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'part-numbers:cleanup {--dry-run : Run without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up duplicate and invalid part numbers in the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->info('🔍 DRY RUN MODE - No changes will be made');
        }

        $this->info('Starting part numbers cleanup...');
        $this->newLine();

        DB::beginTransaction();
        try {
            $stats = [
                'cleaned' => 0,
                'merged' => 0,
                'deleted' => 0,
                'invalid' => 0,
            ];

            // Get all part numbers
            $partNumbers = PartNumber::all();
            $total = $partNumbers->count();

            $this->info("Found {$total} part numbers to process");
            $this->newLine();

            $bar = $this->output->createProgressBar($total);
            $bar->start();

            foreach ($partNumbers as $partNumber) {
                // Clean the name
                $cleanedName = $this->cleanPartNumberName($partNumber->name);

                // Check if name is invalid
                if (trim($cleanedName) === '' || strlen(trim($cleanedName)) < 2) {
                    $itemsCount = Item::where('part_number_id', $partNumber->id)->count();
                    $vehiclesCount = VehicalType::where('v_part_number_id', $partNumber->id)->count();

                    if ($itemsCount === 0 && $vehiclesCount === 0) {
                        if (!$dryRun) {
                            $partNumber->delete();
                        }
                        $stats['deleted']++;
                        $stats['invalid']++;
                    } else {
                        $this->warn("\n⚠️  Invalid part number #{$partNumber->id} '{$partNumber->name}' is still in use and cannot be deleted");
                    }
                    $bar->advance();
                    continue;
                }

                // If name changed, check for duplicates
                if ($cleanedName !== $partNumber->name) {
                    $existing = PartNumber::where('name', $cleanedName)
                        ->where('id', '!=', $partNumber->id)
                        ->first();

                    if ($existing) {
                        // Merge: Move all references to the existing one
                        if (!$dryRun) {
                            Item::where('part_number_id', $partNumber->id)
                                ->update(['part_number_id' => $existing->id]);
                            
                            VehicalType::where('v_part_number_id', $partNumber->id)
                                ->update(['v_part_number_id' => $existing->id]);

                            $partNumber->delete();
                        }
                        $stats['merged']++;
                        $this->line("\n🔄 Merged '{$partNumber->name}' into '{$cleanedName}'");
                    } else {
                        // Just update the name
                        if (!$dryRun) {
                            $partNumber->update(['name' => $cleanedName]);
                        }
                        $stats['cleaned']++;
                    }
                }

                $bar->advance();
            }

            $bar->finish();
            $this->newLine(2);

            if ($dryRun) {
                DB::rollBack();
                $this->info('📊 DRY RUN RESULTS:');
            } else {
                DB::commit();
                $this->info('✅ Cleanup completed successfully!');
                $this->info('📊 RESULTS:');
            }

            $this->table(
                ['Action', 'Count'],
                [
                    ['Cleaned names', $stats['cleaned']],
                    ['Merged duplicates', $stats['merged']],
                    ['Deleted invalid', $stats['deleted']],
                    ['Total processed', $total],
                ]
            );

            return Command::SUCCESS;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('❌ Cleanup failed: ' . $e->getMessage());
            $this->error($e->getTraceAsString());
            return Command::FAILURE;
        }
    }

    /**
     * Clean part number name
     */
    private function cleanPartNumberName($name)
    {
        // Trim whitespace
        $name = trim($name);
        
        // Remove extra spaces
        $name = preg_replace('/\s+/', ' ', $name);
        
        // Remove special characters that shouldn't be in part numbers (keep alphanumeric, spaces, hyphens, underscores)
        $name = preg_replace('/[^a-zA-Z0-9\s\-_]/', '', $name);
        
        // Trim again
        $name = trim($name);
        
        return $name;
    }
}
