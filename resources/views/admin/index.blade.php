@extends('layouts.panel')

@section('title', 'Dashboard Admin — OpenCash')

@section('panel')
    <div class="mb-8">
        <h1 class="text-2xl font-semibold tracking-tight text-ink">Dashboard</h1>
        <p class="mt-1 text-[15px] text-muted">Ringkasan seluruh kas di OpenCash.</p>
    </div>

    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
        <div class="rounded-2xl border border-line bg-surface p-6">
            <span class="flex h-9 w-9 items-center justify-center rounded-full bg-accent-tint text-accent-bright">
                <i data-lucide="building-2" class="h-4 w-4" stroke-width="1.8"></i>
            </span>
            <p class="mt-4 text-[13px] text-muted">Total Grup</p>
            <p class="mt-1 text-3xl font-semibold text-ink">{{ $totalGroups }}</p>
        </div>

        <div class="rounded-2xl border border-line bg-surface p-6">
            <span class="flex h-9 w-9 items-center justify-center rounded-full bg-accent-tint text-accent-bright">
                <i data-lucide="users" class="h-4 w-4" stroke-width="1.8"></i>
            </span>
            <p class="mt-4 text-[13px] text-muted">Bendahara</p>
            <p class="mt-1 text-3xl font-semibold text-ink">{{ $usersByRole['treasurer'] ?? 0 }}</p>
        </div>

        <div class="rounded-2xl border border-line bg-surface p-6">
            <span class="flex h-9 w-9 items-center justify-center rounded-full bg-accent-tint text-accent-bright">
                <i data-lucide="users" class="h-4 w-4" stroke-width="1.8"></i>
            </span>
            <p class="mt-4 text-[13px] text-muted">Siswa</p>
            <p class="mt-1 text-3xl font-semibold text-ink">{{ $usersByRole['student'] ?? 0 }}</p>
        </div>
    </div>

    <div class="mt-8 grid gap-6 lg:grid-cols-2">
        <div class="rounded-2xl border border-line bg-surface p-6">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-ink">Grup terbaru</h2>
                <a href="{{ route('admin.groups.index') }}" class="text-[14px] font-medium text-accent-bright transition-colors hover:text-accent">Lihat semua →</a>
            </div>

            <div class="mt-5 space-y-1">
                @forelse ($recentGroups as $group)
                    <div class="flex items-center justify-between rounded-md px-3 py-3 transition-colors hover:bg-white/5">
                        <div class="min-w-0">
                            <p class="truncate text-[15px] font-medium text-ink">{{ $group->name }}</p>
                            <p class="text-xs text-muted">Kode undangan: {{ $group->invite_code }}</p>
                        </div>
                        <span class="shrink-0 rounded-full bg-accent-tint px-2.5 py-1 text-xs font-medium text-accent-bright">{{ $group->users_count }} anggota</span>
                    </div>
                @empty
                    <p class="py-6 text-center text-[14px] text-muted">Belum ada grup.</p>
                @endforelse
            </div>
        </div>

        <div class="rounded-2xl border border-line bg-surface p-6">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-ink">Aktivitas terbaru</h2>
                <a href="{{ route('admin.audits.index') }}" class="text-[14px] font-medium text-accent-bright transition-colors hover:text-accent">Lihat semua →</a>
            </div>

            <div class="mt-5 space-y-4">
                @forelse ($recentAudits as $audit)
                    <div class="flex gap-3">
                        <span class="mt-1 flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-accent-tint text-accent-bright">
                            <i data-lucide="history" class="h-3.5 w-3.5" stroke-width="1.8"></i>
                        </span>
                        <div class="min-w-0">
                            <p class="text-[14px] text-ink">
                                <span class="font-medium">{{ $audit->updatedBy?->name ?? 'Sistem' }}</span>
                                {{ str_replace('_', ' ', $audit->action) }}
                                <span class="font-medium">{{ $audit->user?->name }}</span>
                            </p>
                            <p class="text-xs text-muted">{{ $audit->created_at?->diffForHumans() }}</p>
                        </div>
                    </div>
                @empty
                    <p class="py-6 text-center text-[14px] text-muted">Belum ada aktivitas.</p>
                @endforelse
            </div>
        </div>
    </div>
@endsection