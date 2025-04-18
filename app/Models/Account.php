<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Sanctum\HasApiTokens;

class Account extends Model
{
    use HasApiTokens;
    protected $fillable = [
        'email',
        'password',
        'first_name',
        'middle_name',
        'last_name',
        'user_role',
        'status'
    ];
    protected $hidden = [
        'password'
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
