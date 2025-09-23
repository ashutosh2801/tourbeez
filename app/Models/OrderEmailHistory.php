<?php

namespace App\Models;

use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderEmailHistory extends Model
{
    protected $fillable = [
        'order_id', 'to_email', 'from_email', 'subject', 'body', 'status'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}