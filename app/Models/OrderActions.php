<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderActions extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'order_id',
        'performed_by',
        'notes',
        'created_at',
        'updated_at',
    ];     

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }


}
