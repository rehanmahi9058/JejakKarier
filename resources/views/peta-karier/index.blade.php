@extends('layouts.dashboard')
@section('judul', 'Peta Karier')
@section('isi')
@php($stl = ['belum' => 'bg-slate-100 text-slate-600', 'proses' => 'bg-amber-100 text-amber-700', 'selesai' => 'bg-emerald-100 text-emerald-700'])
<div x-data="{ tambah: {{ old('judul') ? 'true' : 'false' }}, ubah: {{ old('tujuan') ? 'true' : 'false' }} }">
  <div class="flex flex-wrap items-center justify-between gap-4">
    <div class="flex items-center gap-4">
      <img src="{{ asset('img/maskot.png') }}" alt="" class="h-20 w-20 object-contain">
      <div><h1 class="text-4xl font-extrabold tracking-tight text-slate-900">Tujuan dan langkah kariermu</h1>
        <p class="mt-1 max-w-lg text-slate-500">Ubah rencana menjadi langkah nyata yang terukur untuk mencapai impian profesionalmu.</p></div>
    </div>
    @if ($rencana)<button @click="tambah = true" class="rounded-full bg-brand px-6 py-3 text-sm font-bold text-white shadow-lg shadow-brand/30">+ Tambah Langkah</button>@endif
  </div>

  <div class="mt-8 grid gap-6 xl:grid-cols-[1fr_320px]">
    <div class="space-y-6">
      {{-- Kartu tujuan --}}
      @if (! $rencana)
        <form method="POST" action="{{ route('peta-karier.tujuan') }}" class="rounded-3xl bg-white p-8 shadow-sm ring-1 ring-slate-100">@csrf
          <h2 class="text-xl font-bold">Mulai dengan tujuan utamamu</h2>
          <input name="tujuan" value="{{ old('tujuan') }}" placeholder="mis. Menjadi Product Designer" required class="mt-4 w-full rounded-xl border-slate-200 text-sm focus:border-brand focus:ring-brand">
          <input type="date" name="target_tanggal" value="{{ old('target_tanggal') }}" class="mt-3 w-full rounded-xl border-slate-200 text-sm focus:border-brand focus:ring-brand">
          <button class="mt-4 rounded-xl bg-brand px-6 py-3 text-sm font-bold text-white">Simpan tujuan</button>
        </form>
      @else
        <section class="rounded-3xl bg-gradient-to-br from-[#4a1fe0] to-brand p-8 text-white shadow-xl shadow-brand/20">
          <div class="flex items-start justify-between">
            <div><p class="text-[11px] font-bold uppercase tracking-widest text-white/70">Tujuan utama · <span class="{{ $rencana->status === 'tercapai' ? 'text-amber-300' : 'text-emerald-300' }}">● {{ ucfirst($rencana->status) }}</span></p>
              <h2 class="mt-2 text-3xl font-extrabold">{{ $rencana->tujuan }}</h2>
              @if ($rencana->target_tanggal)<p class="mt-2 text-sm text-white/80">Target tercapai pada <b>{{ $rencana->target_tanggal->translatedFormat('F Y') }}</b></p>@endif</div>
            <button @click="ubah = !ubah" class="grid h-10 w-10 place-items-center rounded-xl bg-white/15" aria-label="Ubah tujuan">✎</button>
          </div>
          <form x-show="ubah" x-cloak method="POST" action="{{ route('peta-karier.tujuan') }}" class="mt-4 grid gap-3 text-slate-800 md:grid-cols-[1fr_180px_auto]">@csrf
            <input name="tujuan" value="{{ old('tujuan', $rencana->tujuan) }}" required class="rounded-xl border-0 text-sm">
            <input type="date" name="target_tanggal" value="{{ old('target_tanggal', $rencana->target_tanggal?->toDateString()) }}" class="rounded-xl border-0 text-sm">
            <button class="rounded-xl bg-white px-5 text-sm font-bold text-brand">Simpan</button>
          </form>
          <p class="mt-8 text-sm text-white/80">Kemajuan Peta Jalan</p>
          <div class="mt-2 h-3 rounded-full bg-white/20"><div class="h-3 rounded-full bg-white/90" style="width: {{ $progres }}%"></div></div>
          <div class="mt-3 flex flex-wrap justify-between gap-2 text-xs text-white/80">
            <span>{{ $selesai }} dari {{ $total }} langkah berhasil diselesaikan ({{ $progres }}%)</span>
            <span>ⓘ Otomatis berstatus “Tercapai” saat 100%</span></div>
        </section>

        {{-- Daftar langkah --}}
        <section class="rounded-3xl bg-white p-8 shadow-sm ring-1 ring-slate-100">
          <h2 class="text-2xl font-extrabold">Langkah yang Direncanakan</h2>
          <p class="text-sm text-slate-500">Ubah status pada setiap langkah untuk memperbarui progres kariermu.</p>
          <div class="mt-6 space-y-3">
            @forelse ($rencana->langkah as $l)
              <div class="flex flex-wrap items-center gap-3 rounded-2xl bg-brand-50/60 p-4">
                <span class="grid h-10 w-10 place-items-center rounded-full {{ ['belum' => 'bg-brand-100 text-brand', 'proses' => 'bg-amber-100 text-amber-600', 'selesai' => 'bg-emerald-400 text-white'][$l->status] }}">{{ $l->status === 'selesai' ? '✓' : ($l->status === 'proses' ? '◔' : '○') }}</span>
                <div class="min-w-0 flex-1"><p class="font-bold {{ $l->status === 'selesai' ? 'text-slate-400 line-through' : 'text-slate-900' }}">{{ $l->judul }}</p>
                  @if ($l->target_tanggal)<p class="text-xs text-slate-500">📅 {{ $l->target_tanggal->translatedFormat('j F Y') }}</p>@endif</div>
                <form method="POST" action="{{ route('peta-karier.langkah.ubah', $l) }}">@csrf @method('PATCH')
                  <select name="status" onchange="this.form.submit()" class="rounded-full border-0 py-1.5 pl-3 pr-8 text-xs font-bold {{ $stl[$l->status] }}">
                    @foreach (['belum' => 'Belum', 'proses' => 'Proses', 'selesai' => 'Selesai'] as $k => $v)<option value="{{ $k }}" @selected($l->status === $k)>{{ $v }}</option>@endforeach
                  </select></form>
                <form method="POST" action="{{ route('peta-karier.langkah.hapus', $l) }}" onsubmit="return confirm('Hapus langkah ini?')">@csrf @method('DELETE')
                  <button class="text-slate-400 hover:text-red-600" aria-label="Hapus langkah">🗑</button></form>
              </div>
            @empty <p class="text-sm text-slate-500">Belum ada langkah. Klik “Tambah Langkah” untuk memulai.</p>
            @endforelse
          </div>
        </section>
      @endif
    </div>

    {{-- Kolom kanan --}}
    <div class="space-y-6">
      @if ($rekom)
      <section class="rounded-3xl bg-white p-6 shadow-sm ring-1 ring-slate-100">
        <div class="flex items-center justify-between"><h3 class="font-bold">Mentor Rekomendasi</h3>
          <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-[11px] font-bold text-emerald-700">{{ $rekom->slot_tersedia_count ? 'Tersedia' : 'Slot penuh' }}</span></div>
        <div class="mt-4 flex items-center gap-3"><span class="grid h-14 w-14 place-items-center rounded-2xl bg-brand-100 font-bold text-brand">{{ mb_substr($rekom->name, 0, 1) }}</span>
          <div><p class="font-bold">{{ $rekom->name }} <span class="text-emerald-600">✔</span></p><p class="text-xs text-slate-500">{{ $rekom->profilMentor->jabatan }}</p></div></div>
        <a href="{{ route('katalog.detail', $rekom) }}" class="mt-5 block rounded-xl bg-brand py-3 text-center text-sm font-bold text-white">📅 Jadwalkan Konsultasi</a>
      </section>
      @endif
      @if ($rencana)
      <section class="rounded-3xl bg-brand-50 p-6">
        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Statistik jalur karier</p>
        <div class="mt-3 grid grid-cols-2 gap-3">
          <div class="rounded-2xl bg-white p-4"><p class="text-2xl font-extrabold text-brand">{{ (int) $rencana->created_at->diffInDays(now()) }}</p><p class="text-xs font-semibold">Hari Berjalan</p></div>
          <div class="rounded-2xl bg-white p-4"><p class="text-2xl font-extrabold text-emerald-700">{{ $selesai }}</p><p class="text-xs font-semibold">Langkah Selesai</p></div>
        </div>
      </section>
      @endif
    </div>
  </div>

  {{-- Modal tambah langkah --}}
  @if ($rencana)
  <div x-show="tambah" x-cloak class="fixed inset-0 z-50 grid place-items-center bg-slate-900/40 p-4" @keydown.escape.window="tambah = false">
    <form method="POST" action="{{ route('peta-karier.langkah.tambah') }}" @click.outside="tambah = false" class="w-full max-w-md rounded-3xl bg-white p-8 shadow-2xl">@csrf
      <h3 class="text-xl font-extrabold">Tambah langkah</h3>
      <label class="mt-4 block text-xs font-semibold">Judul langkah
        <input name="judul" value="{{ old('judul') }}" required maxlength="200" class="mt-2 w-full rounded-xl border-slate-200 text-sm focus:border-brand focus:ring-brand"></label>
      <label class="mt-3 block text-xs font-semibold">Target tanggal
        <input type="date" name="target_tanggal" value="{{ old('target_tanggal') }}" class="mt-2 w-full rounded-xl border-slate-200 text-sm focus:border-brand focus:ring-brand"></label>
      <div class="mt-6 flex justify-end gap-3 text-sm font-bold"><button type="button" @click="tambah = false" class="rounded-xl px-5 py-2.5 text-slate-500">Batal</button>
        <button class="rounded-xl bg-brand px-6 py-2.5 text-white">Simpan</button></div>
    </form>
  </div>
  @endif
</div>
@endsection
