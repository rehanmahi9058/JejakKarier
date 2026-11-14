@extends('layouts.dashboard')
@section('judul', 'Ringkasan')
@section('isi')
@php
  use Illuminate\Support\Str;
  $wib = 'Asia/Jakarta';
  $status = $profil?->status_verifikasi ?? 'menunggu';
  $kurang = [];
  if ($terbit < 1) $kurang[] = 'minimal 1 portofolio berstatus terbit';
  if ($jumlahKeterampilan < 3) $kurang[] = 'minimal 3 keterampilan (sekarang '.$jumlahKeterampilan.')';
  $banner = [
    'menunggu' => ['bg-amber-50 ring-amber-200 text-amber-900', 'Akun menunggu verifikasi admin', 'Sampai terverifikasi, portofoliomu belum tampil di katalog dan kamu belum bisa membuat slot jadwal.'],
    'terverifikasi' => ['bg-emerald-50 ring-emerald-200 text-emerald-900', 'Belum tampil di katalog publik', 'Lengkapi: '.implode(' dan ', $kurang).'.'],
    'ditolak' => ['bg-red-50 ring-red-200 text-red-900', 'Verifikasi ditolak', $profil?->catatan_verifikasi ?: 'Perbaiki profilmu sesuai catatan admin.'],
  ][$status];
  $tampilBanner = $status !== 'terverifikasi' || ! empty($kurang);

  $chip = ['diajukan' => 'bg-amber-100 text-amber-700', 'dikonfirmasi' => 'bg-brand-100 text-brand', 'selesai' => 'bg-emerald-100 text-emerald-700', 'ditolak' => 'bg-red-100 text-red-700', 'dibatalkan' => 'bg-red-100 text-red-700'];
  $label = ['diajukan' => 'Diajukan (Menunggu)', 'dikonfirmasi' => 'Dikonfirmasi', 'selesai' => 'Selesai', 'ditolak' => 'Ditolak', 'dibatalkan' => 'Dibatalkan'];
  $foto = fn ($user) => $user?->profilPengguna?->foto ? asset('storage/'.$user->profilPengguna->foto) : null;
  $inisial = fn ($user) => mb_strtoupper(mb_substr($user->name ?? '?', 0, 1));
  $idSesi = fn ($s) => '#SES-'.str_pad($s->id, 4, '0', STR_PAD_LEFT);
  $idSlot = fn ($s) => $s->slot_mentoring_id ? '#SLT-'.str_pad($s->slot_mentoring_id, 4, '0', STR_PAD_LEFT) : '-';
  $jamSesi = fn ($s) => substr($s->jam_mulai, 0, 5).' – '.substr($s->jam_selesai, 0, 5).' WIB';
  $hariIni = $sekarang->toDateString();
  $besok = $sekarang->copy()->addDay()->toDateString();
  $labelHari = function ($s) use ($hariIni, $besok) {
    $t = $s->tanggal->toDateString();
    return $t === $hariIni ? 'Hari ini' : ($t === $besok ? 'Besok' : $s->tanggal->translatedFormat('l, j M'));
  };
  $titik = fn ($s) => \Carbon\Carbon::parse($s->tanggal->toDateString().' '.$s->jam_mulai, $wib);
  $tabs = ['semua' => 'Semua', 'dikonfirmasi' => 'Dikonfirmasi', 'diajukan' => 'Diajukan', 'selesai' => 'Selesai', 'batal' => 'Dibatalkan'];
@endphp

@if ($tampilBanner)
  <div class="mb-6 rounded-2xl p-5 ring-1 {{ $banner[0] }}">
    <p class="font-bold">{{ $banner[1] }}</p>
    <p class="mt-1 text-sm opacity-90">{{ $banner[2] }}</p>
    <a href="{{ route('profil.edit') }}" class="mt-3 inline-block text-sm font-bold underline">Lengkapi profil mentor →</a>
  </div>
@endif

