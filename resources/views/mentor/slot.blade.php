@extends('layouts.dashboard')
@section('judul', 'Slot Jadwal')
@section('isi')
@php
  $chip = ['tersedia' => 'bg-emerald-100 text-emerald-700', 'dipesan' => 'bg-violet-100 text-violet-700', 'ditutup' => 'bg-slate-100 text-slate-500'];
  $label = ['tersedia' => 'Tersedia', 'dipesan' => 'Dipesan', 'ditutup' => 'Ditutup'];
  $namaHari = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
  $bulanPendek = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
  $bulanNama = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
  $tglIndo = fn ($d) => $d->day . ' ' . $bulanNama[$d->month] . ' ' . $d->year;
  $ke = fn (array $p = []) => route('mentor.slot.index', array_filter(array_merge(['bulan' => $bulan->format('Y-m'), 'status' => $status, 'q' => $cari], $p), fn ($v) => filled($v)));
  $bag = $errors->getBag('tambahSlot');
  $statusProfil = ['terverifikasi' => ['Aktif Menerima Mentee', 'bg-emerald-100 text-emerald-700'], 'menunggu' => ['Menunggu Verifikasi', 'bg-amber-100 text-amber-700'], 'ditolak' => ['Verifikasi Ditolak', 'bg-red-100 text-red-700']][$profil?->status_verifikasi ?? 'menunggu'];
@endphp

