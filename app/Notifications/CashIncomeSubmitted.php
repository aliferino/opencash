<?php

namespace App\Notifications;

use App\Models\CashIncome;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Dikirim ke semua bendahara satu kelas saat siswa mengunggah bukti QRIS.
 */
class CashIncomeSubmitted extends Notification
{
    use Queueable;

    public function __construct(public CashIncome $cashIncome) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Bukti pembayaran QRIS baru',
            'message' => $this->cashIncome->student->name.' mengunggah bukti pembayaran kas.',
            'cash_income_id' => $this->cashIncome->id,
        ];
    }
}