@if ($fokus)
@php
  $m = $fokus->pengguna;
  $pp = $m?->profilPengguna;
  $selesaiSesi = $fokus->status === 'selesai';
  $tautan = $fokus->tautanTerlihatOleh(auth()->user()) ? $fokus->tautan_pertemuan : null;
  $cek = $fokus->checklist ?? array_map(fn ($t) => ['teks' => $t, 'selesai' => false], ['Problem Statement Jelas', 'Validasi Riset Pengguna Kuat', 'Konsistensi Desain Visual', 'Penyusunan Metrik Sukses']);
  $aksiItems = $fokus->action_items ?? [];
  $namaLengkap = $m?->name ?? 'Mentee';
@endphp

{{-- ===== Ruang sesi (hero) ===== --}}
<section id="ruang" class="scroll-mt-6"
  x-data="{
    m: {{ $ruang['mulaiMs'] }}, s: {{ $ruang['selesaiMs'] }}, off: {{ $ruang['sekarangMs'] }} - Date.now(), t: {{ $ruang['sekarangMs'] }}, tersalin: false,
    init() { setInterval(() => { this.t = Date.now() + this.off }, 1000) },
    get fase() { return this.t < this.m ? 'belum' : (this.t <= this.s ? 'live' : 'usai') },
    get sisa() {
      const d = Math.max(0, Math.floor(((this.fase === 'belum' ? this.m : this.s) - this.t) / 1000));
      if (d >= 86400) return Math.floor(d / 86400) + ' hari';
      const p = n => String(n).padStart(2, '0'), h = Math.floor(d / 3600);
      return (h > 0 ? h + ':' : '') + p(Math.floor(d % 3600 / 60)) + ':' + p(d % 60);
    }
  }">
  <div class="relative overflow-hidden rounded-[2rem] bg-white p-8 shadow-sm ring-1 ring-slate-100">
    <div class="pointer-events-none absolute -right-16 -top-16 h-64 w-64 rounded-full bg-brand-100/70 blur-3xl"></div>
    <div class="relative flex flex-wrap items-start justify-between gap-6">
      <div class="min-w-0 max-w-2xl flex-1">
        <div class="flex flex-wrap items-center gap-2 text-[11px] font-bold uppercase tracking-wide">
          @if ($selesaiSesi)
            <span class="rounded-full bg-emerald-100 px-3 py-1.5 text-emerald-700">Sesi selesai</span>
          @else
            <span x-show="fase === 'live'" class="inline-flex items-center gap-1.5 rounded-full bg-amber-100 px-3 py-1.5 text-amber-700"><i class="h-1.5 w-1.5 rounded-full bg-amber-500"></i>Sedang berlangsung (Live Session)</span>
            <span x-show="fase === 'belum'" x-cloak class="rounded-full bg-brand-100 px-3 py-1.5 text-brand">Sesi berikutnya</span>
            <span x-show="fase === 'usai'" x-cloak class="rounded-full bg-slate-100 px-3 py-1.5 text-slate-600">Waktu sesi berakhir</span>
          @endif
          <span class="rounded-full bg-brand-50 px-3 py-1.5 text-slate-600">ID Sesi: {{ $idSesi($fokus) }}</span>
          <span class="rounded-full bg-brand-50 px-3 py-1.5 text-slate-600">Slot: {{ $idSlot($fokus) }}</span>
        </div>
        <h1 class="mt-4 text-3xl font-extrabold leading-tight tracking-tight text-slate-900 sm:text-4xl">{{ Str::limit($fokus->tujuan, 80) }}</h1>
        <p class="mt-3 text-sm leading-relaxed text-slate-500">Sesi mentoring 1-on-1 bersama {{ $namaLengkap }}, {{ $labelHari($fokus) }} • {{ $jamSesi($fokus) }} ({{ $ruang['durasi'] }} menit).</p>
      </div>

      <div class="flex flex-col items-stretch gap-3 sm:items-end">
        @unless ($selesaiSesi)
          <div class="inline-flex items-center gap-3 rounded-2xl bg-brand-50 px-5 py-3 text-sm">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500" x-text="fase === 'live' ? 'Sisa waktu' : (fase === 'belum' ? 'Dimulai dalam' : 'Sesi berakhir')"></span>
            <span x-show="fase !== 'usai'" class="text-2xl font-extrabold tabular-nums text-brand" x-text="sisa"></span>
            <span class="text-xs font-semibold text-slate-500">/ {{ $ruang['durasi'] }} Mnt</span>
          </div>
        @endunless
        <div class="flex flex-wrap gap-3 sm:justify-end">
          @if ($tautan)
            <button type="button" @click="navigator.clipboard?.writeText(@js($tautan)); tersalin = true; setTimeout(() => tersalin = false, 1500)" class="inline-flex items-center gap-2 rounded-full bg-brand-50 px-5 py-3 text-sm font-bold text-brand hover:bg-brand-100">
              <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="9" y="9" width="11" height="11" rx="2"/><path d="M5 15V6a2 2 0 012-2h9"/></svg>
              <span x-text="tersalin ? 'Tersalin ✓' : 'Salin Tautan'">Salin Tautan</span>
            </button>
            <a href="{{ $tautan }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 rounded-full bg-brand px-6 py-3 text-sm font-bold text-white shadow-lg shadow-brand/30 hover:bg-brand-600">
              <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="6" width="12" height="12" rx="2"/><path d="M15 10l6-3v10l-6-3"/></svg>
              {{ str_contains($tautan, 'meet.google') ? 'Gabung Google Meet' : 'Gabung Pertemuan' }}
            </a>
          @else
            <span class="rounded-full bg-slate-100 px-5 py-3 text-sm font-semibold text-slate-500">Tautan pertemuan belum diisi</span>
          @endif
        </div>
      </div>
    </div>
  </div>

  <div class="mt-6 grid items-start gap-6 lg:grid-cols-[minmax(0,5fr)_minmax(0,7fr)]">
    {{-- ===== Kolom kiri: mentee ===== --}}
    <div class="space-y-6">
      <div class="rounded-[2rem] bg-white p-6 shadow-sm ring-1 ring-slate-100">
        <div class="flex items-start gap-4">
          <span class="grid h-20 w-20 shrink-0 place-items-center overflow-hidden rounded-2xl bg-brand-100 text-2xl font-bold text-brand ring-4 ring-brand-50">
            @if ($foto($m))<img src="{{ $foto($m) }}" alt="" class="h-full w-full object-cover">@else{{ $inisial($m) }}@endif
          </span>
          <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-center gap-2">
              <h2 class="text-lg font-extrabold text-slate-900">{{ $namaLengkap }}</h2>
              @unless ($selesaiSesi)<span x-show="fase === 'live'" class="rounded-full bg-emerald-100 px-2.5 py-1 text-[11px] font-bold text-emerald-700">Aktif Sekarang</span>@endunless
            </div>
            <p class="mt-0.5 text-sm text-slate-600">{{ $pp?->tujuan_karier ?: ($pp?->minat ?: 'Pengguna JejakKarier') }}</p>
            <p class="mt-1 text-xs text-slate-400">Pengguna ID: #USR-{{ str_pad($m?->id ?? 0, 4, '0', STR_PAD_LEFT) }}</p>
          </div>
        </div>
        <div class="mt-5 grid grid-cols-2 gap-3">
          <div class="rounded-2xl bg-brand-50 px-4 py-3"><p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Pendidikan</p><p class="mt-1 text-sm font-bold text-slate-800">{{ $pp?->pendidikan ?: '-' }}</p></div>
          <div class="rounded-2xl bg-brand-50 px-4 py-3"><p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Riwayat sesi</p><p class="mt-1 text-sm font-bold text-slate-800">{{ $ruang['riwayat'] }} Sesi Selesai</p></div>
        </div>

        <p class="mt-6 text-[11px] font-bold uppercase tracking-wider text-slate-500">Tujuan sesi mentee</p>
        <div class="mt-2 rounded-2xl bg-brand-50 p-4 text-sm leading-relaxed text-slate-700">"{{ $fokus->tujuan }}"</div>

        <p class="mt-6 text-[11px] font-bold uppercase tracking-wider text-slate-500">Berkas &amp; tautan diskusi</p>
        <div class="mt-2 space-y-2">
          @if ($tautan)
            <a href="{{ $tautan }}" target="_blank" rel="noopener" class="flex items-center gap-3 rounded-2xl bg-brand-50 p-3 hover:bg-brand-100">
              <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-brand-100 text-brand"><svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><rect x="3" y="6" width="12" height="12" rx="2"/><path d="M15 10l6-3v10l-6-3"/></svg></span>
              <span class="min-w-0 flex-1"><span class="block truncate text-sm font-bold text-slate-800">{{ preg_replace('#^https?://#', '', $tautan) }}</span><span class="text-[11px] text-slate-400">Tautan pertemuan daring</span></span>
              <span class="text-slate-400">↗</span>
            </a>
          @else
            <p class="rounded-2xl bg-brand-50 p-3 text-sm text-slate-500">Belum ada tautan pertemuan untuk sesi ini.</p>
          @endif
        </div>
      </div>

      <div class="flex items-center gap-4 rounded-[2rem] bg-brand-50 p-5">
        <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-white text-brand"><svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M8 3v4M16 3v4M3 10h18"/></svg></span>
        <div class="min-w-0 flex-1">
          <p class="text-xs text-slate-500">Jadwal slot mentoring</p>
          <p class="text-sm font-bold text-slate-900">{{ $fokus->tanggal->translatedFormat('l, j M') }} • {{ $jamSesi($fokus) }}</p>
        </div>
        <span class="rounded-full bg-brand-100 px-3 py-1 text-[11px] font-bold text-brand">{{ $fokus->slot_mentoring_id ? 'Slot Terkunci' : 'Tanpa Slot' }}</span>
      </div>
    </div>

    {{-- ===== Kolom kanan: catatan mentor ===== --}}
    <form method="POST" action="{{ route('sesi.catatan', $fokus) }}" class="rounded-[2rem] bg-white p-6 shadow-sm ring-1 ring-slate-100 sm:p-8"
      x-data="{
        cek: @js($cek), aksi: @js($aksiItems), cekBaru: '', aksiBaru: '', labelBaru: '', fb: @js($fokus->umpan_balik ?? ''),
        tambahCek() { const t = this.cekBaru.trim(); if (t && this.cek.length < 12) this.cek.push({ teks: t, selesai: false }); this.cekBaru = '' },
        tambahAksi() { const t = this.aksiBaru.trim(); if (t && this.aksi.length < 12) this.aksi.push({ teks: t, label: this.labelBaru.trim() }); this.aksiBaru = ''; this.labelBaru = '' }
      }">
      @csrf
      <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
          <h2 class="text-xl font-extrabold text-slate-900">Catatan Mentor</h2>
          <p class="mt-1 max-w-sm text-xs text-slate-500">Catatan disimpan pada sesi ini. Tandai sesi selesai setelah pertemuan berakhir.</p>
        </div>
        <span class="rounded-full px-3 py-1.5 text-[11px] font-bold {{ $selesaiSesi ? 'bg-emerald-100 text-emerald-700' : 'bg-brand-100 text-brand' }}">{{ $selesaiSesi ? 'Sesi selesai' : 'Draf catatan' }}</span>
      </div>

      <input type="hidden" name="checklist_json" :value="JSON.stringify(cek)">
      <input type="hidden" name="action_json" :value="JSON.stringify(aksi)">

      <p class="mt-6 text-[11px] font-bold uppercase tracking-wider text-slate-500">Checklist evaluasi langsung</p>
      <div class="mt-2 grid gap-3 sm:grid-cols-2">
        <template x-for="(i, n) in cek" :key="n">
          <div class="flex items-center gap-3 rounded-2xl bg-brand-50 px-4 py-3">
            <input type="checkbox" x-model="i.selesai" class="h-4 w-4 shrink-0 rounded border-slate-300 text-brand focus:ring-brand">
            <span class="min-w-0 flex-1 text-sm font-semibold text-slate-800" x-text="i.teks"></span>
            <button type="button" @click="cek.splice(n, 1)" class="text-slate-400 hover:text-red-600" aria-label="Hapus">×</button>
          </div>
        </template>
      </div>
      <div class="mt-2 flex gap-2">
        <input type="text" x-model="cekBaru" @keydown.enter.prevent="tambahCek()" maxlength="200" placeholder="Tambah poin evaluasi…" class="min-w-0 flex-1 rounded-xl border-0 bg-slate-50 px-3 py-2 text-sm focus:ring-brand">
        <button type="button" @click="tambahCek()" class="rounded-xl bg-brand-50 px-4 py-2 text-sm font-bold text-brand hover:bg-brand-100">+ Tambah</button>
      </div>

      <div class="mt-6 flex items-center justify-between">
        <label for="umpan_balik" class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Uraian umpan balik &amp; rekomendasi karier</label>
        <span class="text-[11px] text-slate-400" x-text="fb.length + ' / 5000'"></span>
      </div>
      <textarea id="umpan_balik" name="umpan_balik" x-model="fb" rows="9" maxlength="5000" class="mt-2 w-full rounded-2xl border-0 bg-brand-50 p-4 text-sm leading-relaxed text-slate-700 placeholder:text-slate-400 focus:ring-brand"
        placeholder="1. Kelebihan utama:
