<?php

namespace App\Imports;

use App\Imports\OrdersImport;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class OrderMultiSheetImport implements WithMultipleSheets
{
    public array $instances = [];

    /**
     * This method is called once.
     * Laravel Excel automatically loops through ALL sheets.
     */
    public function sheets(): array
    {
        return [
            '*' => function ($sheet) {

                // Create new import instance for each sheet
                $instance = new OrdersImport();

                // Store sheet name (example: Nov - 2025)
                $instance->sheetName = $sheet->getTitle();

                // Save instance so we can calculate summary later
                $this->instances[] = $instance;

                return $instance;
            },
        ];
    }

    /**
     * After import, merge results from all sheets
     */
    public function getSummary(): array
    {
        $summary = [
            'total'    => 0,
            'imported' => 0,
            'skipped'  => 0,
            'failed'   => 0,
            'errors'   => [],
        ];

        foreach ($this->instances as $sheet) {

            $summary['total']    += $sheet->total ?? 0;
            $summary['imported'] += $sheet->imported ?? 0;
            $summary['skipped']  += $sheet->skipped ?? 0;
            $summary['failed']   += $sheet->failed ?? 0;

            foreach ($sheet->failReasons ?? [] as $error) {
                $error['sheet'] = $sheet->sheetName ?? 'Unknown';
                $summary['errors'][] = $error;
            }
        }

        return $summary;
    }
}
