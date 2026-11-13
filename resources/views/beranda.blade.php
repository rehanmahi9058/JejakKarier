@extends('layouts.publik')
@section('judul', 'Beranda')
@section('isi')
<section class="mx-auto grid max-w-6xl items-center gap-10 px-6 pt-16 lg:grid-cols-2">
  <div>
    <h1 class="text-5xl font-extrabold leading-[1.1] text-slate-900">Tunjukkan kemampuanmu, <span class="text-brand">temukan arah karirmu</span></h1>
    <p class="mt-5 max-w-md text-slate-600">Wadah rekam JejakKarier profesional untuk talenta muda, fresh graduate, dan mahasiswa di Indonesia. Pesan sesi mentoring 1-on-1 langsung dengan pemilik portofolio.</p>
    <a href="{{ route('katalog') }}" class="mt-6 inline-block rounded-full bg-brand-100 px-6 py-3 text-sm font-bold text-brand">👁 Lihat Katalog Talenta</a>
    <div class="mt-8 inline-flex gap-8 rounded-2xl bg-white px-6 py-4 shadow-sm ring-1 ring-brand-100">
      <div><p class="text-2xl font-extrabold">{{ number_format($stat['portofolio']) }}</p><p class="text-[11px] text-slate-500">KARYA TERBIT</p></div>
      <div><p class="text-2xl font-extrabold text-brand">{{ number_format($stat['mentor']) }}</p><p class="text-[11px] text-slate-500">MENTOR TERVERIFIKASI</p></div>
    </div>
  </div>
  <img src="{{ asset('img/maskot.png') }}" alt="Maskot" class="mx-auto w-72">
</section>

<section class="mx-auto mt-24 max-w-6xl px-6">
  <h2 class="text-3xl font-extrabold">Dirancang Khusus untuk Akselerasi Kariermu</h2>
  <div class="mt-8 grid gap-6 md:grid-cols-2">
    <div class="rounded-3xl bg-white p-8 shadow-sm ring-1 ring-brand-100"><h3 class="text-lg font-bold">Peta Karier Terstruktur</h3>
      <p class="mt-2 text-sm text-slate-600">Pecah impian kariermu menjadi langkah nyata. Status otomatis menjadi “Tercapai” saat semua langkah selesai.</p></div>
    <div class="rounded-3xl bg-white p-8 shadow-sm ring-1 ring-brand-100"><h3 class="text-lg font-bold">1-on-1 Mentoring Terverifikasi</h3>
      <p class="mt-2 text-sm text-slate-600">Pilih mentor dari bukti kerjanya, lalu pesan sesi pada slot yang tersedia.</p></div>
  </div>
</section>

<section class="mx-auto mt-24 max-w-6xl px-6">
  <div class="flex items-end justify-between"><div><h2 class="text-3xl font-extrabold">Portofolio Pilihan Terbaru</h2>
    <p class="text-sm text-slate-500">Hanya mentor dengan minimal 1 entri terbit dan 3 keterampilan.</p></div>
    <a href="{{ route('katalog') }}" class="text-sm font-bold text-brand">Jelajahi Semua Portofolio →</a></div>
  <div class="mt-6 grid gap-5 md:grid-cols-3">@foreach ($terbaru as $m) @include('partials.kartu-mentor', ['m' => $m]) @endforeach</div>
</section>

<section class="mx-auto mt-24 max-w-6xl px-6">
  <div class="flex flex-wrap items-center justify-between gap-6 rounded-[2rem] bg-gradient-to-r from-[#4a2fd9] to-brand p-12 text-white">
    <div class="max-w-xl"><h2 class="text-3xl font-extrabold">Punya Pengalaman di Industri? Jadilah Mentor Terverifikasi di JejakKarier</h2></div>
    <a href="{{ route('register') }}" class="rounded-full bg-white px-8 py-3.5 text-sm font-bold text-brand">Daftar Menjadi Mentor</a>
  </div>
</section>
@endsection
