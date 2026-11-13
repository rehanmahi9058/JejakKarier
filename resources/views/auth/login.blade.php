@extends('layouts.auth')
@section('judul', 'Masuk')
@section('isi')
<div x-data="{ tab: '{{ old('tab', 'pengguna') }}', lihat: false }">
  <p class="text-[11px] font-bold uppercase tracking-wider text-brand">Selamat datang kembali</p>
  <h1 class="mt-2 text-3xl font-extrabold text-slate-900">Masuk ke akunmu</h1>
  <p class="mt-1 text-sm text-slate-500">Lanjutkan membangun JejakKarier profesionalmu.</p>

  <div class="mt-6 inline-flex w-72 rounded-xl border border-slate-100 bg-slate-50 p-1 text-sm font-semibold">
    @foreach (['pengguna' => 'Pengguna', 'mentor' => 'Mentor'] as $k => $v)
      <button type="button" @click="tab='{{ $k }}'" :class="tab==='{{ $k }}' ? 'bg-white text-slate-900 shadow' : 'text-slate-500'" class="flex-1 rounded-lg py-2">{{ $v }}</button>
    @endforeach
  </div>

  <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-4">
    @csrf <input type="hidden" name="tab" :value="tab">
    @if ($errors->any())<div class="rounded-xl bg-red-50 px-4 py-3 text-sm text-red-700">{{ $errors->first() }}</div>@endif
    <label class="block text-xs font-semibold">Alamat email
      <input type="email" name="email" value="{{ old('email') }}" placeholder="nama@email.com" required class="mt-2 w-full rounded-xl border-slate-200 px-4 py-3 text-sm focus:border-brand focus:ring-brand"></label>
    <div>
      <div class="flex justify-between text-xs font-semibold"><span>Kata sandi</span><a href="#" class="text-brand">Lupa kata sandi?</a></div>
      <div class="relative mt-2">
        <input :type="lihat ? 'text' : 'password'" name="password" placeholder="Minimal 8 karakter" required class="w-full rounded-xl border-slate-200 px-4 py-3 pr-11 text-sm focus:border-brand focus:ring-brand">
        <button type="button" @click="lihat=!lihat" class="absolute right-3 top-3 text-slate-400" aria-label="Lihat atau sembunyikan kata sandi">👁</button>
      </div>
    </div>
    <label class="flex items-center gap-2 text-xs text-slate-600"><input type="checkbox" name="ingat" class="rounded border-slate-300 text-brand"> Ingat saya</label>
    <button class="w-full rounded-xl bg-brand py-3.5 text-sm font-bold text-white shadow-lg shadow-brand/30 hover:bg-brand-600">Masuk →</button>
  </form>
  <p class="mt-8 text-center text-xs text-slate-500">Belum punya akun? <a href="{{ route('register') }}" class="font-bold text-brand">Daftar gratis</a></p>
</div>
@endsection
