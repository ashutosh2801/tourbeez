<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;


class ManifestExport implements FromView
{
    public $sessions;
    public $date;
    public $type;

    public function __construct($sessions, $date, $type = 'order')
    {
        $this->sessions = $sessions;
        $this->date = $date;
        $this->type = $type;
    }

    public function view(): View
    {

        if($this->type == 'tour'){
            return view('admin.order.manifest_tour_excel', [
                'sessions' => $this->sessions,
                'date' => $this->date
            ]);
        }
        return view('admin.order.manifest_excel', [
            'sessions' => $this->sessions,
            'date' => $this->date
        ]);
    }
}

