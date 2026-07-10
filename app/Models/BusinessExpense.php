<?php

namespace App\Models;

use App\Models\Partner;
use App\Models\Tour;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusinessExpense extends Model
{
    use HasFactory;

    protected $fillable = [
        'expense_date',
        'category',
        'amount',
        'tour_id',
        'partner_id',
        'vendor',
        'description',
        'created_by',
    ];

    public function tour()
    {
        return $this->belongsTo(Tour::class);
    }

    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class,'created_by');
    }
}
