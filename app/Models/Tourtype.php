<?php

namespace App\Models;

use App\Models\Scopes\SupplierScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tourtype extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'slug'
    ];

    protected static function booted()
    {
        static::addGlobalScope(new SupplierScope('user_id'));
    }


    public function tours(): BelongsToMany
    {
        return $this->belongsToMany(Tour::class);
    }
}
