<?php

namespace App;

use App\Models\Scopes\SupplierScope;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Upload extends Model
{
    use SoftDeletes;

    
    protected $fillable = [
        'file_original_name', 'file_name', 'user_id', 'extension', 'type', 'file_size',
    ];

    protected static function booted()
    {
        static::addGlobalScope(new SupplierScope('user_id'));
    }

    public function user()
    {
    	return $this->belongsTo(User::class);
    }
}
