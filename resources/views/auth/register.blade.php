@extends('layouts.auth')
@section('judul', 'Daftar')
@section('isi')
<div x-data="{ peran: '{{ old('peran', 'pengguna') }}', lihat: false }">
  <span class="rounded-md bg-brand-100 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-brand">Mulai perjalananmu</span>
  <h1 class="mt-3 text-3xl font-extrabold text-slate-900">Buat akun JejakKarier</h1>
  <p class="mt-1 text-sm text-slate-500">Gratis, mudah, dan hanya butuh beberapa menit.</p>

  <div class="mt-6 flex rounded-xl border border-slate-100 bg-slate-50 p-1 text-sm font-semibold">
    @foreach (['pengguna' => 'Pengguna', 'mentor' => 'Mentor'] as $k => $v)
      <button type="button" @click="peran='{{ $k }}'" :class="peran==='{{ $k }}' ? 'bg-white text-slate-900 shadow' : 'text-slate-500'" class="flex-1 rounded-lg py-2.5">{{ $v }}</button>
    @endforeach
  </div>

  <form method="POST" action="{{ route('register') }}" class="mt-5 space-y-4">
    @csrf <input type="hidden" name="peran" :value="peran">
    @foreach (['name' => ['Nama lengkap', 'text', 'Masukkan nama lengkap'], 'email' => ['Alamat email', 'email', 'nama@email.com']] as $f => [$l, $t, $p])
      <label class="block text-xs font-semibold">{{ $l }}
        <input type="{{ $t }}" name="{{ $f }}" value="{{ old($f) }}" placeholder="{{ $p }}" class="mt-2 w-full rounded-xl border-slate-200 px-4 py-3 text-sm focus:border-brand focus:ring-brand">
        @error($f)<span class="mt-1 block font-normal text-red-600">{{ $message }}</span>@enderror</label>
    @endforeach
    <label class="block text-xs font-semibold">Kata sandi
      <div class="relative mt-2">
        <input :type="lihat ? 'text' : 'password'" name="password" placeholder="Minimal 8 karakter" class="w-full rounded-xl border-slate-200 px-4 py-3 pr-11 text-sm focus:border-brand focus:ring-brand">
        <button type="button" @click="lihat=!lihat" class="absolute right-3 top-3 text-slate-400" aria-label="Lihat atau sembunyikan kata sandi">👁</button>
      </div>
      @error('password')<span class="mt-1 block font-normal text-red-600">{{ $message }}</span>@enderror</label>
    <label class="block text-xs font-semibold">Konfirmasi kata sandi
      <input :type="lihat ? 'text' : 'password'" name="password_confirmation" placeholder="Ulangi kata sandi" class="mt-2 w-full rounded-xl border-slate-200 px-4 py-3 text-sm focus:border-brand focus:ring-brand"></label>
    <label class="flex items-start gap-2 text-xs text-slate-600"><input type="checkbox" name="setuju" value="1" class="mt-0.5 rounded border-slate-300 text-brand">
      <span>Saya menyetujui <a href="#" class="text-brand">Syarat Penggunaan</a> dan <a href="#" class="text-brand">Kebijakan Privasi</a> JejakKarier.</span></label>
    @error('setuju')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
    <button class="w-full rounded-xl bg-brand py-3.5 text-sm font-bold text-white shadow-lg shadow-brand/30 hover:bg-brand-600">Buat akun →</button>
  </form>
  <p class="mt-6 text-center text-sm text-slate-500">Sudah punya akun? <a href="{{ route('login') }}" class="font-bold text-brand">Masuk</a></p>
</div>
@endsection
