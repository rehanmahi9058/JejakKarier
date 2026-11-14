<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>@yield('judul', 'JejakKarier') · JejakKarier</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gradient-to-b from-brand-50 to-white text-slate-800 antialiased">
<header class="sticky top-0 z-30 border-b border-brand-100 bg-white/80 backdrop-blur">
  <nav class="mx-auto flex max-w-6xl items-center justify-between px-6 py-3">
    @include('partials.logo', ['sub' => 'Career & Mentoring'])
    <div class="hidden items-center gap-2 text-sm font-semibold md:flex">
      @php
        $menuPublik = [['beranda', 'Beranda', 'beranda'], ['katalog', 'Katalog', 'katalog*']];
        // Cari Mentor dan Peta Karier hanya untuk pengguna/tamu; mentor tidak melihatnya.
        if (! auth()->user()?->adalahMentor()) {
          $menuPublik[] = ['mentoring.index', 'Cari Mentor', 'mentoring*'];
          $menuPublik[] = ['peta-karier.index', 'Peta Karier', 'peta-karier*'];
        }
      @endphp
      @foreach ($menuPublik as [$rute, $label, $pola])
        <a href="{{ route($rute) }}" class="rounded-lg px-3 py-1.5 {{ request()->routeIs($pola) ? 'bg-brand-100 text-brand' : 'text-slate-600 hover:text-brand' }}">{{ $label }}</a>
      @endforeach
    </div>
    <div class="flex items-center gap-3 text-sm font-semibold">
      @auth
        <a href="{{ route('dashboard') }}" class="rounded-full bg-brand px-5 py-2 text-white shadow-lg shadow-brand/30">Dashboard</a>
      @else
        <a href="{{ route('login') }}" class="text-slate-600">Masuk</a>
        <a href="{{ route('register') }}" class="rounded-full bg-brand px-5 py-2 text-white shadow-lg shadow-brand/30">Daftar</a>
      @endauth
    </div>
  </nav>
</header>
<main>@yield('isi')</main>
@include('partials.footer')
</body>
</html>
