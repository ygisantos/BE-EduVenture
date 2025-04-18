<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends Model
{
    protected $fillable = [
        'email',
        'password',
        'first_name',
        'middle_name',
        'last_name',
        'user_role',
        'status'
    ];

    public function books(): HasMany
    {
        return $this->hasMany(Book::class);
    }

    public function minigames(): HasMany
    {
        return $this->hasMany(Minigame::class);
    }

    public function minigameHistories(): HasMany
    {
        return $this->hasMany(MinigameHistory::class);
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }
}
