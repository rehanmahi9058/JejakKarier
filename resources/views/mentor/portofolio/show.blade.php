@extends('layouts.dashboard')
@section('judul', $entri->judul)
@section('isi')
@php($jenisLabel = \App\Http\Controllers\PortofolioController::JENIS)
<a href="{{ route('mentor.portofolio.index') }}" class="text-sm font-semibold text-brand">← Kembali ke Portofolio Saya</a>
<article class="mt-4 max-w-3xl rounded-3xl bg-white p-8 shadow-sm ring-1 ring-slate-100">
  <div class="flex flex-wrap items-center gap-2">
    <span class="rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase {{ $entri->status === 'terbit' ? 'bg-brand-100 text-brand' : 'bg-slate-100 text-slate-500' }}">● {{ $entri->status }}</span>
    <span class="text-sm font-semibold text-slate-500">{{ $jenisLabel[$entri->jenis] }}
      @if ($entri->tanggal_mulai) · {{ $entri->tanggal_mulai->translatedFormat('M Y') }} – {{ $entri->tanggal_selesai?->translatedFormat('M Y') ?? 'sekarang' }}@endif</span>
  </div>
  <h1 class="mt-2 text-3xl font-extrabold text-slate-900">{{ $entri->judul }}</h1>
  @if ($entri->gambar)<img src="{{ asset('storage/'.$entri->gambar) }}" alt="{{ $entri->judul }}" class="mt-5 w-full rounded-2xl">@endif
  <p class="mt-5 whitespace-pre-line text-slate-600">{{ $entri->deskripsi ?: 'Belum ada deskripsi.' }}</p>
  <div class="mt-5 flex flex-wrap gap-2">@foreach ($entri->keterampilan as $k)<span class="rounded-full bg-brand-50 px-3 py-1 text-xs font-semibold text-brand">{{ $k->nama }}</span>@endforeach</div>
  @if ($entri->tautan)<a href="{{ $entri->tautan }}" target="_blank" rel="noopener" class="mt-5 inline-block text-sm font-bold text-brand">Buka tautan ↗</a>@endif
  <div class="mt-8 flex gap-3 text-sm font-bold"><a href="{{ route('mentor.portofolio.edit', $entri) }}" class="rounded-xl bg-brand px-6 py-3 text-white">Ubah entri</a></div>
</article>
@endsection
