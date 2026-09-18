@extends('layouts.panel')

@section('title', 'Laporan Kas — OpenCash')

@section('panel')
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-ink">Laporan Kas</h1>
            <p class="mt-1 text-[15px] text-muted">Ringkasan kondisi kas kelas Anda beserta rincian arus kasnya.</p>
        </div>

        <div class="flex items-center gap-2">
            <button
                type="button"
                disabled
                title="Export PDF belum aktif"
                class="flex cursor-not-allowed items-center gap-2 rounded-md border border-line px-4 py-2.5 text-[14px] font-medium text-muted opacity-50"
            >
                <i data-lucide="file-text" class="h-4 w-4" stroke-width="1.8"></i>
                Export PDF
            </button>
            <button
                type="button"
                disabled
                title="Export Excel belum aktif"
                class="flex cursor-not-allowed items-center gap-2 rounded-md border border-line px-4 py-2.5 text-[14px] font-medium text-muted opacity-50"
            >
                <i data-lucide="table" class="h-4 w-4" stroke-width="1.8"></i>
                Export Excel
            </button>
        </div>
    </div>

    <div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-2xl border border-line bg-surface p-6">
            <span class="flex h-9 w-9 items-center justify-center rounded-full bg-accent-tint text-accent-bright">
                <i data-lucide="wallet" class="h-4 w-4" stroke-width="1.8"></i>
            </span>
            <p class="mt-4 text-[13px] text-muted">Saldo Kas</p>
            <p class="mt-1 text-3xl font-semibold text-ink">Rp{{ number_format($summary['balance'], 0, ',', '.') }}</p>
        </div>

        <div class="rounded-2xl border border-line bg-surface p-6">
            <span class="flex h-9 w-9 items-center justify-center rounded-full bg-emerald-500/15 text-emerald-300">
                <i data-lucide="arrow-down-circle" class="h-4 w-4" stroke-width="1.8"></i>
            </span>
            <p class="mt-4 text-[13px] text-muted">Total Pemasukan</p>
            <p class="mt-1 text-3xl font-semibold text-ink">Rp{{ number_format($summary['total_income'], 0, ',', '.') }}</p>
        </div>

        <div class="rounded-2xl border border-line bg-surface p-6">
            <span class="flex h-9 w-9 items-center justify-center rounded-full bg-red-500/15 text-red-300">
                <i data-lucide="arrow-up-circle" class="h-4 w-4" stroke-width="1.8"></i>
            </span>
            <p class="mt-4 text-[13px] text-muted">Total Pengeluaran</p>
            <p class="mt-1 text-3xl font-semibold text-ink">Rp{{ number_format($summary['total_expense'], 0, ',', '.') }}</p>
        </div>

        <div class="rounded-2xl border border-line bg-surface p-6">
            <span class="flex h-9 w-9 items-center justify-center rounded-full bg-amber-500/15 text-amber-300">
                <i data-lucide="badge-alert" class="h-4 w-4" stroke-width="1.8"></i>
            </span>
            <p class="mt-4 text-[13px] text-muted">Menunggu Verifikasi</p>
            <p class="mt-1 text-3xl font-semibold text-ink">{{ $summary['pending_count'] }}</p>
            <p class="mt-1 text-xs text-muted">Rp{{ number_format($summary['pending_amount'], 0, ',', '.') }} belum masuk saldo</p>
        </div>
    </div>

    <div class="mt-8 grid gap-6 lg:grid-cols-3">
        <div class="rounded-2xl border border-line bg-surface p-6">
            <h2 class="text-lg font-semibold text-ink">Partisipasi Pembayaran</h2>

            @php
                $totalStudents = max($summary['students_count'], 0);
                $paidStudents = $summary['paid_students_count'];
                $paidPercent = $totalStudents > 0 ? round(($paidStudents / $totalStudents) * 100) : 0;
            @endphp

            <p class="mt-4 text-3xl font-semibold text-ink">{{ $paidStudents }}<span class="text-lg text-muted"> / {{ $totalStudents }}</span></p>
            <p class="mt-1 text-[13px] text-muted">siswa sudah pernah membayar kas</p>

            <div class="mt-4 h-2 w-full overflow-hidden rounded-full bg-white/5">
                <div class="h-full rounded-full bg-accent" style="width: {{ $paidPercent }}%"></div>
            </div>
            <p class="mt-2 text-xs text-muted">{{ $paidPercent }}% siswa sudah membayar</p>

            <div class="mt-5 space-y-1 border-t border-line pt-4 text-[13px]">
                <p class="flex items-center justify-between text-muted">
                    <span>Belum pernah bayar</span>
                    <span class="font-medium text-ink">{{ $summary['unpaid_students_count'] }} siswa</span>
                </p>
                <p class="flex items-center justify-between text-muted">
                    <span>Jumlah tagihan</span>
                    <span class="font-medium text-ink">{{ $summary['schedules_count'] }}</span>
                </p>
                <p class="flex items-center justify-between text-muted">
                    <span>Jumlah pengeluaran</span>
                    <span class="font-medium text-ink">{{ $summary['expenses_count'] }}</span>
                </p>
            </div>
        </div>

        <div class="rounded-2xl border border-line bg-surface p-6">
            <h2 class="text-lg font-semibold text-ink">Siswa Teratas</h2>
            <p class="mt-1 text-[13px] text-muted">Total kas terbanyak yang sudah terverifikasi.</p>

            <div class="mt-5 space-y-4">
                @forelse ($topStudents as $index => $row)
                    <div class="flex items-center gap-3">
                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-accent-tint text-[12px] font-semibold text-accent-bright">{{ $index + 1 }}</span>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center justify-between gap-2">
                                <p class="truncate text-[14px] font-medium text-ink">{{ $row->student?->name ?? 'Siswa terhapus' }}</p>
                                <span class="shrink-0 text-[14px] text-ink">Rp{{ number_format($row->total_paid, 0, ',', '.') }}</span>
                            </div>
                            <p class="text-xs text-muted">{{ $row->payments_count }} pembayaran</p>
                        </div>
                    </div>
                @empty
                    <p class="py-6 text-center text-[14px] text-muted">Belum ada pembayaran terverifikasi.</p>
                @endforelse
            </div>
        </div>

        <div class="rounded-2xl border border-line bg-surface p-6">
            <h2 class="text-lg font-semibold text-ink">Aktivitas Terbaru</h2>
            <p class="mt-1 text-[13px] text-muted">Pemasukan &amp; pengeluaran terakhir.</p>

            <div class="mt-5 space-y-4">
                @forelse ($recent as $row)
                    <div class="flex items-start gap-3">
                        <span class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-full {{ $row['type'] === 'income' ? 'bg-emerald-500/15 text-emerald-300' : 'bg-red-500/15 text-red-300' }}">
                            <i data-lucide="{{ $row['type'] === 'income' ? 'arrow-down' : 'arrow-up' }}" class="h-3.5 w-3.5" stroke-width="2"></i>
                        </span>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center justify-between gap-2">
                                <p class="truncate text-[14px] font-medium text-ink">{{ $row['title'] }}</p>
                                <span class="shrink-0 text-[14px] {{ $row['type'] === 'income' ? 'text-emerald-300' : 'text-red-300' }}">
                                    {{ $row['type'] === 'income' ? '+' : '−' }}Rp{{ number_format($row['amount'], 0, ',', '.') }}
                                </span>
                            </div>
                            <p class="truncate text-xs text-muted">{{ $row['subtitle'] }} · {{ optional($row['date'])->translatedFormat('d M Y') }}</p>
                        </div>
                    </div>
                @empty
                    <p class="py-6 text-center text-[14px] text-muted">Belum ada aktivitas kas.</p>
                @endforelse
            </div>
        </div>
    </div>

    @include('treasurer.reports._table')
@endsection
