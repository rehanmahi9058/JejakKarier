@extends('layouts.dashboard')
@section('judul', 'Kelola Mentor dan Akun')
@section('isi')
@php
  $inisial = fn ($n) => collect(explode(' ', $n))->take(2)->map(fn ($k) => mb_strtoupper(mb_substr($k, 0, 1)))->implode('');
  $tabUrl = fn ($t) => route('admin.akun', $t === 'semua' ? ['tab' => 'semua'] : []);
@endphp

<span class="inline-block rounded-md bg-brand-50 px-3 py-1 text-[11px] font-bold uppercase tracking-widest text-brand">Administrator</span>
<h1 class="mt-3 text-4xl font-extrabold tracking-tight text-[#1a1446]">Kelola Mentor dan Akun</h1>
<p class="mt-1 text-sm text-slate-500">Verifikasi mentor baru dan atur status aktif akun. Akun nonaktif tidak dapat masuk dan disembunyikan dari katalog.</p>

<div class="mt-6 flex flex-wrap gap-3">
  <a href="{{ $tabUrl('verifikasi') }}" class="rounded-full px-6 py-2.5 text-sm font-bold {{ $tab === 'verifikasi' ? 'bg-[#1a1446] text-white' : 'bg-white text-slate-700 ring-1 ring-slate-200 hover:bg-slate-50' }}">Verifikasi Mentor ({{ $antrian->count() }})</a>
  <a href="{{ $tabUrl('semua') }}" class="rounded-full px-6 py-2.5 text-sm font-bold {{ $tab === 'semua' ? 'bg-[#1a1446] text-white' : 'bg-white text-slate-700 ring-1 ring-slate-200 hover:bg-slate-50' }}">Semua Akun</a>
</div>

@if ($tab === 'verifikasi')
<section id="verifikasi" class="mt-5 rounded-3xl bg-white p-7 shadow-sm ring-1 ring-slate-100 scroll-mt-6"
  x-data="{ catatan: @js(old('catatan', '')), aksi: '', url: '', salah: false,
    kirim(url, aksi) {
      if (aksi === 'tolak' && !this.catatan.trim()) { this.salah = true; this.$refs.catatan.focus(); return }
      this.salah = false; this.url = url; this.aksi = aksi;
      this.$nextTick(() => this.$refs.form.submit());
    } }">
  <div class="flex items-center justify-between gap-3">
    <h2 class="text-lg font-extrabold text-[#1a1446]">Mentor menunggu verifikasi</h2>
    @if ($antrian->count())<span class="rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700 ring-1 ring-amber-200">{{ $antrian->count() }} Perlu Ditinjau</span>@endif
  </div>

  <form x-ref="form" method="POST" :action="url">
    @csrf
    <input type="hidden" name="aksi" :value="aksi">
    <div class="mt-5 space-y-4">
      @forelse ($antrian as $p)
        <div class="flex flex-wrap items-center justify-between gap-4 rounded-2xl border border-slate-100 px-5 py-4">
          <div class="flex min-w-0 items-center gap-4">
            <span class="grid h-12 w-12 shrink-0 place-items-center rounded-xl bg-brand-100 text-sm font-bold text-brand">{{ $inisial($p->user->name) }}</span>
            <div class="min-w-0">
              <p class="font-bold text-[#1a1446]">{{ $p->user->name }}</p>
              <p class="text-xs text-slate-500">{{ $p->user->email }}<span class="mx-1.5 text-slate-300">●</span>{{ $p->jabatan ?: 'Jabatan belum diisi' }}<span class="mx-1.5 text-slate-300">●</span>{{ $p->tahun_pengalaman }} th</p>
            </div>
          </div>
          <div class="flex items-center gap-3">
            <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700 ring-1 ring-amber-200"><i class="h-1.5 w-1.5 rounded-full bg-amber-500"></i>Menunggu</span>
            <button type="button" @click="kirim('{{ route('admin.mentor.verifikasi', $p) }}', 'tolak')" class="rounded-xl border border-slate-200 bg-white px-5 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50">Tolak</button>
            <button type="button" @click="kirim('{{ route('admin.mentor.verifikasi', $p) }}', 'verifikasi')" class="rounded-xl bg-brand px-5 py-2 text-xs font-bold text-white hover:bg-brand-600">Verifikasi</button>
          </div>
        </div>
      @empty
        <p class="rounded-2xl bg-slate-50 px-5 py-8 text-center text-sm text-slate-500">Tidak ada mentor yang menunggu verifikasi.</p>
      @endforelse
    </div>

    @if ($antrian->count())
      <div class="mt-5 flex flex-wrap items-center gap-3">
        <label for="catatan" class="text-xs font-semibold text-[#1a1446]">Catatan verifikasi <span class="font-normal text-slate-500">(opsional, wajib saat menolak)</span></label>
        <input id="catatan" x-ref="catatan" name="catatan" x-model="catatan" maxlength="500" placeholder="Tulis alasan…" class="min-w-[14rem] flex-1 rounded-xl border-0 bg-slate-50 px-4 py-3 text-sm focus:ring-brand">
      </div>
      <p x-show="salah" x-cloak class="mt-2 text-xs font-semibold text-red-600">Catatan wajib diisi saat menolak mentor.</p>
      @error('catatan')<p class="mt-2 text-xs font-semibold text-red-600">{{ $message }}</p>@enderror
    @endif
  </form>
