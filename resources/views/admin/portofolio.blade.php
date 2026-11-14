@extends('layouts.dashboard')
@section('judul', 'Portofolio Mentor')
@section('isi')
@php
  $jenis = \App\Http\Controllers\PortofolioController::JENIS;
  $tabUrl = fn ($t) => route('admin.portofolio', array_filter(['tab' => $t === 'menunggu' ? null : $t, 'q' => $cari]));
  $inisial = fn ($n) => collect(explode(' ', $n))->take(2)->map(fn ($k) => mb_strtoupper(mb_substr($k, 0, 1)))->implode('');
@endphp

<span class="inline-block rounded-md bg-brand-50 px-3 py-1 text-[11px] font-bold uppercase tracking-widest text-brand">Administrator</span>
<h1 class="mt-3 text-4xl font-extrabold tracking-tight text-[#1a1446]">Portofolio Mentor</h1>
<p class="mt-1 text-sm text-slate-500">Periksa portofolio yang diajukan mentor sebelum ditampilkan di katalog publik.</p>

<div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
  @foreach ([['Menunggu persetujuan', $jumlah['menunggu'], 'text-amber-600'], ['Sudah diterbitkan', $jumlah['terbit'], 'text-emerald-700'], ['Ditolak', $jumlah['ditolak'], 'text-red-600'], ['Total portofolio', $jumlah['semua'], 'text-slate-900']] as [$label, $angka, $warna])
    <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-100"><p class="text-xs font-semibold text-slate-500">{{ $label }}</p><p class="mt-3 text-3xl font-extrabold {{ $warna }}">{{ $angka }}</p></div>
  @endforeach
</div>

<div class="mt-6 flex flex-wrap items-center justify-between gap-3">
  <div class="flex flex-wrap gap-2">
    @foreach (['menunggu' => 'Menunggu', 'terbit' => 'Diterbitkan', 'ditolak' => 'Ditolak', 'semua' => 'Semua'] as $key => $label)
      <a href="{{ $tabUrl($key) }}" class="rounded-full px-5 py-2 text-sm font-bold {{ $tab === $key ? 'bg-[#14112e] text-white' : 'bg-white text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50' }}">{{ $label }} ({{ $jumlah[$key] }})</a>
    @endforeach
  </div>
  <form method="GET" action="{{ route('admin.portofolio') }}" class="w-full sm:w-72">
    @if ($tab !== 'menunggu')<input type="hidden" name="tab" value="{{ $tab }}">@endif
    <input name="q" value="{{ $cari }}" placeholder="Cari judul atau nama mentor…" class="w-full rounded-xl border-slate-200 bg-white px-4 py-2.5 text-sm focus:border-brand focus:ring-brand">
  </form>
</div>

<div class="mt-5 space-y-4">
  @forelse ($entri as $e)
    <article class="overflow-hidden rounded-3xl bg-white shadow-sm ring-1 ring-slate-100" x-data="{ buka: false, catatan: '' }">
      <div class="flex flex-wrap items-start gap-5 p-6">
        <div class="grid h-24 w-28 shrink-0 place-items-center overflow-hidden rounded-2xl bg-brand-50 text-xs font-bold text-brand">
          @if ($e->gambar)<img src="{{ asset('storage/'.$e->gambar) }}" alt="{{ $e->judul }}" class="h-full w-full object-cover">@else{{ $jenis[$e->jenis] ?? 'Portofolio' }}@endif
        </div>
        <div class="min-w-0 flex-1">
          <div class="flex flex-wrap items-center gap-2">
            <h2 class="text-lg font-extrabold text-[#1a1446]">{{ $e->judul }}</h2>
            <span class="rounded-full px-3 py-1 text-[10px] font-bold uppercase {{ ['menunggu' => 'bg-amber-50 text-amber-700', 'terbit' => 'bg-emerald-50 text-emerald-700', 'ditolak' => 'bg-red-50 text-red-600', 'draf' => 'bg-slate-100 text-slate-500'][$e->status] ?? 'bg-slate-100 text-slate-500' }}">{{ ucfirst($e->status) }}</span>
          </div>
          <div class="mt-1 flex flex-wrap items-center gap-2 text-xs text-slate-500">
            <b class="text-slate-700">{{ $e->mentor?->name ?? 'Akun mentor tidak tersedia' }}</b><span>•</span><span>{{ $e->mentor?->email }}</span><span>•</span><span>{{ $jenis[$e->jenis] ?? $e->jenis }}</span>
          </div>
          <p class="mt-3 whitespace-pre-line text-sm leading-6 text-slate-600">{{ \Illuminate\Support\Str::limit($e->deskripsi ?: 'Tidak ada deskripsi.', 260) }}</p>
          @if ($e->keterampilan->isNotEmpty())<div class="mt-3 flex flex-wrap gap-2">@foreach ($e->keterampilan as $skill)<span class="rounded-full bg-brand-50 px-3 py-1 text-xs font-semibold text-brand">{{ $skill->nama }}</span>@endforeach</div>@endif
          @if ($e->tautan)<a href="{{ $e->tautan }}" target="_blank" rel="noopener" class="mt-3 inline-block text-xs font-bold text-brand hover:underline">Buka tautan portofolio ↗</a>@endif
          @if ($e->status === 'ditolak' && $e->catatan_admin)<p class="mt-3 rounded-xl bg-red-50 px-4 py-3 text-xs text-red-700"><b>Alasan penolakan:</b> {{ $e->catatan_admin }}</p>@endif
        </div>
        <div class="flex shrink-0 flex-wrap gap-2">
          <button type="button" @click="buka = !buka" class="rounded-xl border border-slate-200 px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-50" x-text="buka ? 'Tutup' : 'Tinjau' "></button>
        </div>
      </div>
      @if ($e->status === 'menunggu')
        <div x-show="buka" x-cloak class="border-t border-slate-100 bg-slate-50/70 p-6">
          <p class="mb-3 text-sm font-bold text-[#1a1446]">Keputusan admin</p>
          <form method="POST" action="{{ route('admin.portofolio.tinjau', $e) }}" class="space-y-3" x-ref="form">
            @csrf
            <textarea name="catatan" x-model="catatan" rows="2" maxlength="1000" placeholder="Catatan untuk mentor (wajib diisi jika menolak)…" class="w-full rounded-xl border-slate-200 bg-white px-4 py-3 text-sm focus:border-brand focus:ring-brand"></textarea>
            <div class="flex flex-wrap justify-end gap-2">
              <button name="aksi" value="tolak" type="submit" @click="if (!catatan.trim()) { $event.preventDefault(); alert('Tuliskan alasan penolakan terlebih dahulu.') }" class="rounded-xl border border-red-200 bg-white px-5 py-2.5 text-xs font-bold text-red-600 hover:bg-red-50">Tolak portofolio</button>
              <button name="aksi" value="setujui" type="submit" class="rounded-xl bg-brand px-5 py-2.5 text-xs font-bold text-white hover:bg-brand-600">Setujui & terbitkan</button>
            </div>
          </form>
        </div>
      @endif
    </article>
  @empty
    <div class="rounded-3xl bg-white p-12 text-center shadow-sm ring-1 ring-slate-100"><p class="font-bold text-slate-700">Tidak ada portofolio pada kategori ini.</p><p class="mt-1 text-sm text-slate-500">Pengajuan baru dari mentor akan muncul di sini.</p></div>
  @endforelse
</div>
<div class="mt-6">{{ $entri->onEachSide(1)->links() }}</div>
@endsection
