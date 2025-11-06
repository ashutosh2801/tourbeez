<?php

namespace App\Console\Commands;

use App\Traits\TourScheduleHelper;
use Illuminate\Console\Command;

class UpdateTourDisableDate extends Command
{
    use TourScheduleHelper;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-tour-disable-date';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update disabled tour schedule meta for all tours';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $chunkSize = 100;
        $this->info("Processing tours in chunks of {$chunkSize}...");

        \App\Models\Tour::query()
            ->select('id')
            ->orderBy('id')
            ->chunk($chunkSize, function ($tours) {
                foreach ($tours as $tour) {
                    try {
                        $this->updateTourScheduleMeta($tour->id);
                        $this->info("✅ Updated tour ID: {$tour->id}");
                    } catch (\Exception $e) {
                        $this->error("❌ Failed on tour ID {$tour->id}: " . $e->getMessage());
                    }
                }
            });

        $this->info("All tours processed successfully!");
    }
}
