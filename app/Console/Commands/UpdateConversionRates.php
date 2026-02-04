<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class UpdateConversionRates extends Command
{
    protected $signature = 'update:conversion-rates';
    protected $description = 'Fetch and update currency conversion rates JSON file';

    public function handle()
    {
        $this->info('Fetching conversion rates...');

        try {
            // Fetch data from API
            $response = Http::get('https://v6.exchangerate-api.com/v6/707643cafec57edfd7f224bd/latest/USD');

            if ($response->successful()) {
                $data = $response->json();

                // Save only the conversion_rates part
                if (isset($data['conversion_rates'])) {
                    $filePath = public_path('data/conversion_rates.json');
                    file_put_contents(
                        $filePath,
                        json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
                    );

                    $this->info('Conversion rates updated successfully ✅');
                }else {
                    $this->error('Invalid response structure.');
                }
            } else {
                $this->error('Failed to fetch data. HTTP Status: ' . $response->status());
            }
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
        }

        return Command::SUCCESS;
    }
}
