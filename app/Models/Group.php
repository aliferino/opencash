<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Group extends Model
{
    use HasFactory;

    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'qris_image',
    ];

    /**
     * @return HasMany<User, $this>
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * @return HasMany<GroupSetting, $this>
     */
    public function groupSettings(): HasMany
    {
        return $this->hasMany(GroupSetting::class);
    }

    /**
     * @return HasMany<CashSchedule, $this>
     */
    public function cashSchedules(): HasMany
    {
        return $this->hasMany(CashSchedule::class);
    }

    /**
     * @return HasMany<CashExpense, $this>
     */
    public function cashExpenses(): HasMany
    {
        return $this->hasMany(CashExpense::class);
    }
}