<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoryFaq extends Model
{
    protected $table = 'category_faq';

    protected $fillable = [
        'category_id',
        'user_id',
        'question',
        'answer'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}