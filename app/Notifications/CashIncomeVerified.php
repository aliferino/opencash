<?php

namespace App\Notifications;

use App\Models\CashIncome;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Dikirim ke siswa saat bendahara memverifikasi pembayaran QRIS-nya.
 */
class CashIncomeVerified extends Notification
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
            'title' => 'Pembayaran diverifikasi',
            'message' => 'Pembayaran kas kamu untuk "'.$this->cashIncome->cashSchedule->description.'" telah diverifikasi.',
            'cash_income_id' => $this->cashIncome->id,
        ];
    }
}