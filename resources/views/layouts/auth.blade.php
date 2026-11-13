<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>@yield('judul') · JejakKarier</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-white text-slate-800 antialiased">
<div class="grid min-h-screen lg:grid-cols-2">
  <section class="flex flex-col px-8 py-10 sm:px-16 lg:px-20">
    @include('partials.logo')
    <div class="my-auto w-full max-w-[430px] py-10">@yield('isi')</div>
    <div class="flex justify-between border-t border-slate-100 pt-4 text-xs text-slate-400">
      <span>© {{ date('Y') }} JejakKarier. Hak cipta dilindungi.</span><span>Bantuan &nbsp; Privasi</span>
    </div>
  </section>
  <aside class="relative hidden flex-col justify-between overflow-hidden bg-gradient-to-br from-brand via-[#3b22b8] to-[#1a0f4d] px-14 py-24 text-white lg:flex">
    <div>
      <h2 class="text-4xl font-extrabold leading-tight">“Kita tidak harus melihat seluruh tangga. Cukup ambil langkah pertama.”</h2>
      <div class="mt-8 flex items-center gap-3 border-t border-white/15 pt-6 text-sm">
        <span class="grid h-6 w-6 place-items-center rounded-full bg-white/20">✓</span>
        {{-- Angka dari database, bukan karangan --}}
        <span>Bertumbuh bersama <b>{{ number_format(\App\Models\User::where('aktif', true)->whereIn('peran', ['pengguna', 'mentor'])->count()) }} talenta</b> dan mentor profesional Indonesia</span>
      </div>
    </div>
    <img src="{{ asset('img/maskot.png') }}" alt="Maskot burung hantu JejakKarier" class="mx-auto w-80">
    <div class="border-t border-white/15"></div>
  </aside>
</div>
</body>
</html>
