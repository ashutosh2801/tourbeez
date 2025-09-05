<?php

namespace App;

use App\Models\Tour;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Upload extends Model
{
    use SoftDeletes;
    
    protected $fillable = [
        'file_original_name', 'file_name', 'user_id', 'extension', 'type', 'file_size',
    ];

    public function user()
    {
    	return $this->belongsTo(User::class);
    }

    public function tours(): BelongsToMany
    {
        return $this->belongsToMany(Tour::class, 'tour_upload', 'upload_id', 'tour_id')
                    ->withPivot('tour_id', 'upload_id')
                    ->withTimestamps();
    }
}
