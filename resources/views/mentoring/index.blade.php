@extends('layouts.dashboard')
@section('judul', 'Mentoring')
@section('isi')
@php($warna = ['diajukan' => 'bg-violet-100 text-violet-700', 'dikonfirmasi' => 'bg-emerald-100 text-emerald-700', 'ditolak' => 'bg-red-100 text-red-700', 'dibatalkan' => 'bg-slate-100 text-slate-600', 'selesai' => 'bg-blue-100 text-blue-700'])
<span class="rounded-full bg-brand-100 px-3 py-1 text-[11px] font-bold uppercase tracking-wide text-brand">Mentor terverifikasi</span>
<h1 class="mt-3 text-5xl font-extrabold tracking-tight text-slate-900">Temukan mentor yang tepat</h1>
<p class="mt-3 max-w-xl text-slate-500">Diskusikan portofolio dan langkah kariermu dalam sesi 1-on-1 terfokus bersama praktisi industri berpengalaman.</p>

<form method="GET" class="mt-6 rounded-2xl bg-white p-3 shadow-sm ring-1 ring-slate-100">
  <div class="flex gap-3">
    <input name="q" value="{{ request('q') }}" placeholder="Cari mentor, jabatan, atau keahlian..." class="flex-1 rounded-xl border-0 bg-slate-50 px-4 py-3 text-sm focus:ring-brand">
    @if (request('bidang'))<input type="hidden" name="bidang" value="{{ request('bidang') }}">@endif
    <button class="rounded-xl bg-brand-100 px-5 text-sm font-bold text-brand">Cari</button>
  </div>
</form>

<div class="mt-5 flex flex-wrap items-center gap-2 text-xs font-semibold">
  <span class="text-slate-400">BIDANG:</span>
  <a href="{{ route('mentoring.index', request()->except('bidang')) }}" class="rounded-full px-4 py-2 {{ request('bidang') ? 'bg-brand-50 text-slate-600' : 'bg-brand text-white' }}">Semua Bidang</a>
  @foreach ($bidang as $b)
    <a href="{{ route('mentoring.index', array_merge(request()->except('bidang'), ['bidang' => $b->id])) }}" class="rounded-full px-4 py-2 {{ request('bidang') == $b->id ? 'bg-brand text-white' : 'bg-brand-50 text-slate-600' }}">{{ $b->nama }}</a>
  @endforeach
</div>

<div class="mt-6 grid gap-5 md:grid-cols-2 xl:grid-cols-3">
  @forelse ($mentor as $m)
    <article class="flex flex-col rounded-3xl border-t-4 border-brand bg-white p-5 shadow-sm ring-1 ring-slate-100">
      <div class="flex items-center gap-3">
        <span class="grid h-14 w-14 place-items-center rounded-2xl bg-brand-100 text-lg font-bold text-brand">{{ mb_substr($m->name, 0, 1) }}</span>
        <div class="min-w-0"><p class="truncate font-bold text-slate-900">{{ $m->name }} <span class="text-brand">✔</span></p>
          <p class="truncate text-sm text-slate-500">{{ $m->profilMentor->jabatan }}</p>
          <p class="truncate text-xs text-slate-400">{{ $m->profilMentor->bidang?->nama }}</p></div>
      </div>
      <div class="mt-4 flex flex-wrap gap-1.5">@foreach ($m->keterampilan->take(3) as $k)<span class="rounded-full bg-brand-50 px-3 py-1 text-xs font-semibold text-brand">{{ $k->nama }}</span>@endforeach</div>
      <p class="mt-3 line-clamp-2 text-sm text-slate-600">{{ $m->profilMentor->topik_mentoring }}</p>
      <div class="mt-4 flex items-center justify-between text-sm"><span class="text-slate-500">{{ $m->sesi_selesai_count }} sesi selesai</span>
        <span class="rounded-full px-3 py-1 text-xs font-bold {{ $m->slot_tersedia_count ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">{{ $m->slot_tersedia_count ? 'Sisa '.$m->slot_tersedia_count.' Slot' : 'Slot penuh' }}</span></div>
      <a href="{{ route('katalog.detail', $m) }}" class="mt-5 block rounded-xl bg-brand py-3 text-center text-sm font-bold text-white shadow-lg shadow-brand/30">📅 Lihat Jadwal</a>
    </article>
  @empty <p class="col-span-full rounded-3xl bg-white p-10 text-center text-slate-500">Tidak ada mentor yang cocok dengan pencarianmu.</p>
  @endforelse
</div>

<section class="mt-10 rounded-3xl bg-white p-8 shadow-sm ring-1 ring-slate-100">
  <div class="flex flex-wrap items-center justify-between gap-3">
    <div><h2 class="text-2xl font-extrabold">Sesi mentoring saya <span class="ml-1 rounded-full bg-brand-100 px-2.5 py-0.5 text-sm text-brand">{{ $sesi->count() }}</span></h2>
      <p class="text-sm text-slate-500">Maksimal 2 sesi aktif dalam satu waktu untuk menjaga komitmen belajar.</p></div>
    <span class="rounded-full bg-brand-50 px-4 py-2 text-xs font-bold text-brand">Total {{ $aktif }} Sesi Aktif{{ $aktif >= 2 ? ' (Kuota Penuh)' : '' }}</span>
  </div>
  <div class="mt-6 space-y-4">
    @forelse ($sesi as $s)
      <div class="flex flex-wrap items-center justify-between gap-4 rounded-2xl bg-brand-50/60 p-5">
        <div>
          <p class="font-bold text-slate-900">{{ \Illuminate\Support\Str::limit($s->tujuan, 70) }} <span class="text-slate-400">· {{ $s->mentor->name }}</span>
            <span class="ml-1 rounded-full px-2.5 py-0.5 text-xs font-bold {{ $warna[$s->status] }}">{{ ucfirst($s->status) }}</span></p>
          <p class="mt-1 text-sm text-slate-500">{{ $s->tanggal->translatedFormat('j F Y') }} · {{ substr($s->jam_mulai, 0, 5) }}–{{ substr($s->jam_selesai, 0, 5) }} WIB</p>
          @if ($s->tautanTerlihatOleh(auth()->user()) && $s->tautan_pertemuan)
            <a href="{{ $s->tautan_pertemuan }}" target="_blank" rel="noopener" class="mt-1 inline-block text-sm font-bold text-brand">🎥 Buka tautan pertemuan</a>
          @elseif ($s->status === 'diajukan')<p class="mt-1 text-xs italic text-slate-400">Tautan muncul setelah mentor mengonfirmasi.</p>@endif
        </div>
        @if (in_array($s->status, ['diajukan', 'dikonfirmasi']))
          <form method="POST" action="{{ route('sesi.aksi', [$s, 'batalkan']) }}" onsubmit="return confirm('Batalkan sesi ini?')">@csrf
            <button class="rounded-xl bg-white px-4 py-2 text-sm font-semibold text-slate-600 ring-1 ring-slate-200 hover:text-red-600">Batalkan Sesi</button></form>
        @endif
      </div>
    @empty <p class="text-sm text-slate-500">Kamu belum punya sesi. Pilih mentor di atas lalu klik “Lihat Jadwal”.</p>
    @endforelse
  </div>
  @if ($aktif >= 2)<p class="mt-5 rounded-xl bg-brand-50 px-4 py-3 text-sm text-slate-600">Ingin memesan sesi ketiga? Selesaikan salah satu sesi atau batalkan permohonan yang belum disetujui.</p>@endif
</section>
@endsection
