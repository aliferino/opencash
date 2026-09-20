<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class CashIncome extends Model
{
    use HasFactory;

    /**
     * Ikut dikirim saat model di-serialize ke JSON (dipakai view/JS),
     * supaya URL gambar selalu mengikuti disk yang aktif.
     */
    protected $appends = ['proof_image_url'];

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
     * URL siap pakai untuk bukti pembayaran. Mengikuti driver disk ('public',
     * 's3', dst) sehingga aman dipindah ke object storage tanpa ubah view.
     */
    public function proofImageUrl(): Attribute
    {
        return Attribute::make(
            get: fn (): ?string => $this->proof_image
                ? Storage::disk('public')->url($this->proof_image)
                : null,
        );
    }

    public function cashSchedule(): BelongsTo
    {
        return $this->belongsTo(CashSchedule::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function treasurer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'treasurer_id');
    }
}