<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Group extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'invite_code',
        'qris_image',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function cashSchedules(): HasMany
    {
        return $this->hasMany(CashSchedule::class);
    }

    public function cashExpenses(): HasMany
    {
        return $this->hasMany(CashExpense::class);
    }
}