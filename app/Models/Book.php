<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Book extends Model
{
    protected $fillable = [
        'title',
        'description',
        'status',
        'account_id',
        'deleted_at'
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function contents(): HasMany
    {
        return $this->hasMany(BookContent::class);
    }
}
