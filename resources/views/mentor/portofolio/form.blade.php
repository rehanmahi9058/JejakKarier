@extends('layouts.dashboard')
@section('judul', $entri->exists ? 'Ubah Entri' : 'Tambah Entri')
@section('isi')
@php($inp = 'mt-2 w-full rounded-xl border-slate-200 px-4 py-3 text-sm placeholder:text-slate-400 focus:border-brand focus:ring-brand')
<a href="{{ route('mentor.portofolio.index') }}" class="text-sm font-semibold text-brand">← Kembali ke Portofolio Saya</a>
<h1 class="mt-3 text-3xl font-extrabold text-slate-900">{{ $entri->exists ? 'Ubah entri portofolio' : 'Tambah entri portofolio' }}</h1>
<p class="mt-1 text-sm text-slate-500">Hanya entri berstatus <b>terbit</b> yang tampil di katalog publik.</p>

<form method="POST" enctype="multipart/form-data" x-data="{ pratinjau: null }" class="mt-6 max-w-3xl space-y-5 rounded-3xl bg-white p-8 shadow-sm ring-1 ring-slate-100"
      action="{{ $entri->exists ? route('mentor.portofolio.update', $entri) : route('mentor.portofolio.store') }}">
  @csrf @if ($entri->exists) @method('PUT') @endif

  <label class="block text-sm font-semibold">Judul
    <input name="judul" value="{{ old('judul', $entri->judul) }}" required maxlength="160" placeholder="mis. Sistem Inventaris UMKM" class="{{ $inp }}">
    @error('judul')<span class="mt-1 block text-xs font-normal text-red-600">{{ $message }}</span>@enderror</label>

  <div class="grid gap-4 sm:grid-cols-2">
    <label class="block text-sm font-semibold">Jenis
      <select name="jenis" class="{{ $inp }}">@foreach (\App\Http\Controllers\PortofolioController::JENIS as $k => $v)<option value="{{ $k }}" @selected(old('jenis', $entri->jenis) === $k)>{{ $v }}</option>@endforeach</select>
      @error('jenis')<span class="mt-1 block text-xs font-normal text-red-600">{{ $message }}</span>@enderror</label>
    <label class="block text-sm font-semibold">Tautan (opsional)
      <input type="url" name="tautan" value="{{ old('tautan', $entri->tautan) }}" placeholder="https://..." class="{{ $inp }}">
      @error('tautan')<span class="mt-1 block text-xs font-normal text-red-600">{{ $message }}</span>@enderror</label>
    <label class="block text-sm font-semibold">Tanggal mulai
      <input type="date" name="tanggal_mulai" value="{{ old('tanggal_mulai', $entri->tanggal_mulai?->toDateString()) }}" class="{{ $inp }}">
      @error('tanggal_mulai')<span class="mt-1 block text-xs font-normal text-red-600">{{ $message }}</span>@enderror</label>
    <label class="block text-sm font-semibold">Tanggal selesai
      <input type="date" name="tanggal_selesai" value="{{ old('tanggal_selesai', $entri->tanggal_selesai?->toDateString()) }}" class="{{ $inp }}">
      @error('tanggal_selesai')<span class="mt-1 block text-xs font-normal text-red-600">{{ $message }}</span>@enderror</label>
  </div>

  <label class="block text-sm font-semibold">Deskripsi
    <textarea name="deskripsi" rows="5" maxlength="2000" placeholder="Ceritakan masalah, peranmu, dan hasilnya..." class="{{ $inp }}">{{ old('deskripsi', $entri->deskripsi) }}</textarea>
    @error('deskripsi')<span class="mt-1 block text-xs font-normal text-red-600">{{ $message }}</span>@enderror</label>

  <div>
    <p class="text-sm font-semibold">Gambar utama</p>
    <div class="mt-2 flex flex-wrap items-center gap-4">
      <div class="grid h-24 w-32 place-items-center overflow-hidden rounded-2xl bg-brand-50 text-xs text-slate-400">
        <img x-show="pratinjau" x-cloak :src="pratinjau" alt="Pratinjau" class="h-full w-full object-cover">
        @if ($entri->gambar)<img x-show="!pratinjau" src="{{ asset('storage/'.$entri->gambar) }}" alt="Gambar saat ini" class="h-full w-full object-cover">@else<span x-show="!pratinjau">Belum ada</span>@endif
      </div>
      <input type="file" name="gambar" accept="image/jpeg,image/png" @change="pratinjau = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : null"
             class="text-sm text-slate-500 file:mr-3 file:rounded-xl file:border-0 file:bg-brand-100 file:px-4 file:py-2.5 file:text-sm file:font-bold file:text-brand">
    </div>
    <p class="mt-1 text-xs text-slate-400">JPG atau PNG, maksimal 2 MB.{{ $entri->exists && $entri->gambar ? ' Kosongkan jika tidak ingin mengganti.' : '' }}</p>
    @error('gambar')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
  </div>

  <div>
    <p class="text-sm font-semibold">Keterampilan terkait</p>
    <div class="mt-2 flex flex-wrap gap-2">
      @foreach ($semuaSkill as $k)
        <label class="cursor-pointer"><input type="checkbox" name="skill[]" value="{{ $k->id }}" class="peer sr-only" @checked(in_array($k->id, old('skill', $dipilih)))>
          <span class="inline-block rounded-full bg-brand-50 px-4 py-1.5 text-xs font-semibold text-slate-600 peer-checked:bg-brand peer-checked:text-white">{{ $k->nama }}</span></label>
      @endforeach
    </div>
    @error('skill.*')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
  </div>

  <div>
    <p class="text-sm font-semibold">Status</p>
    <div class="mt-2 grid gap-3 sm:grid-cols-2">
      @foreach (['draf' => ['Draf', 'Disimpan, belum tampil di katalog.'], 'terbit' => ['Terbit', 'Tampil di katalog (jika syarat kurasi terpenuhi).']] as $k => [$t, $d])
        <label class="cursor-pointer"><input type="radio" name="status" value="{{ $k }}" class="peer sr-only" @checked(old('status', $entri->status) === $k)>
          <span class="block rounded-2xl border border-slate-200 p-4 peer-checked:border-brand peer-checked:bg-brand-50"><b class="text-sm">{{ $t }}</b><span class="mt-1 block text-xs text-slate-500">{{ $d }}</span></span></label>
      @endforeach
    </div>
    @error('status')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
  </div>

  <div class="flex justify-end gap-3 pt-2 text-sm font-bold">
    <a href="{{ route('mentor.portofolio.index') }}" class="rounded-xl px-6 py-3 text-slate-500 ring-1 ring-slate-200 hover:bg-slate-50">Batal</a>
    <button class="rounded-xl bg-brand px-8 py-3 text-white shadow-lg shadow-brand/30 hover:bg-brand-600">Simpan</button>
  </div>
</form>
@endsection
