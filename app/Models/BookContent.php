<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookContent extends Model
{
    protected $fillable = [
        'book_id',
        'content',
        'title',
        'page_number'
    ];

    protected $casts = [
        'content' => 'string', // Ensure content is treated as a string
    ];

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }
}
