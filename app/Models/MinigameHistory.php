<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MinigameHistory extends Model
{
    protected $fillable = [
        'minigame_id',
        'account_id',
        'total_score',
        'correct_count',
        'incorrect_count'
    ];

    public function minigame(): BelongsTo
    {
        return $this->belongsTo(Minigame::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
