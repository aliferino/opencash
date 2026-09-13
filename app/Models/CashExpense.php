<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashExpense extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_id',
        'treasurer_id',
        'amount',
        'expense_date',
        'description',
        'proof_image',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'expense_date' => 'date',
        ];
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function treasurer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'treasurer_id');
    }
}