@extends('layouts.dashboard')
@section('judul', 'Portofolio Saya')
@section('isi')
@php
  $jenisLabel = \App\Http\Controllers\PortofolioController::JENIS;
  $periode = function ($e) {
      $m = $e->tanggal_mulai?->translatedFormat('M Y'); $s = $e->tanggal_selesai?->translatedFormat('M Y');
      return $m ? ($s ? "$m – $s" : "$m – sekarang") : ($s ?: '—');
  };
  $ke = fn (array $p = []) => route('mentor.portofolio.index', array_filter(array_merge(['tab' => $tab === 'semua' ? null : $tab, 'q' => $cari], $p), fn ($v) => filled($v)));
@endphp

<div class="flex flex-wrap items-start justify-between gap-4">
  <div>
    <p class="flex items-center gap-2 text-[11px] font-bold uppercase tracking-widest text-brand"><span class="h-2 w-2 rounded-full bg-brand"></span>Portofolio Mentor</p>
    <h1 class="mt-2 text-4xl font-extrabold tracking-tight text-slate-900">Portofolio Saya</h1>
    <p class="mt-2 max-w-xl text-sm text-slate-500">Kelola proyek, pengalaman, dan sertifikatmu. Hanya yang berstatus terbit tampil di katalog publik.</p>
  </div>
  <a href="{{ route('mentor.portofolio.create') }}" class="rounded-full bg-brand px-6 py-3 text-sm font-bold text-white shadow-lg shadow-brand/30 hover:bg-brand-600">+ Tambah Entri</a>
</div>

<div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
  @foreach ([['Total portofolio', $jumlah['semua'], 'text-slate-900'], ['Aktif terbit', $jumlah['terbit'], 'text-emerald-700'], ['Draf disimpan', $jumlah['draf'], 'text-slate-900']] as [$l, $n, $w])
    <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-100">
      <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">{{ $l }}</p>
      <p class="mt-3 text-4xl font-extrabold {{ $w }}">{{ $n }}</p>
    </div>
  @endforeach
  <div class="flex items-start gap-3 rounded-2xl bg-brand-50 p-5">
    <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-brand text-white">ⓘ</span>
    <div><p class="text-xs font-bold text-slate-700">Status Kurasi</p>
      <p class="mt-1 text-sm {{ $tampil ? 'text-emerald-700' : 'text-slate-600' }}">{{ $kurasi }}</p></div>
  </div>
</div>

<div class="mt-6 flex flex-wrap items-center justify-between gap-3">
  <div class="inline-flex rounded-full bg-brand-50 p-1.5 text-sm font-semibold">
    @foreach (['semua' => 'Semua', 'terbit' => 'Terbit', 'draf' => 'Draf'] as $k => $v)
      <a href="{{ $ke(['tab' => $k === 'semua' ? null : $k, 'page' => null]) }}" class="rounded-full px-5 py-2 {{ $tab === $k ? 'bg-brand text-white' : 'text-slate-600' }}">{{ $v }} ({{ $jumlah[$k] }})</a>
    @endforeach
  </div>
  <form method="GET" class="w-full sm:w-72">
    @if ($tab !== 'semua')<input type="hidden" name="tab" value="{{ $tab }}">@endif
    <input name="q" value="{{ $cari }}" placeholder="Cari karya atau skill..." class="w-full rounded-full border-0 bg-white px-5 py-2.5 text-sm shadow-sm ring-1 ring-slate-100 focus:ring-brand">
  </form>
</div>

<section class="mt-5 overflow-hidden rounded-3xl bg-white shadow-sm ring-1 ring-slate-100">
  @forelse ($entri as $e)
    <article class="flex flex-wrap items-center gap-5 border-b border-slate-100 p-6 last:border-0">
      <div class="grid h-24 w-28 shrink-0 place-items-center overflow-hidden rounded-2xl bg-gradient-to-br from-brand-100 to-brand-50 text-xs font-bold text-brand">
        @if ($e->gambar)<img src="{{ asset('storage/'.$e->gambar) }}" alt="{{ $e->judul }}" class="h-full w-full object-cover">@else {{ $jenisLabel[$e->jenis] }} @endif
      </div>
      <div class="min-w-0 flex-1">
        <div class="flex flex-wrap items-center gap-2">
          <h2 class="text-lg font-bold text-slate-900">{{ $e->judul }}</h2>
          <span class="rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase {{ $e->status === 'terbit' ? 'bg-brand-100 text-brand' : 'bg-slate-100 text-slate-500' }}">● {{ $e->status }}</span>
        </div>
        <div class="mt-1 flex flex-wrap items-center gap-2 text-sm text-slate-500">
          <b class="text-slate-700">{{ $jenisLabel[$e->jenis] }}</b><span>•</span><span>{{ $periode($e) }}</span>
          @foreach ($e->keterampilan->take(3) as $k)<span class="rounded-md bg-brand-50 px-2 py-0.5 text-xs font-semibold text-brand">{{ $k->nama }}</span>@endforeach
        </div>
        <p class="mt-2 text-sm text-slate-500">{{ \Illuminate\Support\Str::limit($e->deskripsi, 110) }}</p>
      </div>
      <div class="flex items-center gap-2 text-sm font-semibold">
        <a href="{{ route('mentor.portofolio.show', $e) }}" class="rounded-full bg-brand-50 px-4 py-2 text-slate-700 hover:bg-brand-100">👁 Lihat</a>
        <a href="{{ route('mentor.portofolio.edit', $e) }}" class="rounded-full bg-brand-50 px-4 py-2 text-slate-700 hover:bg-brand-100">✎ Ubah</a>
        <form method="POST" action="{{ route('mentor.portofolio.destroy', $e) }}" onsubmit="return confirm('Hapus entri “{{ addslashes($e->judul) }}”? Tindakan ini tidak bisa dibatalkan.')">@csrf @method('DELETE')
          <button class="rounded-full bg-red-50 px-4 py-2 text-red-600 hover:bg-red-100">🗑 Hapus</button></form>
      </div>
    </article>
  @empty
    <div class="p-12 text-center text-slate-500">
      <p class="font-semibold text-slate-700">{{ $cari !== '' || $tab !== 'semua' ? 'Tidak ada entri yang cocok.' : 'Belum ada entri portofolio.' }}</p>
      <a href="{{ route('mentor.portofolio.create') }}" class="mt-3 inline-block text-sm font-bold text-brand">+ Tambah entri pertamamu</a>
    </div>
  @endforelse
</section>

<div class="mt-6 flex flex-wrap items-center justify-between gap-4 text-sm">
  <div class="flex items-center gap-3"><span class="font-bold text-slate-700">Paginasi:</span>{{ $entri->onEachSide(1)->links() }}</div>
  @unless ($tampil)<p class="text-slate-500"><span class="text-red-500">●</span> {{ $kurasi }}.</p>@endunless
</div>
@endsection
