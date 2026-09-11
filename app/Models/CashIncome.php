<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashIncome extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'cash_schedule_id',
        'student_id',
        'treasurer_id',
        'amount_paid',
        'fine_paid',
        'income_date',
        'payment_method',
        'proof_image',
        'notes',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'amount_paid' => 'integer',
            'fine_paid' => 'integer',
            'income_date' => 'date',
        ];
    }

    /**
     * @return BelongsTo<CashSchedule, $this>
     */
    public function cashSchedule(): BelongsTo
    {
        return $this->belongsTo(CashSchedule::class);
    }

    /**
     * The student who made the payment.
     *
     * @return BelongsTo<User, $this>
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /**
     * The treasurer who verified/recorded the payment.
     *
     * @return BelongsTo<User, $this>
     */
    public function treasurer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'treasurer_id');
    }
}