<?php

namespace App\Models;

use App\Models\Scopes\SupplierScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'user_id'
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
