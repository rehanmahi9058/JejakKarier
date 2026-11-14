@extends('layouts.dashboard')
@section('judul', 'Mentoring')
@section('isi')
@php
  $chip = ['diajukan' => 'bg-amber-100 text-amber-700', 'dikonfirmasi' => 'bg-violet-100 text-violet-700', 'selesai' => 'bg-emerald-100 text-emerald-700', 'ditolak' => 'bg-red-100 text-red-700', 'dibatalkan' => 'bg-slate-100 text-slate-500'];
  $label = ['diajukan' => 'Menunggu', 'dikonfirmasi' => 'Dikonfirmasi', 'selesai' => 'Selesai', 'ditolak' => 'Ditolak', 'dibatalkan' => 'Dibatalkan'];
  $namaHari = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
  $bulanNama = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
  $tglIndo = fn ($d) => $d->day . ' ' . $bulanNama[$d->month] . ' ' . $d->year;
  $ke = fn (array $p = []) => route('sesi.index', array_filter(array_merge(['bulan' => $bulan->format('Y-m'), 'tab' => $tab === 'semua' ? null : $tab, 'q' => $cari], $p), fn ($v) => filled($v)));
  $aksiUntuk = [
    'diajukan' => [['konfirmasi', 'Konfirmasi', 'bg-brand text-white hover:bg-brand-600'], ['tolak', 'Tolak', 'bg-slate-100 text-slate-600 hover:bg-slate-200']],
    'dikonfirmasi' => [['selesai', 'Tandai Selesai', 'bg-brand text-white hover:bg-brand-600'], ['batalkan', 'Batalkan', 'bg-slate-100 text-slate-600 hover:bg-slate-200']],
  ];
  $statusProfil = ['terverifikasi' => ['Aktif Menerima Mentee', 'bg-emerald-100 text-emerald-700'], 'menunggu' => ['Menunggu Verifikasi', 'bg-amber-100 text-amber-700'], 'ditolak' => ['Verifikasi Ditolak', 'bg-red-100 text-red-700']][$profil?->status_verifikasi ?? 'menunggu'];
  $bulanIni = $bulan->format('Y-m');
@endphp

