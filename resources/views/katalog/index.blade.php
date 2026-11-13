@extends('layouts.publik')
@section('judul', 'Katalog Portofolio')
@section('isi')
<section class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-6 px-6 pt-14">
  <div class="max-w-md"><h1 class="text-5xl font-extrabold leading-[1.1] text-slate-900">Portofolio Nyata <span class="text-brand">Mentor Terpercaya</span></h1>
    <p class="mt-4 text-sm text-slate-600">Temukan portofolio berbasis bukti nyata dari para mentor profesional yang sudah terverifikasi.</p></div>
  <img src="{{ asset('img/maskot.png') }}" alt="Maskot" class="h-44 rounded-3xl bg-brand-100 p-3">
  <div class="flex gap-6 rounded-2xl bg-white px-6 py-4 shadow-sm ring-1 ring-brand-100">
    @foreach ([[$stat['portofolio'], 'Karya Terbit'], [$stat['mentor'], 'Mentor Terverifikasi'], [$stat['sesi'], 'Sesi Selesai']] as [$n, $l])
      <div><p class="text-2xl font-extrabold text-brand">{{ number_format($n) }}</p><p class="text-[11px] text-slate-500">{{ $l }}</p></div>
    @endforeach
  </div>
</section>

<section class="mx-auto mt-8 max-w-6xl px-6">
  <form method="GET" class="rounded-3xl bg-white p-6 shadow-sm ring-1 ring-brand-100">
    <div class="grid gap-3 md:grid-cols-[1fr_220px_220px]">
      <input name="q" value="{{ request('q') }}" placeholder="Cari mentor, judul portofolio, atau keterampilan..." class="rounded-xl border-0 bg-brand-50 px-4 py-3 text-sm focus:ring-brand">
      <select name="bidang" onchange="this.form.submit()" class="rounded-xl border-0 bg-brand-50 px-4 py-3 text-sm font-semibold focus:ring-brand">
        <option value="">Semua Bidang Karier</option>
        @foreach ($bidang as $b)<option value="{{ $b->id }}" @selected(request('bidang') == $b->id)>{{ $b->nama }}</option>@endforeach
      </select>
      <select disabled class="rounded-xl border-0 bg-brand-50 px-4 py-3 text-sm font-semibold"><option>Urutkan: Terbaru Terbit</option></select>
    </div>
    <div class="mt-4 flex flex-wrap items-center gap-2 text-xs font-semibold">
      <span class="text-slate-500">Keterampilan:</span>
      <a href="{{ route('katalog', request()->except('ket', 'page')) }}" class="rounded-full px-4 py-1.5 {{ request('ket') ? 'bg-brand-100 text-slate-600' : 'bg-brand text-white' }}">Semua</a>
      @foreach ($chip as $c)
        <a href="{{ route('katalog', array_merge(request()->except('page'), ['ket' => $c->id])) }}" class="rounded-full px-4 py-1.5 {{ request('ket') == $c->id ? 'bg-brand text-white' : 'bg-brand-100 text-slate-600' }}">{{ $c->nama }}</a>
      @endforeach
      <a href="{{ route('katalog') }}" class="ml-auto text-slate-500">↻ Reset Filter</a>
    </div>
  </form>

  <div class="mt-8 flex items-center gap-3"><h2 class="text-xl font-bold">Menampilkan Portofolio</h2>
    <span class="rounded-full bg-brand-100 px-3 py-1 text-xs font-bold text-brand">{{ $mentor->total() }} Talenta Pilihan</span></div>

  <div class="mt-5 grid gap-5 md:grid-cols-2 lg:grid-cols-3">
    @forelse ($mentor as $m) @include('partials.kartu-mentor', ['m' => $m])
    @empty <p class="col-span-full rounded-3xl bg-white p-10 text-center text-slate-500">Belum ada portofolio yang cocok dengan filter ini.</p>
    @endforelse
  </div>

  <div class="mt-8 flex flex-wrap items-center justify-between gap-4 text-sm text-slate-600">
    <span>Menampilkan <b>{{ $mentor->firstItem() ?? 0 }} - {{ $mentor->lastItem() ?? 0 }}</b> dari <b>{{ $mentor->total() }}</b> talenta berprestasi</span>
    {{ $mentor->links() }}
  </div>
</section>
@endsection
