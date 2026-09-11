<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Period extends Model
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
        'interval_days',
    ];

    /**
     * @return HasMany<GroupSetting, $this>
     */
    public function groupSettings(): HasMany
    {
        return $this->hasMany(GroupSetting::class);
    }
}