<div>
  <div class="flex flex-wrap items-start justify-between gap-4">
    <div>
      <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 lg:text-4xl">Kelola Sesi Mentoring</h1>
      <p class="mt-2 max-w-xl text-sm text-slate-500">Konfirmasi atau tolak permintaan sesi 1-on-1 dan pantau jadwalmu untuk kurun waktu 1 bulan penuh ({{ $bulanLabel }}). Tautan pertemuan baru terlihat oleh pemesan setelah sesi dikonfirmasi.</p>
    </div>
    <a href="{{ route('mentor.slot.index') }}" class="rounded-full bg-brand px-6 py-3 text-sm font-bold text-white shadow-lg shadow-brand/30 hover:bg-brand-600">Kelola Slot Jadwal</a>
  </div>

  @if ($errors->any())<div class="mt-5 rounded-xl bg-red-50 px-4 py-3 text-sm text-red-700">{{ $errors->first() }}</div>@endif

  @if ($jumlahLuar > 0)
    <div class="mt-5 rounded-xl bg-amber-50 px-4 py-3 text-sm text-amber-800">Ada {{ $jumlahLuar }} permintaan menunggu konfirmasi di bulan lain. <a href="{{ route('sesi.index', ['bulan' => $bulanLuar, 'tab' => 'diajukan']) }}" class="font-bold underline">Lihat sekarang →</a></div>
  @endif

  {{-- Kartu profil mentor --}}
  <section class="mt-6 rounded-3xl bg-white p-6 shadow-sm ring-1 ring-slate-100">
    <div class="flex flex-wrap items-center gap-6">
      <span class="grid h-20 w-20 shrink-0 place-items-center overflow-hidden rounded-2xl bg-gradient-to-br from-brand to-brand-600 text-2xl font-bold text-white shadow-lg shadow-brand/30">
        @if ($u->fotoUrl())<img src="{{ $u->fotoUrl() }}" alt="" class="h-full w-full object-cover">@else{{ collect(explode(' ', $u->name))->take(2)->map(fn ($k) => mb_substr($k, 0, 1))->implode('') }}@endif
      </span>
      <div class="min-w-0 flex-1">
        <div class="flex flex-wrap items-center gap-2">
          <h2 class="text-2xl font-extrabold text-slate-900">{{ $u->name }}</h2>
          <span class="rounded-full bg-brand-100 px-2.5 py-1 text-[11px] font-bold text-brand">#PM-{{ str_pad($u->id, 4, '0', STR_PAD_LEFT) }}</span>
          <span class="rounded-full px-2.5 py-1 text-[11px] font-bold {{ $statusProfil[1] }}">Status: {{ $statusProfil[0] }}</span>
        </div>
        <p class="mt-1 text-sm text-slate-600">{{ $profil?->jabatan ?: 'Jabatan belum diisi' }}</p>
        <div class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-slate-500">
          @if ($profil?->bidang)<span class="font-semibold text-brand">{{ $profil->bidang->nama }}</span>@endif
          <span>{{ $totalSelesai }} sesi selesai</span>
        </div>
      </div>
      <a href="{{ route('profil.edit') }}" class="rounded-xl bg-brand-50 px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-brand-100">
        <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400">Profil mentor</span>{{ $u->name }}{{ $profil?->jabatan ? ' ('.$profil->jabatan.')' : '' }}
      </a>
    </div>
  </section>

  {{-- Statistik --}}
  <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
    <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-100"><p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Total sesi bulan ini</p>
      <p class="mt-3 text-4xl font-extrabold text-slate-900">{{ $jumlah['semua'] }} <span class="text-sm font-medium text-slate-500">sesi terdata</span></p>
      <p class="mt-2 text-xs text-slate-500">{{ $bulanLabel }}</p></div>
    <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-100"><p class="text-[11px] font-bold uppercase tracking-wider text-amber-700">Menunggu konfirmasi</p>
      <p class="mt-3 text-4xl font-extrabold text-amber-700">{{ $jumlah['diajukan'] }} <span class="text-sm font-medium text-slate-500">permintaan baru</span></p>
      <p class="mt-2 text-xs text-slate-500">{{ $jumlah['diajukan'] > 0 ? 'Butuh respons darimu' : 'Tidak ada yang menunggu' }}</p></div>
    <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-100"><p class="text-[11px] font-bold uppercase tracking-wider text-brand">Dikonfirmasi</p>
      <p class="mt-3 text-4xl font-extrabold text-brand">{{ $jumlah['dikonfirmasi'] }} <span class="text-sm font-medium text-slate-500">sesi terjadwal</span></p>
      <p class="mt-2 text-xs text-slate-500">{{ $mentee ? 'Mentee: '.$mentee : 'Belum ada mentee' }}</p></div>
    <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-100"><p class="text-[11px] font-bold uppercase tracking-wider text-emerald-700">Sesi selesai</p>
      <p class="mt-3 text-4xl font-extrabold text-emerald-700">{{ $jumlah['selesai'] }} <span class="text-sm font-medium text-slate-500">bulan ini</span></p>
      <p class="mt-2 text-xs text-slate-500">{{ $jumlah['riwayat'] }} ditolak / dibatalkan</p></div>
  </div>

  {{-- Kalender --}}
  <section class="mt-6 rounded-3xl bg-white p-6 shadow-sm ring-1 ring-slate-100">
    <div class="flex flex-wrap items-center justify-between gap-3">
      <div>
        <h3 class="text-lg font-extrabold text-slate-900">Kalender Sesi Mentoring 1 Bulan Penuh</h3>
        <p class="text-xs text-slate-500">{{ $bulanLabel }} • Klik tanggal untuk melihat atau menyaring sesi</p>
      </div>
      <div class="flex flex-wrap items-center gap-3 text-xs font-semibold">
        <span class="flex items-center gap-1.5"><i class="h-2 w-2 rounded-full bg-amber-400"></i>Menunggu</span>
        <span class="flex items-center gap-1.5"><i class="h-2 w-2 rounded-full bg-brand"></i>Dikonfirmasi</span>
        <span class="flex items-center gap-1.5"><i class="h-2 w-2 rounded-full bg-emerald-400"></i>Selesai</span>
        <span class="ml-2 flex items-center gap-1">
          <a href="{{ $ke(['bulan' => $bulanSebelum]) }}" class="rounded-lg bg-brand-50 px-3 py-1.5 text-slate-600 hover:bg-brand-100" aria-label="Bulan sebelumnya">‹</a>
          <a href="{{ route('sesi.index') }}" class="rounded-lg bg-brand-100 px-3 py-1.5 text-brand">Bulan ini</a>
          <a href="{{ $ke(['bulan' => $bulanSesudah]) }}" class="rounded-lg bg-brand-50 px-3 py-1.5 text-slate-600 hover:bg-brand-100" aria-label="Bulan berikutnya">›</a>
        </span>
      </div>
    </div>
    <div class="mt-5 grid grid-cols-7 gap-2 border-t border-slate-100 pt-4 text-center">
      @foreach (['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'] as $i => $h)<p class="text-[11px] font-bold {{ $i === 0 ? 'text-red-600' : 'text-slate-500' }}">{{ $h }}</p>@endforeach
      @foreach ($sel as $c)
        @if (! $c)<div></div>
        @else
          @php
            $n = fn ($s) => $c['sesi']->where('status', $s)->count();
            $dom = $n('diajukan') ? 'diajukan' : ($n('dikonfirmasi') ? 'dikonfirmasi' : ($n('selesai') ? 'selesai' : (($n('ditolak') + $n('dibatalkan')) ? 'dibatalkan' : null)));
            $kelas = ['diajukan' => 'bg-amber-50 border-amber-200 text-amber-800', 'dikonfirmasi' => 'bg-violet-50 border-violet-200 text-brand', 'selesai' => 'bg-emerald-50 border-emerald-200 text-emerald-800', 'dibatalkan' => 'bg-slate-50 border-slate-200 text-slate-500'][$dom] ?? 'bg-slate-50/60 border-transparent text-slate-400';
            $aktifTgl = $tanggal && $tanggal->toDateString() === $c['tgl'];
          @endphp
          @if ($dom)
            <a href="{{ $ke(['tanggal' => $aktifTgl ? null : $c['tgl'], 'tab' => null]) }}" class="flex min-h-[3.75rem] flex-col items-center justify-center gap-0.5 rounded-xl border px-1 py-1.5 text-sm font-bold {{ $kelas }} {{ $aktifTgl ? 'ring-2 ring-brand' : '' }}">
              {{ $c['hari'] }}
              @foreach (['diajukan', 'dikonfirmasi', 'selesai'] as $st)@if ($n($st))<span class="rounded-full px-1.5 text-[9px] font-semibold {{ $chip[$st] }}">{{ $n($st) }} {{ $label[$st] }}</span>@endif @endforeach
              @if ($n('ditolak') + $n('dibatalkan'))<span class="rounded-full px-1.5 text-[9px] font-semibold {{ $chip['dibatalkan'] }}">{{ $n('ditolak') + $n('dibatalkan') }} Batal</span>@endif
            </a>
          @else
            <div class="flex min-h-[3.75rem] items-center justify-center rounded-xl border text-sm {{ $kelas }}">{{ $c['hari'] }}</div>
          @endif
        @endif
      @endforeach
    </div>
  </section>

  {{-- Filter daftar --}}
  <section class="mt-6 rounded-2xl bg-white p-3 shadow-sm ring-1 ring-slate-100">
    <div class="flex flex-wrap items-center gap-2">
      @foreach (['semua' => 'Semua Sesi', 'diajukan' => 'Menunggu', 'dikonfirmasi' => 'Dikonfirmasi', 'selesai' => 'Selesai', 'riwayat' => 'Ditolak/Batal'] as $k => $t)
        <a href="{{ $ke(['tab' => $k === 'semua' ? null : $k]) }}" class="rounded-full px-4 py-2 text-sm font-semibold {{ ($tab === $k) ? 'bg-brand text-white' : 'text-slate-600 hover:bg-brand-50' }}">{{ $t }} <span class="{{ ($tab === $k) ? '' : 'text-brand' }}">({{ $jumlah[$k] }})</span></a>
      @endforeach
      <form method="GET" class="ml-auto flex flex-wrap items-center gap-2">
        @if ($tab !== 'semua')<input type="hidden" name="tab" value="{{ $tab }}">@endif
        @if ($tanggal)<input type="hidden" name="tanggal" value="{{ $tanggal->toDateString() }}">@endif
        <input name="q" value="{{ $cari }}" placeholder="Cari mentee / tujuan / ID…" class="w-52 rounded-xl border-0 bg-slate-50 px-3 py-2 text-sm focus:ring-brand">
        <select name="bulan" onchange="this.form.submit()" class="rounded-xl border-0 bg-slate-50 px-3 py-2 text-sm font-semibold focus:ring-brand">
          @foreach ($opsiBulan as $v => $l)<option value="{{ $v }}" @selected($v === $bulanIni)>{{ $l }}</option>@endforeach
        </select>
      </form>
    </div>
    @if ($tanggal)<p class="mt-2 px-2 text-xs text-slate-500">Menampilkan sesi tanggal <b>{{ $tglIndo($tanggal) }}</b>. <a href="{{ $ke(['tanggal' => null]) }}" class="font-bold text-brand">Hapus filter tanggal</a></p>@endif
  </section>

  {{-- Tabel sesi --}}
  <section class="mt-4 overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-100">
    <div class="overflow-x-auto">
      <table class="w-full min-w-[56rem] text-left text-sm">
        <thead class="bg-brand-50 text-[11px] font-bold uppercase tracking-wider text-slate-500">
          <tr>
            <th class="px-4 py-3">ID &amp; Tanggal</th><th class="px-3 py-3">Jam Sesi (Durasi)</th><th class="px-3 py-3">Status Sesi</th>
            <th class="px-3 py-3">Tautan Pertemuan (Daring)</th><th class="px-3 py-3">Mentee / Tujuan</th><th class="px-3 py-3 text-right">Aksi</th>
          </tr>
        </thead>
        @forelse ($sesi as $s)
          @php
            $mulai = substr($s->jam_mulai, 0, 5);
            $selesaiJam = substr($s->jam_selesai, 0, 5);
            $menit = \Carbon\Carbon::parse($s->jam_mulai)->diffInMinutes(\Carbon\Carbon::parse($s->jam_selesai));
            $tautan = $s->tautanTerlihatOleh(auth()->user()) ? $s->tautan_pertemuan : null;
            $aksi = $aksiUntuk[$s->status] ?? [];
            $mati = in_array($s->status, ['ditolak', 'dibatalkan'], true);
          @endphp
          <tbody x-data="{ buka: null, salin(t) { navigator.clipboard?.writeText(t); } }" class="divide-y divide-slate-100 border-t border-slate-100">
            <tr class="align-middle {{ $mati ? 'text-slate-400' : '' }}">
              <td class="px-4 py-4"><p class="text-[11px] font-bold text-brand">#SES-{{ str_pad($s->id, 4, '0', STR_PAD_LEFT) }}</p>
                <p class="font-bold text-slate-900">{{ $tglIndo($s->tanggal) }}</p><p class="text-xs text-slate-500">{{ $namaHari[$s->tanggal->dayOfWeek] }}</p></td>
              <td class="px-3 py-4"><p class="font-semibold text-slate-800">{{ $mulai }} – {{ $selesaiJam }} WIB</p><p class="text-xs text-slate-500">{{ $menit }} menit</p></td>
              <td class="px-3 py-4"><span class="rounded-full px-3 py-1 text-xs font-bold {{ $chip[$s->status] }}">{{ $label[$s->status] }}</span></td>
              <td class="px-3 py-4">
                @if ($tautan)
                  <span class="inline-flex max-w-[16rem] items-center gap-2 rounded-lg bg-brand-50 px-3 py-2 font-mono text-xs text-slate-700">
                    <a href="{{ $tautan }}" target="_blank" rel="noopener" class="truncate hover:text-brand">{{ preg_replace('#^https?://#', '', $tautan) }}</a>
                    <button type="button" @click="salin(@js($tautan))" class="shrink-0 text-slate-400 hover:text-brand" aria-label="Salin tautan">⧉</button>
                  </span>
                @else<span class="text-xs italic text-slate-400">{{ $s->status === 'diajukan' ? 'Diisi saat konfirmasi' : 'Belum ada tautan' }}</span>@endif
              </td>
              <td class="px-3 py-4">
                <p class="font-bold {{ $mati ? 'text-slate-500' : 'text-slate-900' }}">{{ $s->pengguna?->name }}</p>
                <p class="text-xs text-slate-500">{{ \Illuminate\Support\Str::limit($s->tujuan, 50) }}</p>
                @if ($s->catatan)<p class="mt-1 text-[11px] text-slate-500"><b>Catatan:</b> {{ \Illuminate\Support\Str::limit($s->catatan, 60) }}</p>@endif
              </td>
              <td class="px-3 py-4">
                <div class="flex justify-end gap-2 text-xs font-bold">
                  @foreach ($aksi as [$kode, $teks, $kelas])
                    <button type="button" @click="buka = buka === '{{ $kode }}' ? null : '{{ $kode }}'" :class="buka === '{{ $kode }}' ? 'ring-2 ring-brand' : ''" class="rounded-lg px-3 py-1.5 {{ $kelas }}">{{ $teks }}</button>
                  @endforeach
                </div>
              </td>
            </tr>
            @if (count($aksi))
              <tr x-show="buka" x-cloak>
                <td colspan="6" class="bg-slate-50 px-4 py-4">
                  @foreach ($aksi as [$kode, $teks, $kelas])
                    <form x-show="buka === '{{ $kode }}'" x-cloak method="POST" action="{{ route('sesi.aksi', [$s, $kode]) }}" class="grid gap-3 sm:grid-cols-[1fr_1fr_auto] sm:items-end"
                          @if ($kode !== 'konfirmasi') onsubmit="return confirm('{{ $teks }} sesi ini?')" @endif>@csrf
                      @if ($kode === 'konfirmasi')
                        <label class="block text-xs font-semibold text-slate-700">Tautan pertemuan
                          <input type="url" name="tautan_pertemuan" value="{{ $s->tautan_pertemuan }}" placeholder="https://meet.google.com/..." class="mt-1 w-full rounded-xl border-slate-200 text-sm focus:border-brand focus:ring-brand"></label>
                      @else
                        <div class="hidden sm:block"></div>
                      @endif
                      <label class="block text-xs font-semibold text-slate-700">Catatan (opsional)
                        <input type="text" name="catatan" maxlength="500" class="mt-1 w-full rounded-xl border-slate-200 text-sm focus:border-brand focus:ring-brand"></label>
                      <button class="rounded-xl bg-brand px-5 py-2.5 text-sm font-bold text-white hover:bg-brand-600">{{ $teks }}</button>
                    </form>
                  @endforeach
                </td>
              </tr>
            @endif
          </tbody>
        @empty
          <tbody><tr><td colspan="6" class="px-6 py-12 text-center text-slate-500">Belum ada sesi untuk filter ini.</td></tr></tbody>
        @endforelse
      </table>
    </div>
    <div class="flex items-center justify-between border-t border-slate-100 px-5 py-3 text-xs text-slate-500">
      <span>Menampilkan {{ $sesi->firstItem() ?? 0 }} - {{ $sesi->lastItem() ?? 0 }} dari {{ $sesi->total() }} sesi</span>
      <span class="flex gap-2">
        @if ($sesi->previousPageUrl())<a href="{{ $sesi->previousPageUrl() }}" class="rounded-lg bg-brand-50 px-3 py-1.5 font-semibold text-slate-600 hover:bg-brand-100">‹ Sebelumnya</a>@endif
        @if ($sesi->nextPageUrl())<a href="{{ $sesi->nextPageUrl() }}" class="rounded-lg bg-brand-50 px-3 py-1.5 font-semibold text-slate-600 hover:bg-brand-100">Berikutnya ›</a>@endif
      </span>
    </div>
  </section>
</div>
@endsection
