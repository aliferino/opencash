<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

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

    /**
     * Ikut dikirim saat model di-serialize ke JSON (dipakai view/JS),
     * supaya URL gambar selalu mengikuti disk yang aktif.
     */
    protected $appends = ['proof_image_url'];

    /**
     * URL siap pakai untuk foto nota. Mengikuti driver disk ('public', 's3',
     * dst) sehingga aman dipindah ke object storage tanpa ubah view.
     */
    public function proofImageUrl(): Attribute
    {
        return Attribute::make(
            get: fn (): ?string => $this->proof_image
                ? Storage::disk('public')->url($this->proof_image)
                : null,
        );
    }

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