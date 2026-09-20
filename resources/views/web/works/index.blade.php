@extends('layouts.web')

@section('title', 'Cara Kerja — OpenCash')
@section('meta_description', 'Alur lengkap OpenCash: admin menyiapkan kelas, bendahara menyiapkan tagihan, siswa bergabung dan membayar, lalu kas terverifikasi dan bisa dilihat seluruh kelas.')

@section('web')

    <section class="mx-auto max-w-6xl px-6 pt-16 pb-10 md:pt-24">
        <span class="web-rise web-rise-1 rounded-full bg-accent-tint px-3 py-1 text-sm font-medium text-accent-bright">Cara kerja</span>
        <h1 class="web-rise web-rise-2 mt-5 max-w-2xl text-4xl font-semibold leading-[1.08] tracking-tight text-ink md:text-5xl">
            Dari kelas dibuat sampai kas terverifikasi.
        </h1>
        <p class="web-rise web-rise-3 mt-5 max-w-xl text-[17px] leading-relaxed text-muted">
            Ini alur lengkapnya — siapa mengerjakan apa, dan di titik mana status pembayaran berubah. Ringkasan singkatnya ada di beranda.
        </p>

        <dl class="web-rise web-rise-4 mt-10 grid gap-x-8 gap-y-6 border-t border-line pt-8 sm:grid-cols-3">
            <div>
                <dt class="text-[13px] font-medium uppercase tracking-[0.12em] text-muted">Admin</dt>
                <dd class="mt-1.5 text-[15px] leading-relaxed text-ink">Membuat kelas dan menambah akun bendahara. Admin bersifat lintas kelas.</dd>
            </div>
            <div>
                <dt class="text-[13px] font-medium uppercase tracking-[0.12em] text-muted">Bendahara</dt>
                <dd class="mt-1.5 text-[15px] leading-relaxed text-ink">Mengatur tagihan dan QRIS, mencatat uang tunai, memverifikasi bukti transfer.</dd>
            </div>
            <div>
                <dt class="text-[13px] font-medium uppercase tracking-[0.12em] text-muted">Siswa</dt>
                <dd class="mt-1.5 text-[15px] leading-relaxed text-ink">Melihat tagihan dan riwayatnya, membayar tunai atau QRIS. Tidak bisa mengubah data.</dd>
            </div>
        </dl>
    </section>

    {{-- Langkah --}}
    <section class="mx-auto max-w-6xl px-6 py-14">
        <ol class="space-y-12">

            <li class="grid gap-3 md:grid-cols-[88px_1fr] md:gap-10">
                <span class="font-mono text-[15px] text-accent-bright">01</span>
                <div class="max-w-2xl">
                    <h2 class="text-xl font-medium text-ink">Admin menyiapkan kelas dan bendahara</h2>
                    <p class="mt-2 text-[15px] leading-relaxed text-muted">
                        Admin membuat kelas baru. OpenCash otomatis membuat kode undangan untuk kelas itu, lalu admin menambahkan satu atau dua akun sebagai bendahara kelas.
                    </p>
                    <p class="mt-3 border-l border-line pl-4 text-[15px] leading-relaxed text-muted">
                        Kelas tidak dibuat sendiri oleh bendahara — ini menjaga satu kelas tetap punya satu data, bukan beberapa yang terpisah.
                    </p>
                </div>
            </li>

            <li class="grid gap-3 md:grid-cols-[88px_1fr] md:gap-10">
                <span class="font-mono text-[15px] text-accent-bright">02</span>
                <div class="max-w-2xl">
                    <h2 class="text-xl font-medium text-ink">Bendahara menyiapkan tagihan dan QRIS</h2>
                    <p class="mt-2 text-[15px] leading-relaxed text-muted">
                        Bendahara membuat tagihan: keterangan, nominal, dan tanggal jatuh tempo. Nominal diatur per tagihan, jadi tiap iuran bisa berbeda besarannya. Bendahara juga mengunggah gambar QRIS kelas sekali, dan membagikan kode undangan ke siswa.
                    </p>
                </div>
            </li>

            <li class="grid gap-3 md:grid-cols-[88px_1fr] md:gap-10">
                <span class="font-mono text-[15px] text-accent-bright">03</span>
                <div class="max-w-2xl">
                    <h2 class="text-xl font-medium text-ink">Siswa bergabung memakai kode undangan</h2>
                    <p class="mt-2 text-[15px] leading-relaxed text-muted">
                        Siswa mendaftar akun, lalu memasukkan kode undangan dari bendahara. Begitu kode cocok, siswa langsung tergabung di kelas — tidak ada proses persetujuan satu per satu, dan satu kode bisa dipakai seluruh siswa di kelas itu.
                    </p>
                </div>
            </li>

            <li class="grid gap-3 md:grid-cols-[88px_1fr] md:gap-10">
                <span class="font-mono text-[15px] text-accent-bright">04</span>
                <div>
                    <h2 class="text-xl font-medium text-ink">Siswa membayar — tunai atau QRIS</h2>
                    <p class="mt-2 max-w-2xl text-[15px] leading-relaxed text-muted">
                        Ada dua jalur, dan keduanya berakhir di catatan yang sama:
                    </p>

                    <div class="mt-6 grid gap-5 md:grid-cols-2">
                        <div class="rounded-xl border border-line bg-surface/50 p-5">
                            <div class="flex items-center gap-2.5">
                                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-accent-tint text-accent-bright">
                                    <svg viewBox="0 0 20 20" fill="none" class="h-4 w-4" aria-hidden="true">
                                        <rect x="2.6" y="6" width="14.8" height="9" rx="1.8" stroke="currentColor" stroke-width="1.5"/>
                                        <circle cx="10" cy="10.5" r="2.2" stroke="currentColor" stroke-width="1.5"/>
                                    </svg>
                                </span>
                                <p class="font-medium text-ink">Tunai</p>
                            </div>
                            <p class="mt-3 text-[15px] leading-relaxed text-muted">
                                Siswa menyerahkan uang ke bendahara di kelas. Bendahara mencatatnya langsung, dan statusnya <span class="text-ink">langsung terverifikasi</span> karena uangnya sudah di tangan.
                            </p>
                        </div>

                        <div class="rounded-xl border border-line bg-surface/50 p-5">
                            <div class="flex items-center gap-2.5">
                                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-accent-tint text-accent-bright">
                                    <svg viewBox="0 0 20 20" fill="none" class="h-4 w-4" aria-hidden="true">
                                        <rect x="3.5" y="3.5" width="5" height="5" rx="1" stroke="currentColor" stroke-width="1.5"/>
                                        <rect x="11.5" y="3.5" width="5" height="5" rx="1" stroke="currentColor" stroke-width="1.5"/>
                                        <rect x="3.5" y="11.5" width="5" height="5" rx="1" stroke="currentColor" stroke-width="1.5"/>
                                        <path d="M11.5 11.5h5v5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                    </svg>
                                </span>
                                <p class="font-medium text-ink">QRIS mandiri</p>
                            </div>
                            <p class="mt-3 text-[15px] leading-relaxed text-muted">
                                Siswa scan QRIS kelas, transfer, lalu mengunggah bukti bayar. Statusnya <span class="text-ink">menunggu verifikasi</span> sampai bendahara mencocokkan dengan mutasi rekening.
                            </p>
                        </div>
                    </div>

                    <p class="mt-5 max-w-2xl border-l border-line pl-4 text-[15px] leading-relaxed text-muted">
                        Satu tagihan boleh dibayar bertahap. Kalau tagihannya Rp5.000 dan siswa baru bayar Rp3.000, sisa Rp2.000 tetap muncul sebagai kekurangan di halaman siswa maupun di rekap bendahara.
                    </p>
                </div>
            </li>

            <li class="grid gap-3 md:grid-cols-[88px_1fr] md:gap-10">
                <span class="font-mono text-[15px] text-accent-bright">05</span>
                <div class="max-w-2xl">
                    <h2 class="text-xl font-medium text-ink">Bendahara memverifikasi</h2>
                    <p class="mt-2 text-[15px] leading-relaxed text-muted">
                        Untuk pembayaran QRIS, bendahara mengecek mutasi rekening lalu menandai bukti itu terverifikasi atau ditolak. Kalau nominal yang ditransfer berbeda dari yang diisi siswa, nominalnya bisa dikoreksi saat verifikasi. Bendahara juga dapat notifikasi begitu ada bukti baru masuk.
                    </p>
                </div>
            </li>

            <li class="grid gap-3 md:grid-cols-[88px_1fr] md:gap-10">
                <span class="font-mono text-[15px] text-accent-bright">06</span>
                <div class="max-w-2xl">
                    <h2 class="text-xl font-medium text-ink">Kas tercatat dan terbuka untuk semua</h2>
                    <p class="mt-2 text-[15px] leading-relaxed text-muted">
                        Setelah terverifikasi, saldo kas dan riwayat pembayaran langsung terbarui. Siswa bisa melihat tagihan, status cicilannya, dan kondisi kas kelas kapan saja tanpa harus bertanya ke bendahara. Bendahara punya halaman laporan lengkap dengan pemasukan, pengeluaran, dan rekap per siswa, yang bisa diunduh sebagai PDF atau Excel.
                    </p>
                </div>
            </li>
        </ol>
    </section>

    {{-- Catatan kecil --}}
    <section class="mx-auto max-w-6xl px-6 pb-16">
        <div class="grid gap-6 rounded-xl border border-line bg-surface/40 p-6 sm:grid-cols-3 sm:gap-8">
            <div>
                <p class="font-medium text-ink">Saldo bisa minus</p>
                <p class="mt-1.5 text-[15px] leading-relaxed text-muted">Kalau pengeluaran melebihi pemasukan yang sudah terverifikasi, saldo ditampilkan apa adanya — bukan dianggap error.</p>
            </div>
            <div>
                <p class="font-medium text-ink">Pembayaran pending tidak masuk saldo</p>
                <p class="mt-1.5 text-[15px] leading-relaxed text-muted">Bukti QRIS yang belum diverifikasi belum dihitung sebagai pemasukan, tapi tetap dicatat supaya siswa tidak membayar dobel.</p>
            </div>
            <div>
                <p class="font-medium text-ink">Catat banyak sekaligus</p>
                <p class="mt-1.5 text-[15px] leading-relaxed text-muted">Bendahara bisa mengunggah pemasukan dan pengeluaran lewat berkas Excel memakai template yang disediakan.</p>
            </div>
        </div>
    </section>

    {{-- CTA --}}
    <section class="border-t border-line px-6 py-24 text-center">
        <h2 class="text-3xl font-semibold tracking-tight text-ink">Siap merapikan kas kelasmu?</h2>
        <p class="mx-auto mt-3 max-w-md text-[15px] text-muted">Buat kelas dalam hitungan menit. Gratis untuk digunakan.</p>
        <a
            href="{{ route('register') }}"
            class="mt-7 inline-block rounded-md bg-accent px-7 py-3 text-[15px] font-medium text-white transition-colors hover:bg-accent-bright hover:text-[#070b18] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent-bright active:translate-y-px"
        >
            Daftar gratis
        </a>
    </section>

@endsection
