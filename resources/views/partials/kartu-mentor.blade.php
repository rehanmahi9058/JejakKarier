@php($p = $m->portofolio->first())
<article class="flex flex-col rounded-3xl bg-white p-5 shadow-sm ring-1 ring-brand-100">
  <div class="flex items-center gap-3">
    <span class="grid h-11 w-11 place-items-center rounded-full bg-brand-100 font-bold text-brand">{{ mb_substr($m->name, 0, 1) }}</span>
    <div class="min-w-0 flex-1"><p class="truncate text-sm font-bold text-slate-900">{{ $m->name }}</p>
      <p class="truncate text-xs text-slate-500">{{ $m->profilMentor->jabatan }}</p></div>
    <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-semibold text-emerald-700">✓ Terverifikasi</span>
  </div>
  <p class="mt-4 text-[11px] font-bold uppercase tracking-wide text-brand">{{ $m->profilMentor->bidang?->nama }}</p>
  <h3 class="mt-1 line-clamp-1 text-lg font-bold text-slate-900">{{ $p->judul }}</h3>
  <div class="mt-3 grid h-32 place-items-center rounded-2xl bg-gradient-to-br from-brand-100 to-brand-50 text-xs font-semibold text-brand">
    @if ($p->gambar)<img src="{{ asset('storage/'.$p->gambar) }}" alt="{{ $p->judul }}" class="h-32 w-full rounded-2xl object-cover">@else {{ ucfirst(str_replace('_', ' ', $p->jenis)) }} @endif
  </div>
  <p class="mt-4 text-[11px] font-bold uppercase tracking-wide text-slate-500">{{ $m->keterampilan->count() }} keterampilan teratas</p>
  <div class="mt-2 flex flex-wrap gap-1.5">
    @foreach ($m->keterampilan->take(4) as $k)<span class="rounded-full bg-brand-100 px-3 py-1 text-xs font-semibold text-brand-600">{{ $k->nama }}</span>@endforeach
  </div>
  <div class="mt-auto pt-4">
    <p class="rounded-xl bg-brand-50 px-3 py-2 text-xs text-slate-600">{{ $m->sesi_selesai_count }} sesi mentor selesai · {{ $m->terbit_count }} portofolio terbit</p>
    <div class="mt-3 grid grid-cols-[1fr_1.4fr] gap-2 text-sm font-bold">
      <a href="{{ route('katalog.detail', $m) }}#ringkasan" class="rounded-xl bg-brand-100 py-2.5 text-center text-brand">Ringkasan</a>
      <a href="{{ route('katalog.detail', $m) }}" class="rounded-xl bg-brand py-2.5 text-center text-white">Portofolio ↗</a>
    </div>
  </div>
</article>
