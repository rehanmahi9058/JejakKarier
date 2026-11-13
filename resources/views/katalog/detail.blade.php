@extends('layouts.publik')
@section('judul', $mentor->name)
@section('isi')
<div class="mx-auto grid max-w-6xl gap-6 px-6 pt-10 lg:grid-cols-[1fr_380px]">
  <div class="space-y-6">
    <section id="ringkasan" class="rounded-3xl bg-white p-6 shadow-sm ring-1 ring-brand-100">
      <div class="flex items-center gap-4">
        <span class="grid h-16 w-16 place-items-center rounded-2xl bg-brand text-2xl font-bold text-white">{{ mb_substr($mentor->name, 0, 1) }}</span>
        <div><h1 class="text-2xl font-extrabold">{{ $mentor->name }} <span class="text-sm font-semibold text-emerald-600">✓ Terverifikasi</span></h1>
          <p class="text-sm text-slate-500">{{ $mentor->profilMentor->jabatan }} · {{ $mentor->profilMentor->tahun_pengalaman }} tahun pengalaman · {{ $mentor->profilMentor->bidang?->nama }}</p></div>
      </div>
      <div class="mt-4 flex flex-wrap gap-2">
        @foreach ($mentor->keterampilan as $k)<span class="rounded-full bg-brand-100 px-3 py-1 text-xs font-semibold text-brand-600">{{ $k->nama }} · {{ $k->pivot->level }}</span>@endforeach
      </div>
    </section>
    @foreach ($mentor->portofolio as $p)
      <article class="rounded-3xl bg-white p-6 shadow-sm ring-1 ring-brand-100">
        <p class="text-xs font-bold uppercase text-brand">{{ str_replace('_', ' ', $p->jenis) }} · {{ $p->tanggal_mulai?->translatedFormat('M Y') }} – {{ $p->tanggal_selesai?->translatedFormat('M Y') ?? 'sekarang' }}</p>
        <h2 class="mt-1 text-xl font-bold">{{ $p->judul }}</h2>
        @if ($p->gambar)<img src="{{ asset('storage/'.$p->gambar) }}" alt="{{ $p->judul }}" class="mt-3 w-full rounded-2xl">@endif
        <p class="mt-3 text-sm text-slate-600">{{ $p->deskripsi }}</p>
        @if ($p->tautan)<a href="{{ $p->tautan }}" target="_blank" rel="noopener" class="mt-3 inline-block text-sm font-bold text-brand">Buka tautan ↗</a>@endif
      </article>
    @endforeach
  </div>

  <aside class="h-fit rounded-3xl bg-white p-6 shadow-sm ring-1 ring-brand-100 lg:sticky lg:top-24">
    <h2 class="text-lg font-bold">Pesan sesi mentoring</h2>
    @if ($errors->any())<p class="mt-3 rounded-xl bg-red-50 px-3 py-2 text-sm text-red-700">{{ $errors->first() }}</p>@endif
    @auth
      @if (auth()->user()->peran === 'pengguna')
        <form method="POST" x-data="{ slot: null }" :action="slot ? '{{ url('/slot') }}/' + slot + '/pesan' : '#'" class="mt-4 space-y-3">
          @csrf
          <div class="max-h-60 space-y-2 overflow-auto">
            @forelse ($slot as $s)
              <label class="flex cursor-pointer items-center justify-between rounded-xl border border-slate-200 px-4 py-3 text-sm has-[:checked]:border-brand has-[:checked]:bg-brand-50">
                <span><b>{{ $s->tanggal->translatedFormat('l, d M Y') }}</b><br><span class="text-slate-500">{{ substr($s->jam_mulai, 0, 5) }}–{{ substr($s->jam_selesai, 0, 5) }}</span></span>
                <input type="radio" name="pilih" value="{{ $s->id }}" x-model="slot" class="text-brand"></label>
            @empty <p class="text-sm text-slate-500">Belum ada slot tersedia.</p> @endforelse
          </div>
          <textarea name="tujuan" maxlength="500" rows="4" required placeholder="Tujuan sesi (maks. 500 karakter)" class="w-full rounded-xl border-slate-200 text-sm focus:border-brand focus:ring-brand">{{ old('tujuan') }}</textarea>
          @error('tujuan')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
          <button :disabled="!slot" class="w-full rounded-xl bg-brand py-3 text-sm font-bold text-white disabled:opacity-40">Ajukan sesi</button>
        </form>
      @else <p class="mt-3 text-sm text-slate-500">Hanya akun berperan pengguna yang dapat memesan sesi.</p> @endif
    @else <a href="{{ route('login') }}" class="mt-4 block rounded-xl bg-brand py-3 text-center text-sm font-bold text-white">Masuk untuk memesan</a> @endauth
  </aside>
</div>
@endsection
