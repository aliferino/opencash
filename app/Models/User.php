<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'group_id',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function audits(): HasMany
    {
        return $this->hasMany(UserAudit::class);
    }

    public function auditsMade(): HasMany
    {
        return $this->hasMany(UserAudit::class, 'updated_by');
    }

    public function cashIncomes(): HasMany
    {
        return $this->hasMany(CashIncome::class, 'student_id');
    }

    public function handledCashIncomes(): HasMany
    {
        return $this->hasMany(CashIncome::class, 'treasurer_id');
    }

    public function cashExpenses(): HasMany
    {
        return $this->hasMany(CashExpense::class, 'treasurer_id');
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isTreasurer(): bool
    {
        return $this->role === 'treasurer';
    }

    public function isStudent(): bool
    {
        return $this->role === 'student';
    }
}