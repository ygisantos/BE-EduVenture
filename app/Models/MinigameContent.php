<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MinigameContent extends Model
{
    protected $fillable = [
        'minigame_id',
        'question',
        'correct_answer',
        'option_1',
        'option_2',
        'option_3',
        'option_4',
        'points',
        'timer',
        'account_id'
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
