<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GroupSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_id',
        'period_id',
        'cash_amount',
        'fine_amount',
        'qris_image',
    ];

    protected function casts(): array
    {
        return [
            'cash_amount' => 'integer',
            'fine_amount' => 'integer',
        ];
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(Period::class);
    }
}