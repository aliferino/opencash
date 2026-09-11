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
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'group_id',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * The group (class/kelas) this user belongs to.
     *
     * @return BelongsTo<Group, $this>
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * Audit entries recorded about this user.
     *
     * @return HasMany<UserAudit, $this>
     */
    public function audits(): HasMany
    {
        return $this->hasMany(UserAudit::class);
    }

    /**
     * Audit entries this user performed on other users.
     *
     * @return HasMany<UserAudit, $this>
     */
    public function auditsMade(): HasMany
    {
        return $this->hasMany(UserAudit::class, 'updated_by');
    }

    /**
     * Cash income payments made by this user as a student.
     *
     * @return HasMany<CashIncome, $this>
     */
    public function cashIncomes(): HasMany
    {
        return $this->hasMany(CashIncome::class, 'student_id');
    }

    /**
     * Cash income payments handled by this user as a treasurer.
     *
     * @return HasMany<CashIncome, $this>
     */
    public function handledCashIncomes(): HasMany
    {
        return $this->hasMany(CashIncome::class, 'treasurer_id');
    }

    /**
     * Cash expenses recorded by this user as a treasurer.
     *
     * @return HasMany<CashExpense, $this>
     */
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