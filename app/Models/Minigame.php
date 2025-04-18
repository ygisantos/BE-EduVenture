<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Minigame extends Model
{
    protected $fillable = [
        'title',
        'default_timer',
        'default_points',
        'starts_at',
        'account_id'
    ];

    protected $casts = [
        'starts_at' => 'datetime'
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function contents(): HasMany
    {
        return $this->hasMany(MinigameContent::class);
    }

    public function histories(): HasMany
    {
        return $this->hasMany(MinigameHistory::class);
    }
}
