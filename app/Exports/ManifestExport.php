<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class ManifestExport implements FromView
{
    public $sessions;
    public $date;

    public function __construct($sessions, $date)
    {
        $this->sessions = $sessions;
        $this->date = $date;
    }

    public function view(): View
    {
        return view('admin.order.manifest_excel', [
            'sessions' => $this->sessions,
            'date' => $this->date
        ]);
    }
}

