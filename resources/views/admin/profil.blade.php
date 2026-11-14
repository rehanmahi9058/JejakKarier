@extends('layouts.dashboard')
@section('judul', 'Profil Saya')
@section('isi')
@php
  $inisial = collect(explode(' ', $u->name))->take(2)->map(fn ($k) => mb_strtoupper(mb_substr($k, 0, 1)))->implode('');
  $input = 'mt-2 w-full rounded-xl border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 placeholder-slate-400 focus:border-brand focus:ring-brand';
@endphp
<span class="inline-block rounded-md bg-brand-50 px-3 py-1 text-[11px] font-bold uppercase tracking-widest text-brand">Administrator</span>
<h1 class="mt-3 text-4xl font-extrabold tracking-tight text-[#1a1446]">Profil Saya</h1>
<p class="mt-1 text-sm text-slate-500">Ubah nama, email, dan kata sandi akun administrator.</p>

<form method="POST" action="{{ route('admin.profil.ubah') }}" class="mt-6 rounded-3xl bg-white p-8 shadow-sm ring-1 ring-slate-100" x-data="{ baru: '' }">
  @csrf @method('PUT')
  <div class="flex flex-wrap items-center gap-6 border-b border-slate-100 pb-8">
    <span class="grid h-20 w-20 shrink-0 place-items-center rounded-full bg-brand-100 text-2xl font-extrabold text-brand ring-2 ring-brand/20 ring-offset-2">{{ $inisial }}</span>
    <div>
      <div class="flex flex-wrap items-center gap-3">
        <p class="text-xl font-extrabold text-[#1a1446]">{{ $u->name }}</p>
        <span class="rounded-full bg-brand-50 px-3 py-1 text-xs font-bold text-brand">Administrator</span>
      </div>
      <p class="mt-1 text-xs text-slate-400">Akun utama sistem manajemen portal JejakKarier</p>
    </div>
  </div>

  <div class="mt-8 grid gap-x-6 gap-y-6 md:grid-cols-2">
    <div><label for="name" class="block text-sm font-semibold text-[#1a1446]">Nama</label>
      <input id="name" name="name" value="{{ old('name', $u->name) }}" required class="{{ $input }}">
      @error('name')<p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p>@enderror</div>
    <div><label for="email" class="block text-sm font-semibold text-[#1a1446]">Email</label>
      <input id="email" type="email" name="email" value="{{ old('email', $u->email) }}" required class="{{ $input }}">
      @error('email')<p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p>@enderror</div>
    <div><label for="password" class="block text-sm font-semibold text-[#1a1446]">Kata sandi baru</label>
      <input id="password" type="password" name="password" x-model="baru" autocomplete="new-password" placeholder="Minimal 8 karakter" class="{{ $input }}">
      @error('password')<p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p>@enderror</div>
    <div><label for="password_confirmation" class="block text-sm font-semibold text-[#1a1446]">Konfirmasi kata sandi</label>
      <input id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password" placeholder="Ulangi kata sandi" class="{{ $input }}"></div>
    <div class="md:col-span-2" x-show="baru.length > 0 || {{ $errors->has('password_lama') ? 'true' : 'false' }}" x-cloak>
      <label for="password_lama" class="block text-sm font-semibold text-[#1a1446]">Kata sandi saat ini <span class="font-normal text-slate-400">(untuk konfirmasi penggantian)</span></label>
      <input id="password_lama" type="password" name="password_lama" autocomplete="current-password" class="{{ $input }} md:max-w-[calc(50%-0.75rem)]">
      @error('password_lama')<p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p>@enderror</div>
  </div>

  <div class="mt-8 flex justify-end">
    <button class="rounded-xl bg-brand px-7 py-3 text-sm font-bold text-white shadow-lg shadow-brand/30 hover:bg-brand-600">Simpan perubahan</button>
  </div>
</form>
@endsection