<div x-data="{ modal: {{ $bag->any() ? 'true' : 'false' }}, pilih: [], ids: @js($bisaDipilih), semuaDipilih() { return this.ids.length > 0 && this.ids.every(i => this.pilih.includes(i)); }, toggleSemua() { this.pilih = this.semuaDipilih() ? [] : [...this.ids]; }, salin(t) { navigator.clipboard?.writeText(t); } }">

  <div class="flex flex-wrap items-start justify-between gap-4">
    <div>
      <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 lg:text-4xl">Kelola Jadwal &amp; Slot Mentoring</h1>
      <p class="mt-2 max-w-xl text-sm text-slate-500">Atur dan tinjau ketersediaan slot konsultasi 1-on-1 untuk kurun waktu 1 bulan penuh ({{ $bulanLabel }}).</p>
    </div>
    <button type="button" @click="modal = true" class="rounded-full bg-brand px-6 py-3 text-sm font-bold text-white shadow-lg shadow-brand/30 hover:bg-brand-600">+ Tambah Slot Baru</button>
  </div>

  @if ($errors->has('slot'))<div class="mt-5 rounded-xl bg-red-50 px-4 py-3 text-sm text-red-700">{{ $errors->first('slot') }}</div>@endif

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
          <span>{{ $selesai }} sesi selesai</span>
          @if ($durasiMin)<span>{{ $durasiMin === $durasiMaks ? $durasiMin : "{$durasiMin}–{$durasiMaks}" }} menit / sesi</span>@endif
        </div>
      </div>
      <a href="{{ route('profil.edit') }}" class="rounded-xl bg-brand-50 px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-brand-100">
        <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400">Profil mentor</span>{{ $u->name }}{{ $profil?->jabatan ? ' ('.$profil->jabatan.')' : '' }}
      </a>
    </div>
    @unless ($layak)
      <p class="mt-4 rounded-xl bg-amber-50 px-4 py-3 text-xs text-amber-800">Slotmu belum tampil di katalog. Syaratnya: profil terverifikasi, minimal 1 portofolio terbit, dan minimal 3 keterampilan. Cek di <a href="{{ route('profil.edit') }}" class="font-bold underline">Profil Saya</a>.</p>
    @endunless
  </section>

  {{-- Statistik --}}
  <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
    <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-100"><p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Total slot bulan ini</p>
      <p class="mt-3 text-4xl font-extrabold text-slate-900">{{ $statistik['total'] }} <span class="text-sm font-medium text-slate-500">slot terdata</span></p>
      <p class="mt-2 text-xs text-slate-500">Kapasitas bulan ini terisi {{ $statistik['terisi'] }}%</p></div>
    <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-100"><p class="text-[11px] font-bold uppercase tracking-wider text-emerald-700">Slot tersedia</p>
      <p class="mt-3 text-4xl font-extrabold text-emerald-700">{{ $statistik['tersedia'] }} <span class="text-sm font-medium text-slate-500">dapat dipesan mentee</span></p>
      <p class="mt-2 text-xs {{ $layak ? 'text-emerald-700' : 'text-slate-500' }}">{{ $layak ? 'Siap dipromosikan ke katalog' : 'Belum tampil di katalog' }}</p></div>
    <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-100"><p class="text-[11px] font-bold uppercase tracking-wider text-brand">Slot dipesan</p>
      <p class="mt-3 text-4xl font-extrabold text-brand">{{ $statistik['dipesan'] }} <span class="text-sm font-medium text-slate-500">sesi terjadwal</span></p>
      <p class="mt-2 text-xs text-slate-500">{{ $statistik['mentee'] ? 'Mentee: '.$statistik['mentee'] : 'Belum ada mentee' }}</p></div>
    <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-100"><p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Slot ditutup</p>
      <p class="mt-3 text-4xl font-extrabold text-slate-700">{{ $statistik['ditutup'] }} <span class="text-sm font-medium text-slate-500">diarsipkan / nonaktif</span></p>
      <p class="mt-2 text-xs text-slate-500">Bisa dibuka kembali kapan saja</p></div>
  </div>

  {{-- Kalender --}}
  <section class="mt-6 rounded-3xl bg-white p-6 shadow-sm ring-1 ring-slate-100">
    <div class="flex flex-wrap items-center justify-between gap-3">
      <div>
        <h3 class="text-lg font-extrabold text-slate-900">Kalender Slot Mentoring 1 Bulan Penuh</h3>
        <p class="text-xs text-slate-500">{{ $bulanLabel }} • Klik tanggal untuk melihat atau menyaring slot</p>
      </div>
      <div class="flex items-center gap-3 text-xs font-semibold">
        <span class="flex items-center gap-1.5"><i class="h-2 w-2 rounded-full bg-emerald-400"></i>Tersedia</span>
        <span class="flex items-center gap-1.5"><i class="h-2 w-2 rounded-full bg-brand"></i>Dipesan</span>
        <span class="flex items-center gap-1.5"><i class="h-2 w-2 rounded-full bg-slate-400"></i>Ditutup</span>
        <span class="ml-2 flex items-center gap-1">
          <a href="{{ $ke(['bulan' => $bulanSebelum]) }}" class="rounded-lg bg-brand-50 px-3 py-1.5 text-slate-600 hover:bg-brand-100" aria-label="Bulan sebelumnya">‹</a>
          <a href="{{ route('mentor.slot.index') }}" class="rounded-lg bg-brand-100 px-3 py-1.5 text-brand">Bulan ini</a>
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
            $n = fn ($s) => $c['slot']->where('status', $s)->count();
            $dom = $n('dipesan') ? 'dipesan' : ($n('tersedia') ? 'tersedia' : ($n('ditutup') ? 'ditutup' : null));
            $kelas = ['dipesan' => 'bg-violet-50 border-violet-200 text-brand', 'tersedia' => 'bg-emerald-50 border-emerald-200 text-emerald-800', 'ditutup' => 'bg-slate-50 border-slate-200 text-slate-500'][$dom] ?? 'bg-slate-50/60 border-transparent text-slate-400';
            $aktifTgl = $tanggal && $tanggal->toDateString() === $c['tgl'];
          @endphp
          @if ($dom)
            <a href="{{ $ke(['tanggal' => $aktifTgl ? null : $c['tgl'], 'status' => null]) }}" class="flex min-h-[3.75rem] flex-col items-center justify-center gap-0.5 rounded-xl border px-1 py-1.5 text-sm font-bold {{ $kelas }} {{ $aktifTgl ? 'ring-2 ring-brand' : '' }}">
              {{ $c['hari'] }}
              @foreach (['dipesan', 'tersedia', 'ditutup'] as $st)@if ($n($st))<span class="rounded-full px-1.5 text-[9px] font-semibold {{ $chip[$st] }}">{{ $n($st) }} {{ $label[$st] }}</span>@endif @endforeach
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
      @foreach (['semua' => 'Semua Slot', 'tersedia' => 'Tersedia', 'dipesan' => 'Dipesan', 'ditutup' => 'Ditutup'] as $k => $t)
        @php($aktifTab = ($status ?: 'semua') === $k)
        <a href="{{ $ke(['status' => $k === 'semua' ? null : $k]) }}" class="rounded-full px-4 py-2 text-sm font-semibold {{ $aktifTab ? 'bg-brand text-white' : 'text-slate-600 hover:bg-brand-50' }}">{{ $t }} <span class="{{ $aktifTab ? '' : 'text-brand' }}">({{ $tab[$k] }})</span></a>
      @endforeach
      <form method="GET" class="ml-auto flex flex-wrap items-center gap-2">
        @if ($status)<input type="hidden" name="status" value="{{ $status }}">@endif
        @if ($tanggal)<input type="hidden" name="tanggal" value="{{ $tanggal->toDateString() }}">@endif
        <input name="q" value="{{ $cari }}" placeholder="Cari mentee / tautan / ID…" class="w-52 rounded-xl border-0 bg-slate-50 px-3 py-2 text-sm focus:ring-brand">
        <select name="bulan" onchange="this.form.submit()" class="rounded-xl border-0 bg-slate-50 px-3 py-2 text-sm font-semibold focus:ring-brand">
          @foreach ($opsiBulan as $v => $l)<option value="{{ $v }}" @selected($v === $bulan->format('Y-m'))>{{ $l }}</option>@endforeach
        </select>
      </form>
      <form method="POST" action="{{ route('mentor.slot.tutup-massal') }}" onsubmit="return confirm('Tutup slot yang dipilih?')">@csrf
        <template x-for="id in pilih" :key="id"><input type="hidden" name="ids[]" :value="id"></template>
        <button :disabled="pilih.length === 0" class="rounded-xl bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-600 enabled:bg-red-50 enabled:text-red-700 disabled:cursor-not-allowed disabled:text-slate-400">Tutup Slot Terpilih (<span x-text="pilih.length"></span>)</button>
      </form>
    </div>
    @if ($tanggal)<p class="mt-2 px-2 text-xs text-slate-500">Menampilkan slot tanggal <b>{{ $tglIndo($tanggal) }}</b>. <a href="{{ $ke(['tanggal' => null]) }}" class="font-bold text-brand">Hapus filter tanggal</a></p>@endif
  </section>

  {{-- Tabel slot --}}
  <section class="mt-4 overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-100">
    <div class="overflow-x-auto">
      <table class="w-full min-w-[56rem] text-left text-sm">
        <thead class="bg-brand-50 text-[11px] font-bold uppercase tracking-wider text-slate-500">
          <tr>
            <th class="w-10 px-4 py-3"><input type="checkbox" class="rounded border-slate-300 text-brand focus:ring-brand" :checked="semuaDipilih()" @change="toggleSemua()" aria-label="Pilih semua slot tersedia di halaman ini"></th>
            <th class="px-3 py-3">ID &amp; Tanggal</th><th class="px-3 py-3">Jam Sesi (Durasi)</th><th class="px-3 py-3">Status Slot</th>
            <th class="px-3 py-3">Tautan Pertemuan (Daring)</th><th class="px-3 py-3">Mentee / Keterangan</th><th class="px-3 py-3 text-right">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          @forelse ($slot as $s)
            @php($mulai = substr($s->jam_mulai, 0, 5))@php($selesaiJam = substr($s->jam_selesai, 0, 5))
            @php($menit = \Carbon\Carbon::parse($s->jam_mulai)->diffInMinutes(\Carbon\Carbon::parse($s->jam_selesai)))
            <tr class="align-middle {{ $s->status === 'ditutup' ? 'text-slate-400' : '' }}">
              <td class="px-4 py-4">@if ($s->status === 'tersedia')<input type="checkbox" value="{{ $s->id }}" x-model="pilih" class="rounded border-slate-300 text-brand focus:ring-brand" aria-label="Pilih slot {{ $s->id }}">@endif</td>
              <td class="px-3 py-4"><p class="text-[11px] font-bold text-brand">#SLT-{{ str_pad($s->id, 4, '0', STR_PAD_LEFT) }}</p>
                <p class="font-bold text-slate-900">{{ $tglIndo($s->tanggal) }}</p><p class="text-xs text-slate-500">{{ $namaHari[$s->tanggal->dayOfWeek] }}</p></td>
              <td class="px-3 py-4"><p class="font-semibold text-slate-800">{{ $mulai }} – {{ $selesaiJam }} WIB</p><p class="text-xs text-slate-500">{{ $menit }} menit</p></td>
              <td class="px-3 py-4"><span class="rounded-full px-3 py-1 text-xs font-bold {{ $chip[$s->status] }}">{{ $label[$s->status] }}</span></td>
              <td class="px-3 py-4">
                @if ($s->tautan_pertemuan)
                  <span class="inline-flex max-w-[16rem] items-center gap-2 rounded-lg bg-brand-50 px-3 py-2 font-mono text-xs {{ $s->status === 'ditutup' ? 'text-slate-400 line-through' : 'text-slate-700' }}">
                    <span class="truncate">{{ preg_replace('#^https?://#', '', $s->tautan_pertemuan) }}</span>
                    @if ($s->status !== 'ditutup')<button type="button" @click="salin(@js($s->tautan_pertemuan))" class="shrink-0 text-slate-400 hover:text-brand" aria-label="Salin tautan">⧉</button>@endif
                  </span>
                @else<span class="text-xs italic text-slate-400">Belum ada tautan</span>@endif
              </td>
              <td class="px-3 py-4">
                @if ($s->status === 'dipesan' && $s->sesi)
                  <p class="font-bold text-slate-900">{{ $s->sesi->pengguna?->name }}</p><p class="text-xs text-slate-500">{{ \Illuminate\Support\Str::limit($s->sesi->tujuan, 40) }}</p>
                @elseif ($s->status === 'tersedia')<span class="text-xs italic text-slate-400">Belum ada pemesan</span>
                @else<span class="text-xs text-slate-500">Ditutup oleh mentor</span>@endif
              </td>
              <td class="px-3 py-4">
                <div class="flex justify-end gap-2 text-xs font-bold">
                  @if ($s->status === 'tersedia')
                    <form method="POST" action="{{ route('mentor.slot.tutup', $s) }}">@csrf @method('PATCH')<button class="rounded-lg bg-slate-100 px-3 py-1.5 text-slate-600 hover:bg-slate-200">Tutup</button></form>
                  @elseif ($s->status === 'ditutup')
                    <form method="POST" action="{{ route('mentor.slot.buka', $s) }}">@csrf @method('PATCH')<button class="rounded-lg bg-brand-100 px-3 py-1.5 text-brand hover:bg-brand-50">Buka</button></form>
                  @endif
                  @if ($s->status !== 'dipesan')
                    <form method="POST" action="{{ route('mentor.slot.hapus', $s) }}" onsubmit="return confirm('Hapus slot ini?')">@csrf @method('DELETE')<button class="rounded-lg bg-red-50 px-3 py-1.5 text-red-700 hover:bg-red-100">Hapus</button></form>
                  @endif
                </div>
              </td>
            </tr>
          @empty
            <tr><td colspan="7" class="px-6 py-12 text-center text-slate-500">Belum ada slot untuk filter ini. Klik <b>+ Tambah Slot Baru</b> untuk membuat jadwal.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="flex items-center justify-between border-t border-slate-100 px-5 py-3 text-xs text-slate-500">
      <span>Menampilkan {{ $slot->firstItem() ?? 0 }} - {{ $slot->lastItem() ?? 0 }} dari {{ $slot->total() }} slot</span>
      <span class="flex gap-2">
        @if ($slot->previousPageUrl())<a href="{{ $slot->previousPageUrl() }}" class="rounded-lg bg-brand-50 px-3 py-1.5 font-semibold text-slate-600 hover:bg-brand-100">‹ Sebelumnya</a>@endif
        @if ($slot->nextPageUrl())<a href="{{ $slot->nextPageUrl() }}" class="rounded-lg bg-brand-50 px-3 py-1.5 font-semibold text-slate-600 hover:bg-brand-100">Berikutnya ›</a>@endif
      </span>
    </div>
  </section>

  {{-- Modal tambah slot --}}
  <div x-show="modal" x-cloak @keydown.escape.window="modal = false" class="fixed inset-0 z-50 grid place-items-center bg-slate-900/40 p-4" role="dialog" aria-modal="true" aria-labelledby="judul-modal">
    <form method="POST" action="{{ route('mentor.slot.store') }}" @click.outside="modal = false" class="w-full max-w-md rounded-3xl bg-white p-6 shadow-xl">@csrf
      <h2 id="judul-modal" class="text-xl font-extrabold text-slate-900">Tambah Slot Baru</h2>
      <p class="mt-1 text-xs text-slate-500">Slot yang tersedia akan tampil di katalog dan bisa dipesan mentee.</p>
      @if ($bag->any())<div class="mt-4 rounded-xl bg-red-50 px-4 py-3 text-sm text-red-700">@foreach ($bag->all() as $e)<p>{{ $e }}</p>@endforeach</div>@endif
      <label class="mt-4 block text-sm font-semibold text-slate-700">Tanggal
        <input type="date" name="tanggal" value="{{ old('tanggal') }}" min="{{ now()->toDateString() }}" required class="mt-1 w-full rounded-xl border-slate-200 text-sm focus:border-brand focus:ring-brand"></label>
      <div class="mt-3 grid grid-cols-2 gap-3">
        <label class="block text-sm font-semibold text-slate-700">Jam mulai
          <input type="time" name="jam_mulai" value="{{ old('jam_mulai', '19:00') }}" required class="mt-1 w-full rounded-xl border-slate-200 text-sm focus:border-brand focus:ring-brand"></label>
        <label class="block text-sm font-semibold text-slate-700">Jam selesai
          <input type="time" name="jam_selesai" value="{{ old('jam_selesai', '19:45') }}" required class="mt-1 w-full rounded-xl border-slate-200 text-sm focus:border-brand focus:ring-brand"></label>
      </div>
      <label class="mt-3 block text-sm font-semibold text-slate-700">Tautan pertemuan <span class="font-normal text-slate-400">(opsional)</span>
        <input type="url" name="tautan_pertemuan" value="{{ old('tautan_pertemuan') }}" placeholder="https://meet.google.com/abc-defg-hij" class="mt-1 w-full rounded-xl border-slate-200 text-sm focus:border-brand focus:ring-brand"></label>
      <div class="mt-6 flex justify-end gap-2">
        <button type="button" @click="modal = false" class="rounded-xl px-4 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50">Batal</button>
        <button class="rounded-xl bg-brand px-5 py-2.5 text-sm font-bold text-white hover:bg-brand-600">Simpan Slot</button>
      </div>
    </form>
  </div>
</div>
@endsection