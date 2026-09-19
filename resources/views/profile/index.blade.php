@extends('layouts.panel')

@section('title', 'Profil Saya — OpenCash')

@section('panel')
    <div>
        <h1 class="text-2xl font-semibold tracking-tight text-ink">Profil Saya</h1>
        <p class="mt-1 text-[15px] text-muted">Ubah nama, email, dan password akunmu.</p>
    </div>

    @if (session('status'))
        <div class="mt-5 flex items-start gap-3 rounded-xl border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-[13.5px] text-emerald-300">
            <i data-lucide="check-circle-2" class="mt-0.5 h-4 w-4 shrink-0" stroke-width="1.8"></i>
            <span>{{ session('status') }}</span>
        </div>
    @endif

    @if ($errors->any())
        <div class="mt-5 rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-3 text-[13.5px] text-red-300" role="alert">
            <ul class="list-disc space-y-0.5 pl-4">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="mt-6 grid gap-6 lg:grid-cols-[1fr_20rem]">
        <form method="POST" action="{{ route('profile.update') }}" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="rounded-2xl border border-line bg-surface p-6">
                <h2 class="text-[15px] font-semibold text-ink">Data Akun</h2>
                <p class="mt-1 text-[13px] text-muted">Email dipakai untuk login, jadi pastikan masih aktif.</p>

                <div class="mt-5 space-y-4">
                    <div>
                        <label for="profile-name" class="text-[13px] font-medium text-ink">Nama lengkap</label>
                        <input type="text" id="profile-name" name="name" required maxlength="255"
                            value="{{ old('name', $user->name) }}"
                            class="mt-1.5 w-full rounded-md border border-line bg-bg px-3 py-2.5 text-[14px] text-ink outline-none focus:border-accent" />
                    </div>

                    <div>
                        <label for="profile-email" class="text-[13px] font-medium text-ink">Email</label>
                        <input type="email" id="profile-email" name="email" required maxlength="255"
                            value="{{ old('email', $user->email) }}" spellcheck="false"
                            class="mt-1.5 w-full rounded-md border border-line bg-bg px-3 py-2.5 text-[14px] text-ink outline-none focus:border-accent" />
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="text-[13px] font-medium text-ink">Peran</label>
                            <input type="text" readonly value="{{ $user->isTreasurer() ? 'Bendahara' : 'Siswa' }}"
                                class="mt-1.5 w-full cursor-not-allowed rounded-md border border-line bg-bg px-3 py-2.5 text-[14px] text-muted outline-none" />
                        </div>
                        <div>
                            <label class="text-[13px] font-medium text-ink">Kelas</label>
                            <input type="text" readonly value="{{ $user->group?->name ?? '—' }}"
                                class="mt-1.5 w-full cursor-not-allowed rounded-md border border-line bg-bg px-3 py-2.5 text-[14px] text-muted outline-none" />
                        </div>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-line bg-surface p-6">
                <h2 class="text-[15px] font-semibold text-ink">Ubah Password</h2>
                <p class="mt-1 text-[13px] text-muted">Kosongkan bagian ini kalau tidak ingin mengganti password.</p>

                <div class="mt-5 space-y-4">
                    <div>
                        <label for="profile-current-password" class="text-[13px] font-medium text-ink">Password saat ini</label>
                        <input type="password" id="profile-current-password" name="current_password"
                            autocomplete="current-password"
                            class="mt-1.5 w-full rounded-md border border-line bg-bg px-3 py-2.5 text-[14px] text-ink outline-none focus:border-accent"
                            placeholder="Wajib diisi kalau ganti password" />
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="profile-password" class="text-[13px] font-medium text-ink">Password baru</label>
                            <input type="password" id="profile-password" name="password" autocomplete="new-password"
                                class="mt-1.5 w-full rounded-md border border-line bg-bg px-3 py-2.5 text-[14px] text-ink outline-none focus:border-accent"
                                placeholder="Minimal 8 karakter" />
                        </div>

                        <div>
                            <label for="profile-password-confirmation" class="text-[13px] font-medium text-ink">Ulangi password baru</label>
                            <input type="password" id="profile-password-confirmation" name="password_confirmation" autocomplete="new-password"
                                class="mt-1.5 w-full rounded-md border border-line bg-bg px-3 py-2.5 text-[14px] text-ink outline-none focus:border-accent"
                                placeholder="Ulangi password" />
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit"
                    class="rounded-md bg-accent px-5 py-2.5 text-[14px] font-medium text-white transition-colors hover:bg-accent-bright hover:text-[#070b18]">
                    Simpan Perubahan
                </button>
            </div>
        </form>

        <div class="rounded-2xl border border-line bg-surface p-6">
            <h2 class="text-[15px] font-semibold text-ink">Riwayat Perubahan</h2>
            <p class="mt-1 text-[13px] text-muted">10 perubahan terakhir pada akunmu.</p>

            <div class="mt-5 space-y-4">
                @forelse ($audits as $audit)
                    <div class="flex gap-3">
                        <span class="mt-1 flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-accent-tint text-accent-bright">
                            <i data-lucide="history" class="h-3.5 w-3.5" stroke-width="1.8"></i>
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="text-[13.5px] text-ink">
                                {{ match ($audit->action) {
                                    'created' => 'Akun dibuat',
                                    'updated' => 'Data akun diubah',
                                    'deleted' => 'Akun dihapus',
                                    default => $audit->action,
                                } }}
                            </p>
                            <p class="text-xs text-muted">
                                {{ $audit->created_at?->translatedFormat('d M Y, H:i') }} &middot; oleh {{ $audit->actor_name }}
                            </p>
                        </div>
                    </div>
                @empty
                    <p class="py-6 text-center text-[13px] text-muted">Belum ada perubahan tercatat.</p>
                @endforelse
            </div>
        </div>
    </div>
@endsection
