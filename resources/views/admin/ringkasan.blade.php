@extends('layouts.dashboard')
@section('judul', 'Ringkasan Admin')
@section('isi')
@php
  $namaDepan = \Illuminate\Support\Str::before(auth()->user()->name, ' ');
  $inisial = fn ($n) => collect(explode(' ', $n))->take(2)->map(fn ($k) => mb_strtoupper(mb_substr($k, 0, 1)))->implode('');
  $kartu = [
    [$menunggu, 'Mentor menunggu verifikasi', 'bg-amber-50 text-amber-500', 'M12 6v6l4 2m6-2a10 10 0 11-20 0 10 10 0 0120 0z'],
    [$terverifikasi, 'Mentor terverifikasi', 'bg-brand-50 text-brand', 'M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z'],
    [$aktif, 'Akun aktif', 'bg-brand-50 text-brand', 'M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z'],
  ];
@endphp

<span class="inline-block rounded-md bg-brand-50 px-3 py-1 text-[11px] font-bold uppercase tracking-widest text-brand">Administrator</span>
<h1 class="mt-3 text-3xl font-extrabold tracking-tight text-[#1a1446] sm:text-4xl">Selamat datang, {{ $namaDepan }}!</h1>
<p class="mt-1 text-sm text-slate-500">Ringkasan mentor dan akun di JejakKarier.</p>

<div class="mt-8 grid gap-5 sm:grid-cols-3">
  @foreach ($kartu as [$angka, $ket, $warna, $ikon])
    <div class="rounded-3xl bg-white p-6 shadow-sm ring-1 ring-slate-100">
      <span class="grid h-12 w-12 place-items-center rounded-2xl {{ $warna }}"><svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $ikon }}"/></svg></span>
      <p class="mt-6 text-3xl font-extrabold text-[#1a1446]">{{ $angka }}</p>
      <p class="mt-1 text-sm text-slate-500">{{ $ket }}</p>
    </div>
  @endforeach
</div>

<section class="mt-6 overflow-hidden rounded-3xl bg-white shadow-sm ring-1 ring-slate-100">
  <div class="flex items-center justify-between px-7 py-6">
    <h2 class="text-lg font-bold text-[#1a1446]">Perlu ditinjau</h2>
    <a href="{{ route('admin.akun') }}" class="text-sm font-bold text-brand">Lihat semua ›</a>
  </div>
  @forelse ($tinjau as $p)
    <div class="flex flex-wrap items-center justify-between gap-4 border-t border-slate-100 px-7 py-3">
      <div class="flex min-w-0 items-center gap-4">
        <span class="grid h-12 w-12 shrink-0 place-items-center rounded-xl bg-brand-100 text-sm font-bold text-brand">{{ $inisial($p->user->name) }}</span>
        <div class="min-w-0">
          <p class="font-bold text-[#1a1446]">{{ $p->user->name }}</p>
          <p class="truncate text-xs text-slate-500">{{ $p->user->email }}<span class="mx-1.5 text-slate-300">●</span>{{ $p->jabatan ?: 'Jabatan belum diisi' }}</p>
        </div>
      </div>
      <div class="flex items-center gap-3">
        <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700 ring-1 ring-amber-200"><i class="h-1.5 w-1.5 rounded-full bg-amber-500"></i>Menunggu</span>
        <a href="{{ route('admin.akun') }}#verifikasi" class="rounded-xl bg-[#14112e] px-5 py-2 text-xs font-bold text-white hover:bg-black">Tinjau</a>
      </div>
    </div>
  @empty
    <p class="border-t border-slate-100 px-7 py-10 text-center text-sm text-slate-500">Tidak ada mentor yang menunggu verifikasi.</p>
  @endforelse
</section>

<a href="{{ route('admin.akun') }}" class="mt-6 inline-flex items-center gap-3 rounded-full bg-[#14112e] px-7 py-3.5 text-sm font-bold text-white hover:bg-black">Kelola mentor dan akun <span aria-hidden="true">→</span></a>
@endsection