- ...

2. Poin rekomendasi &amp; tindak lanjut:
- ..."></textarea>

      <p class="mt-6 text-[11px] font-bold uppercase tracking-wider text-slate-500">Rekomendasi langkah berikutnya (action items)</p>
      <div class="mt-2 space-y-2">
        <template x-for="(i, n) in aksi" :key="n">
          <div class="flex items-center gap-3 rounded-2xl bg-brand-50 px-4 py-3">
            <svg class="h-4 w-4 shrink-0 text-brand" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M10 8l4 4-4 4"/></svg>
            <span class="min-w-0 flex-1 text-sm text-slate-800" x-text="i.teks"></span>
            <span class="shrink-0 text-xs font-semibold text-slate-500" x-text="i.label"></span>
            <button type="button" @click="aksi.splice(n, 1)" class="text-slate-400 hover:text-red-600" aria-label="Hapus">×</button>
          </div>
        </template>
        <p x-show="aksi.length === 0" class="rounded-2xl bg-slate-50 px-4 py-3 text-xs text-slate-400">Belum ada action item.</p>
      </div>
      <div class="mt-2 grid gap-2 sm:grid-cols-[1fr_9rem_auto]">
        <input type="text" x-model="aksiBaru" @keydown.enter.prevent="tambahAksi()" maxlength="200" placeholder="Langkah tindak lanjut…" class="min-w-0 rounded-xl border-0 bg-slate-50 px-3 py-2 text-sm focus:ring-brand">
        <input type="text" x-model="labelBaru" @keydown.enter.prevent="tambahAksi()" maxlength="40" placeholder="Prioritas / target" class="min-w-0 rounded-xl border-0 bg-slate-50 px-3 py-2 text-sm focus:ring-brand">
        <button type="button" @click="tambahAksi()" class="rounded-xl bg-brand-50 px-4 py-2 text-sm font-bold text-brand hover:bg-brand-100">+ Tambah</button>
      </div>

      <div class="mt-8 flex flex-wrap gap-3">
        <button type="submit" name="aksi" value="simpan" class="flex-1 rounded-full bg-brand-50 px-6 py-3.5 text-sm font-bold text-slate-800 hover:bg-brand-100">{{ $selesaiSesi ? 'Perbarui Catatan' : 'Simpan Catatan Sementara' }}</button>
        @unless ($selesaiSesi)
          <button type="submit" name="aksi" value="selesai" onclick="return confirm('Tandai sesi ini selesai? Catatan ikut tersimpan.')" class="flex-1 rounded-full bg-emerald-600 px-6 py-3.5 text-sm font-bold text-white shadow-lg shadow-emerald-600/25 hover:bg-emerald-700">✓ Tandai Sesi Selesai</button>
        @endunless
      </div>
    </form>
  </div>
