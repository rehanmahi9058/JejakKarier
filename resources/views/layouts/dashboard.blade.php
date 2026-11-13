<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>@yield('judul', 'Dashboard') · JejakKarier</title>
  <style>[x-cloak]{display:none!important}</style>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 text-slate-800 antialiased">
@php
  $u = auth()->user();
  $ikon = [
    'grid' => 'M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z',
    'folder' => 'M2.25 12.75V12A2.25 2.25 0 014.5 9.75h15A2.25 2.25 0 0121.75 12v.75m-8.69-6.44l-2.12-2.12a1.5 1.5 0 00-1.061-.44H4.5A2.25 2.25 0 002.25 6v12a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9a2.25 2.25 0 00-2.25-2.25h-5.379a1.5 1.5 0 01-1.06-.44z',
    'map' => 'M9 6.75V15m6-6v8.25m.503 3.498l4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.317.159-.69.159-1.006 0L9.503 3.252a1.125 1.125 0 00-1.006 0L3.622 5.689C3.24 5.88 3 6.27 3 6.695V19.18c0 .836.88 1.38 1.628 1.006l3.869-1.934c.317-.159.69-.159 1.006 0l4.994 2.497c.317.158.69.158 1.006 0z',
    'users' => 'M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z',
    'cal' => 'M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5',
    'user' => 'M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z',
  ];
  // [label, nama rute atau '#', pola rute aktif, ikon]
  $menu = match ($u->peran) {
    'admin' => [['Ringkasan', 'admin.ringkasan', 'admin*', 'grid'], ['Portofolio', '#', '', 'folder'], ['Profil Saya', 'profil.edit', 'profil*', 'user'], ['Kelola Mentor dan Akun', '#', '', 'users']],
    'mentor' => [['Ringkasan', 'dashboard', 'dashboard', 'grid'], ['Portofolio', '#', '', 'folder'], ['Slot Jadwal', '#', '', 'cal'], ['Mentoring', '#', '', 'users'], ['Profil Saya', 'profil.edit', 'profil*', 'user']],
    default => [['Ringkasan', 'dashboard', 'dashboard', 'grid'], ['Portofolio', 'katalog', 'katalog*', 'folder'], ['Peta Karier', 'peta-karier.index', 'peta-karier*', 'map'], ['Mentoring', 'mentoring.index', 'mentoring*', 'users'], ['Profil Saya', 'profil.edit', 'profil*', 'user']],
  };
  $label = ['admin' => 'Menu Admin', 'mentor' => 'Mentor Portal', 'pengguna' => 'Menu Pengguna'][$u->peran];
@endphp
<div class="flex min-h-screen">
  <aside class="sticky top-0 hidden h-screen w-64 shrink-0 flex-col border-r border-slate-200 bg-white px-5 py-6 lg:flex">
    @include('partials.logo', ['sub' => $label])
    <p class="mt-8 px-2 text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ $label }}</p>
    <nav class="mt-3 space-y-1.5">
      @foreach ($menu as [$nama, $rute, $pola, $ik])
        @php($aktif = $rute !== '#' && request()->routeIs($pola))
        <a href="{{ $rute === '#' ? '#' : route($rute) }}" class="flex items-center gap-3 rounded-xl border px-4 py-2.5 text-sm font-semibold {{ $aktif ? 'border-brand-100 bg-brand-100 text-brand' : 'border-transparent text-slate-600 hover:bg-slate-50' }}">
          <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $ikon[$ik] }}"/></svg>{{ $nama }}
        </a>
      @endforeach
    </nav>
    <form method="POST" action="{{ route('logout') }}" class="mt-auto border-t border-slate-100 pt-4">@csrf
      <button class="flex w-full items-center gap-3 rounded-xl px-4 py-2.5 text-sm font-semibold text-slate-500 hover:bg-slate-50">↪ Keluar</button>
    </form>
  </aside>

  <div class="flex min-w-0 flex-1 flex-col">
    <header class="flex items-center justify-between border-b border-slate-200 bg-white px-8 py-4">
      <span class="text-sm text-slate-500">{{ now()->translatedFormat('l, j F Y') }}</span>
      <a href="{{ route('profil.edit') }}" class="flex items-center gap-3 text-right">
        <div class="leading-tight"><p class="text-sm font-bold text-slate-900">{{ $u->name }}</p><p class="text-xs capitalize text-slate-400">{{ $u->peran }}</p></div>
        <span class="grid h-10 w-10 place-items-center overflow-hidden rounded-full bg-brand-100 font-bold text-brand">@if ($u->fotoUrl())<img src="{{ $u->fotoUrl() }}" alt="" class="h-full w-full object-cover">@else{{ mb_substr($u->name, 0, 1) }}@endif</span>
      </a>
    </header>
    <main class="flex-1 px-6 py-8 lg:px-10">
      @if (session('sukses'))<div class="mb-5 rounded-xl bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">{{ session('sukses') }}</div>@endif
      @if ($errors->any())<div class="mb-5 rounded-xl bg-red-50 px-4 py-3 text-sm text-red-700">{{ $errors->first() }}</div>@endif
      @yield('isi')
    </main>
    @include('partials.footer')
  </div>
</div>
</body>
</html>