</section>
@endif

<section id="akun" class="mt-6 overflow-hidden rounded-3xl bg-white shadow-sm ring-1 ring-slate-100 scroll-mt-6">
  <div class="flex flex-wrap items-center justify-between gap-3 px-7 py-6">
    <h2 class="text-lg font-extrabold text-[#1a1446]">Daftar akun</h2>
    <form method="GET" action="{{ route('admin.akun') }}" class="relative w-full sm:w-80">
      @if ($tab === 'semua')<input type="hidden" name="tab" value="semua">@endif
      <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path stroke-linecap="round" d="M21 21l-4.3-4.3"/></svg>
      <input name="q" value="{{ $cari }}" placeholder="Cari nama atau email…" class="w-full rounded-xl border-slate-200 bg-slate-50 py-2.5 pl-9 pr-3 text-sm focus:border-brand focus:ring-brand">
    </form>
  </div>

  @forelse ($akun as $a)
    @php
      $mentor = $a->adalahMentor();
      $st = $a->profilMentor?->status_verifikasi;
      $warna = ! $a->aktif ? 'bg-slate-100 text-slate-400' : ($mentor ? 'bg-emerald-50 text-emerald-600' : 'bg-brand-50 text-brand');
    @endphp
    <div class="flex flex-wrap items-center justify-between gap-4 border-t border-slate-100 px-7 py-4">
      <div class="flex min-w-0 items-center gap-4">
        <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl text-sm font-bold {{ $warna }}">{{ $inisial($a->name) }}</span>
        <div class="min-w-0">
          <p class="font-bold {{ $a->aktif ? 'text-[#1a1446]' : 'text-slate-500' }}">{{ $a->name }}</p>
          <p class="text-xs text-slate-500">{{ $a->email }}<span class="mx-1.5 text-slate-300">●</span>{{ $mentor ? 'Mentor' : 'Pengguna' }}@if ($mentor && $st)<span class="mx-1.5 text-slate-300">●</span>{{ ['terverifikasi' => 'Terverifikasi', 'menunggu' => 'Menunggu verifikasi', 'ditolak' => 'Verifikasi ditolak'][$st] }}@endif</p>
        </div>
      </div>
      <div class="flex items-center gap-4">
        <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $a->aktif ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200' : 'bg-slate-100 text-slate-500' }}">{{ $a->aktif ? 'Aktif' : 'Nonaktif' }}</span>
        <form method="POST" action="{{ route('admin.akun.status', $a) }}" @if ($a->aktif) onsubmit="return confirm('Nonaktifkan akun ini? Akun ini tidak bisa masuk dan disembunyikan dari katalog.')" @endif>
          @csrf @method('PATCH')
          <button type="submit" role="switch" aria-checked="{{ $a->aktif ? 'true' : 'false' }}" aria-label="{{ $a->aktif ? 'Nonaktifkan' : 'Aktifkan' }} {{ $a->name }}" class="relative h-7 w-12 rounded-full transition {{ $a->aktif ? 'bg-brand' : 'bg-slate-200' }}">
            <span class="absolute top-0.5 h-6 w-6 rounded-full bg-white shadow {{ $a->aktif ? 'right-0.5' : 'left-0.5' }}"></span>
          </button>
        </form>
      </div>
    </div>
  @empty
    <p class="border-t border-slate-100 px-7 py-10 text-center text-sm text-slate-500">Tidak ada akun yang cocok.</p>
  @endforelse

  @if ($akun->hasPages())
    <div class="flex items-center justify-between border-t border-slate-100 px-7 py-3 text-xs text-slate-500">
      <span>{{ $akun->firstItem() }} - {{ $akun->lastItem() }} dari {{ $akun->total() }} akun</span>
      <span class="flex gap-2">
        @if ($akun->previousPageUrl())<a href="{{ $akun->previousPageUrl() }}#akun" class="rounded-lg bg-brand-50 px-3 py-1.5 font-semibold text-slate-600 hover:bg-brand-100">‹ Sebelumnya</a>@endif
        @if ($akun->nextPageUrl())<a href="{{ $akun->nextPageUrl() }}#akun" class="rounded-lg bg-brand-50 px-3 py-1.5 font-semibold text-slate-600 hover:bg-brand-100">Berikutnya ›</a>@endif
      </span>
    </div>
  @endif
</section>
@endsection