</section>
@else
<section class="rounded-[2rem] bg-white p-8 shadow-sm ring-1 ring-slate-100">
  <p class="text-[11px] font-bold uppercase tracking-widest text-brand">Ruang sesi</p>
  <h1 class="mt-2 text-3xl font-extrabold tracking-tight text-slate-900">Belum ada sesi yang dikonfirmasi</h1>
  <p class="mt-3 max-w-xl text-sm text-slate-500">
    @if ($jumlah['diajukan'] > 0)Ada {{ $jumlah['diajukan'] }} permintaan menunggu keputusanmu di tabel di bawah.@else Buat slot jadwal agar mentee bisa memesan sesi denganmu.@endif
  </p>
  <a href="{{ route('mentor.slot.index') }}" class="mt-5 inline-block rounded-full bg-brand px-6 py-3 text-sm font-bold text-white shadow-lg shadow-brand/30 hover:bg-brand-600">Kelola Slot Jadwal</a>
</section>
@endif

{{-- ===== Manajemen & riwayat sesi ===== --}}
<section id="riwayat" class="mt-12 scroll-mt-6">
  <div class="flex flex-wrap items-end justify-between gap-4">
    <div>
      <h2 class="text-2xl font-extrabold tracking-tight text-slate-900">Manajemen &amp; Riwayat Sesi Mentoring</h2>
      <p class="mt-1 text-sm text-slate-500">Kelola daftar sesi mendatang, permintaan diajukan, serta rekap hasil mentoring.</p>
    </div>
    <div class="flex max-w-full gap-1 overflow-x-auto rounded-full bg-white p-1.5 shadow-sm ring-1 ring-slate-100">
      @foreach ($tabs as $k => $t)
        <a href="{{ route('dashboard', array_filter(['tab' => $k === 'semua' ? null : $k, 'sesi' => request('sesi')])) }}#riwayat" class="whitespace-nowrap rounded-full px-4 py-2 text-sm font-semibold {{ $tab === $k ? 'bg-brand-100 text-brand' : 'text-slate-500 hover:bg-slate-50' }}">{{ $t }} ({{ $jumlah[$k] }})</a>
      @endforeach
    </div>
  </div>

  <div class="mt-5 overflow-hidden rounded-[2rem] bg-white shadow-sm ring-1 ring-slate-100">
    <div class="overflow-x-auto">
      <table class="w-full min-w-[60rem] text-left text-sm">
        <thead class="text-[11px] font-bold uppercase tracking-wider text-slate-500">
          <tr><th class="px-6 py-4">ID &amp; Slot</th><th class="px-3 py-4">Mentee</th><th class="px-3 py-4">Tujuan sesi</th><th class="px-3 py-4">Jadwal &amp; waktu</th><th class="px-3 py-4">Status</th><th class="px-6 py-4 text-right">Aksi</th></tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          @forelse ($sesi as $s)
            @php
              $mulaiR = $titik($s);
              $selesaiR = \Carbon\Carbon::parse($s->tanggal->toDateString().' '.$s->jam_selesai, $wib);
              $liveR = $s->status === 'dikonfirmasi' && $mulaiR->lte($sekarang) && $selesaiR->gte($sekarang);
              $redup = in_array($s->status, ['ditolak', 'dibatalkan'], true);
              $durasiR = $mulaiR->diffInMinutes($selesaiR);
            @endphp
            <tr class="{{ $liveR ? 'bg-brand-50/60' : '' }} {{ $redup ? 'text-slate-400' : '' }}">
              <td class="px-6 py-4"><p class="font-bold {{ $redup ? 'text-slate-400' : 'text-brand' }}">{{ $idSesi($s) }}</p><p class="text-[11px] text-slate-400">Slot: {{ $idSlot($s) }}</p></td>
              <td class="px-3 py-4">
                <div class="flex items-center gap-3">
                  <span class="grid h-10 w-10 shrink-0 place-items-center overflow-hidden rounded-full bg-brand-100 text-sm font-bold text-brand">@if ($foto($s->pengguna))<img src="{{ $foto($s->pengguna) }}" alt="" class="h-full w-full object-cover">@else{{ $inisial($s->pengguna) }}@endif</span>
                  <div class="min-w-0"><p class="font-bold {{ $redup ? 'text-slate-500' : 'text-slate-900' }}">{{ $s->pengguna?->name }}</p><p class="text-[11px] text-slate-400">{{ Str::limit($s->pengguna?->profilPengguna?->tujuan_karier ?: ($s->pengguna?->profilPengguna?->minat ?: 'Pengguna'), 28) }}</p></div>
                </div>
              </td>
              <td class="max-w-[14rem] px-3 py-4">
                <p class="{{ $redup ? '' : 'text-slate-700' }}">{{ Str::limit($s->tujuan, 44) }}</p>
                @if ($liveR)<p class="mt-1 text-[11px] font-semibold text-brand">● Sesi berlangsung</p>
                @elseif ($s->status === 'selesai' && filled($s->umpan_balik))<p class="mt-1 text-[11px] font-semibold text-emerald-700">✓ Catatan mentor tersimpan</p>@endif
              </td>
              <td class="px-3 py-4"><p class="font-bold {{ $redup ? 'text-slate-500' : 'text-slate-900' }}">{{ $labelHari($s) }}, {{ substr($s->jam_mulai, 0, 5) }} WIB</p><p class="text-[11px] text-slate-400">Durasi {{ $durasiR }} menit</p></td>
              <td class="px-3 py-4"><span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-[11px] font-bold {{ $liveR ? 'bg-amber-100 text-amber-700' : $chip[$s->status] }}">@if ($liveR)<i class="h-1.5 w-1.5 rounded-full bg-amber-500"></i>@endif{{ $liveR ? 'Dikonfirmasi (Live)' : $label[$s->status] }}</span></td>
              <td class="px-6 py-4">
                <div class="flex items-center justify-end gap-2 text-xs font-bold">
                  @if ($s->status === 'dikonfirmasi')
                    @if ($liveR)<a href="{{ route('dashboard', ['sesi' => $s->id]) }}#ruang" class="rounded-full bg-brand px-4 py-2 text-white hover:bg-brand-600">Buka Ruang</a>
                    @else<a href="{{ route('mentor.slot.index') }}" class="rounded-full bg-brand-50 px-4 py-2 text-brand hover:bg-brand-100">Detail Slot</a>@endif
                  @elseif ($s->status === 'diajukan')
                    <form method="POST" action="{{ route('sesi.aksi', [$s, 'konfirmasi']) }}">@csrf<button class="rounded-full bg-emerald-600 px-4 py-2 text-white hover:bg-emerald-700">Setujui</button></form>
                    <form method="POST" action="{{ route('sesi.aksi', [$s, 'tolak']) }}" onsubmit="return confirm('Tolak permintaan sesi ini? Slot akan tersedia kembali.')">@csrf<button class="rounded-full bg-red-50 px-4 py-2 text-red-600 hover:bg-red-100">Tolak</button></form>
                  @elseif ($s->status === 'selesai')
                    <a href="{{ route('dashboard', ['sesi' => $s->id]) }}#ruang" class="rounded-full bg-brand-50 px-4 py-2 text-brand hover:bg-brand-100">Lihat Catatan</a>
                  @else
                    <span class="px-4 py-2 text-slate-400">Arsip</span>
                  @endif
                </div>
              </td>
            </tr>
          @empty
            <tr><td colspan="6" class="px-6 py-14 text-center text-slate-500">Belum ada sesi untuk filter ini.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="flex flex-wrap items-center justify-between gap-2 border-t border-slate-100 px-6 py-3 text-xs text-slate-500">
      <span>Menampilkan {{ $sesi->firstItem() ?? 0 }} - {{ $sesi->lastItem() ?? 0 }} dari {{ $sesi->total() }} sesi</span>
      <span class="flex gap-2">
        @if ($sesi->previousPageUrl())<a href="{{ $sesi->previousPageUrl() }}" class="rounded-lg bg-brand-50 px-3 py-1.5 font-semibold text-slate-600 hover:bg-brand-100">‹ Sebelumnya</a>@endif
        @if ($sesi->nextPageUrl())<a href="{{ $sesi->nextPageUrl() }}" class="rounded-lg bg-brand-50 px-3 py-1.5 font-semibold text-slate-600 hover:bg-brand-100">Berikutnya ›</a>@endif
        <a href="{{ route('sesi.index') }}" class="rounded-lg bg-brand-100 px-3 py-1.5 font-semibold text-brand">Kalender &amp; semua sesi →</a>
      </span>
    </div>
  </div>
</section>
@endsection
