@extends('layouts.dashboard')
@section('judul', 'Ringkasan')
@section('isi')
<h1 class="text-3xl font-extrabold text-slate-900">Selamat datang, {{ \Illuminate\Support\Str::before(auth()->user()->name, ' ') }}!</h1>
<p class="mt-1 text-slate-500">Mari lanjutkan perkembangan kariermu hari ini.</p>

<div class="mt-6 grid gap-5 lg:grid-cols-[2fr_1fr]">
  <section class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-[#4a1fe0] to-brand p-8 text-white">
    <p class="text-[11px] font-bold uppercase tracking-widest text-white/70">Kelengkapan profil</p>
    <h2 class="mt-3 max-w-md text-2xl font-extrabold">{{ $lengkap >= 100 ? 'Profilmu sudah lengkap!' : 'Lengkapi profilmu agar mentor mengenalmu' }}</h2>
    <p class="mt-2 max-w-md text-sm text-white/70">Isi minat, kota, dan bio agar mentor lebih mudah memahami tujuanmu.</p>
    <span class="absolute right-8 top-8 text-sm font-bold">{{ $lengkap }}%</span>
    <div class="mt-6 h-2 max-w-sm rounded-full bg-white/20"><div class="h-2 rounded-full bg-white" style="width: {{ $lengkap }}%"></div></div>
        @if ($lengkap < 100)<a href="{{ route('profil.edit') }}" class="relative z-10 mt-5 inline-block rounded-xl bg-white px-5 py-2.5 text-sm font-bold text-brand">Lengkapi profil →</a>@endif
    <img src="{{ asset('img/maskot.png') }}" alt="" class="absolute -bottom-6 right-10 w-28 opacity-95">
  </section>

  <section class="flex flex-col rounded-3xl bg-white p-6 shadow-sm ring-1 ring-slate-100">
    @if ($sesiTerdekat)
      <span class="self-end rounded-full bg-amber-50 px-3 py-1 text-xs font-bold text-amber-700">Dikonfirmasi</span>
      <h3 class="mt-3 text-lg font-bold">Sesi bersama {{ $sesiTerdekat->mentor->name }}</h3>
      <p class="mt-1 text-sm text-slate-500">{{ $sesiTerdekat->tanggal->translatedFormat('l, j M') }}, {{ substr($sesiTerdekat->jam_mulai, 0, 5) }}–{{ substr($sesiTerdekat->jam_selesai, 0, 5) }}</p>
      <a href="{{ route('mentoring.index') }}" class="mt-auto pt-6 text-sm font-bold text-emerald-800">Lihat detail sesi →</a>
    @else
      <h3 class="text-lg font-bold">Belum ada sesi terdekat</h3>
      <p class="mt-1 text-sm text-slate-500">Sesi yang sudah dikonfirmasi mentor akan muncul di sini.</p>
      <a href="{{ route('mentoring.index') }}" class="mt-auto pt-6 text-sm font-bold text-brand">Cari mentor →</a>
    @endif
  </section>
</div>

<div class="mt-5 grid gap-5 md:grid-cols-3">
  @foreach ([[$progres.'%', 'Progres peta karier'], [$aktif.' / 2', 'Sesi aktif (maks. 2)'], [$selesai, 'Sesi mentoring selesai']] as [$n, $l])
    <div class="rounded-3xl bg-white p-6 shadow-sm ring-1 ring-slate-100"><p class="text-3xl font-extrabold text-slate-900">{{ $n }}</p><p class="mt-1 text-sm text-slate-500">{{ $l }}</p></div>
  @endforeach
</div>

<section class="mt-5 rounded-3xl bg-white p-6 shadow-sm ring-1 ring-slate-100">
  <div class="flex justify-between"><div><h3 class="font-bold">Langkah karier berikutnya</h3>
    <p class="text-sm text-slate-400">{{ $rencana ? 'Tujuan: '.$rencana->tujuan : 'Kamu belum punya tujuan karier.' }}</p></div>
    <a href="{{ route('peta-karier.index') }}" class="text-sm font-bold text-emerald-800">Lihat peta</a></div>
  <div class="mt-4 h-2 rounded-full bg-slate-100"><div class="h-2 rounded-full bg-emerald-800" style="width: {{ $progres }}%"></div></div>
  @if ($langkahBerikut)
    <div class="mt-5 flex items-center gap-3"><span class="h-8 w-8 rounded-full border-2 border-emerald-800"></span>
      <div><p class="text-sm font-bold">{{ $langkahBerikut->judul }}</p>
        @if ($langkahBerikut->target_tanggal)<p class="text-xs text-slate-400">Target {{ $langkahBerikut->target_tanggal->translatedFormat('j F Y') }}</p>@endif</div></div>
  @elseif ($rencana)<p class="mt-5 text-sm text-slate-500">Semua langkah sudah selesai. Hebat!</p>
  @else <a href="{{ route('peta-karier.index') }}" class="mt-5 inline-block text-sm font-bold text-brand">Buat tujuan karier →</a>@endif
</section>
@endsection
