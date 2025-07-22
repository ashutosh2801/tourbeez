<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Order extends Model
{
    use HasFactory, SoftDeletes;
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
        ->useLogName('Orders')
        ->setDescriptionForEvent(fn(string $eventName) => "This model has been {$eventName}")
        ->logOnly(['*'])
        ->logOnlyDirty()
        ->dontSubmitEmptyLogs();
    }

    protected $fillable = [
        'tour_id', 'user_id', 'session_id', 'order_number', 'tour_date', 'tour_time', 'number_of_guests', 'payment_status', 
        'payment_method', 'total_amount', 'currency', 'order_status'
    ];

    public function tour_detail($id, $label='all') {
        $tour = Tour::where('id', $id)->first();
        if($label == 'all')
        return $tour;

        return $tour->$label;
    }

    public function orderTours()
    {
        return $this->hasMany(OrderTour::class);
    }

    public function tour() {
        return $this->belongsTo(Tour::class);
    }

    public function user() {
        return $this->belongsTo(user::class);
    }


    public function getStatusAttribute()
    {

        $val = $this->order_status;

        switch($val) {
            case 1:
                return 'New';
                break;
            case 2:
                return 'On Hold';
                break;
            case 3:
                return 'Pending supplier';
                break; 
            case 4:
                return 'Pending customer';
                break;
            case 5:
                return 'Confirmed';
                break;
            case 6:
                return 'Cancelled';   
                break;  
            case 7:
                return 'Abandoned cart';   
                break; 
            default:
                return 'Cancelled';   
                break;   
        }
    }
}
