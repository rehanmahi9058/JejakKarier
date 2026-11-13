@extends('layouts.dashboard')
@section('judul', 'Profil Saya')
@section('isi')
@if (! $u->adalahMentor() && ! $u->adalahAdmin())
@php
  $foto = $u->fotoUrl();
  $input = 'mt-2 w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-800 placeholder-slate-400 focus:border-brand focus:bg-white focus:ring-brand';
  $minatAwal = collect(explode(',', (string) old('minat', $profil->minat)))->map(fn ($t) => trim($t))->filter()->unique()->values()->all();
@endphp

<h1 class="text-4xl font-extrabold tracking-tight text-[#1a1446]">Profil Saya</h1>
<p class="mt-2 text-slate-500">Lengkapi data diri, minat, dan tujuan kariermu untuk rekomendasi yang optimal.</p>

<form method="POST" action="{{ route('profil.update') }}" enctype="multipart/form-data"
      class="mt-8 rounded-3xl bg-white p-8 shadow-sm ring-1 ring-slate-100"
      x-data="{ pratinjau: null, bio: @js(old('bio', $profil->bio ?? '')), tags: @js($minatAwal), baru: '', menambah: false,
        tambah() { this.baru.split(',').map(t => t.trim()).filter(t => t && !this.tags.some(x => x.toLowerCase() === t.toLowerCase())).forEach(t => this.tags.push(t)); this.baru = ''; this.menambah = false; } }">
  @csrf @method('PUT')

  {{-- Foto & status --}}
  <div class="flex flex-wrap items-center gap-6 border-b border-slate-100 pb-8">
    <div class="relative shrink-0">
      <span class="grid h-24 w-24 place-items-center overflow-hidden rounded-full bg-brand-100 text-3xl font-bold text-brand ring-2 ring-brand/30 ring-offset-2">
        <img x-show="pratinjau" :src="pratinjau" alt="" class="h-full w-full object-cover" x-cloak>
        @if ($foto)<img x-show="!pratinjau" src="{{ $foto }}" alt="Foto profil" class="h-full w-full object-cover">@else<span x-show="!pratinjau">{{ mb_substr($u->name, 0, 1) }}</span>@endif
      </span>
      <label for="foto" class="absolute -bottom-0.5 -right-0.5 grid h-8 w-8 cursor-pointer place-items-center rounded-full bg-brand text-white ring-2 ring-white" title="Ganti foto">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z"/><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0z"/></svg>
      </label>
    </div>
    <div>
      <div class="flex items-center gap-3">
        <p class="text-xl font-bold text-[#1a1446]">{{ $u->name }}</p>
        <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $u->aktif ? 'bg-emerald-50 text-emerald-600 ring-1 ring-emerald-200' : 'bg-slate-100 text-slate-500' }}">{{ $u->aktif ? 'Aktif' : 'Nonaktif' }}</span>
      </div>
      <p class="mt-1 text-sm text-slate-500">Format JPG, GIF, atau PNG. Ukuran maksimal 2MB.</p>
      <input id="foto" type="file" name="foto" accept="image/png,image/jpeg,image/gif,image/webp" class="sr-only"
             @change="pratinjau = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : null">
      <div class="mt-3 flex flex-wrap items-center gap-4">
        <label for="foto" class="inline-flex cursor-pointer items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
          <svg class="h-4 w-4 text-brand" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.41a2.25 2.25 0 013.182 0l2.909 2.91M3.75 21h16.5a1.5 1.5 0 001.5-1.5V4.5a1.5 1.5 0 00-1.5-1.5H3.75a1.5 1.5 0 00-1.5 1.5v15a1.5 1.5 0 001.5 1.5z"/></svg>
          Ganti foto
        </label>
        @if ($foto)<label class="flex items-center gap-2 text-xs text-slate-500"><input type="checkbox" name="hapus_foto" value="1" class="rounded border-slate-300 text-brand focus:ring-brand"> Hapus foto saat ini</label>@endif
      </div>
      @error('foto')<p class="mt-2 text-xs font-semibold text-red-600">{{ $message }}</p>@enderror
    </div>
  </div>

  {{-- Data diri --}}
  <div class="mt-8 grid gap-x-6 gap-y-6 md:grid-cols-2">
    <div><label for="name" class="block text-sm font-semibold text-[#1a1446]">Nama lengkap</label>
      <input id="name" name="name" value="{{ old('name', $u->name) }}" required class="{{ $input }}">
      @error('name')<p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p>@enderror</div>
    <div><label for="email" class="block text-sm font-semibold text-[#1a1446]">Email</label>
      <input id="email" type="email" name="email" value="{{ old('email', $u->email) }}" required class="{{ $input }}">
      @error('email')<p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p>@enderror</div>
    <div><label for="pendidikan" class="block text-sm font-semibold text-[#1a1446]">Pendidikan / status saat ini</label>
      <input id="pendidikan" name="pendidikan" value="{{ old('pendidikan', $profil->pendidikan) }}" placeholder="mis. Mahasiswa Teknologi Informasi" class="{{ $input }}">
      @error('pendidikan')<p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p>@enderror</div>
    <div><label for="tujuan_karier" class="block text-sm font-semibold text-[#1a1446]">Tujuan karier</label>
      <input id="tujuan_karier" name="tujuan_karier" value="{{ old('tujuan_karier', $profil->tujuan_karier) }}" placeholder="mis. Menjadi Data Analyst" class="{{ $input }}">
      @error('tujuan_karier')<p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p>@enderror</div>
  </div>

  {{-- Minat (tag) --}}
  <div class="mt-6">
    <span class="block text-sm font-semibold text-[#1a1446]">Minat</span>
    <input type="hidden" name="minat" :value="tags.join(', ')">
    <div class="mt-3 flex flex-wrap items-center gap-3">
      <template x-for="(t, i) in tags" :key="t">
        <span class="inline-flex items-center gap-2 rounded-full bg-[#1f1a4d] px-4 py-1.5 text-xs font-semibold text-white">
          <span x-text="t"></span>
          <button type="button" @click="tags.splice(i, 1)" class="text-white/80 hover:text-white" :aria-label="'Hapus ' + t">
            <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 6l12 12M18 6L6 18"/></svg>
          </button>
        </span>
      </template>
      <button type="button" x-show="!menambah" @click="menambah = true; $nextTick(() => $refs.minatBaru.focus())"
              class="inline-flex items-center gap-1.5 rounded-full border border-dashed border-slate-300 bg-slate-50 px-4 py-1.5 text-xs font-semibold text-slate-600 hover:border-brand hover:text-brand">
        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 5v14M5 12h14"/></svg> Tambah
      </button>
      <input x-show="menambah" x-cloak x-ref="minatBaru" x-model="baru" @keydown.enter.prevent="tambah()" @keydown.escape="baru = ''; menambah = false" @blur="tambah()"
             placeholder="Ketik minat, lalu Enter" class="w-48 rounded-full border-slate-200 bg-white px-4 py-1.5 text-xs focus:border-brand focus:ring-brand">
    </div>
    @error('minat')<p class="mt-2 text-xs font-semibold text-red-600">{{ $message }}</p>@enderror
  </div>

  {{-- Tentang saya --}}
  <div class="mt-6">
    <label for="bio" class="block text-sm font-semibold text-[#1a1446]">Tentang saya</label>
    <textarea id="bio" name="bio" rows="4" maxlength="300" x-model="bio" placeholder="Ceritakan sedikit tentang dirimu" class="{{ $input }} resize-none"></textarea>
    <p class="mt-2 text-right text-xs text-slate-400">Maksimal 300 karakter <span x-text="'· ' + bio.length + '/300'"></span></p>
    @error('bio')<p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p>@enderror
  </div>

  {{-- Aksi --}}
  <div class="mt-8 flex flex-wrap items-center justify-between gap-4 border-t border-slate-100 pt-6">
    <p class="flex items-center gap-2 text-xs text-slate-500">
      <svg class="h-4 w-4 text-brand" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/></svg>
      Data privasi profil Anda terlindungi dan aman
    </p>
    <div class="flex items-center gap-3">
      <a href="{{ route('profil.edit') }}" class="rounded-xl border border-slate-200 bg-white px-6 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Batal</a>
      <button class="inline-flex items-center gap-2 rounded-xl bg-brand px-6 py-2.5 text-sm font-bold text-white shadow-lg shadow-brand/30 hover:bg-brand-600">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg> Simpan profil
      </button>
    </div>
  </div>
</form>

<div class="mt-6">    <form method="POST" action="{{ route('profil.password') }}" class="rounded-3xl bg-white p-6 shadow-sm ring-1 ring-slate-100">
      @csrf @method('PUT')
      <h2 class="text-lg font-bold text-slate-900">Ganti kata sandi</h2>
      <div class="mt-4 grid gap-4 md:grid-cols-3">
        <div><label for="password_lama" class="block text-sm font-semibold text-slate-700">Kata sandi saat ini</label>
          <input id="password_lama" type="password" name="password_lama" autocomplete="current-password" class="{{ $input }}">
          @error('password_lama', 'password')<p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p>@enderror</div>
        <div><label for="password" class="block text-sm font-semibold text-slate-700">Kata sandi baru</label>
          <input id="password" type="password" name="password" autocomplete="new-password" class="{{ $input }}">
          @error('password', 'password')<p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p>@enderror</div>
        <div><label for="password_confirmation" class="block text-sm font-semibold text-slate-700">Ulangi kata sandi baru</label>
          <input id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password" class="{{ $input }}"></div>
      </div>
      <button class="mt-5 rounded-xl bg-brand-100 px-6 py-3 text-sm font-bold text-brand">Ganti Kata Sandi</button>
    </form></div>
@else
@php
  $mentor = $u->adalahMentor();
  $admin = $u->adalahAdmin();
  $foto = $u->fotoUrl();
  $input = 'mt-1 w-full rounded-xl border-slate-200 text-sm focus:border-brand focus:ring-brand';
  $dipilih = collect(old('skill', $skillSaya->keys()->all()))->map(fn ($x) => (int) $x)->all();
@endphp

<div class="flex flex-wrap items-center justify-between gap-4">
  <div>
    <h1 class="text-4xl font-extrabold tracking-tight text-slate-900">Profil Saya</h1>
    <p class="mt-1 max-w-xl text-slate-500">
      {{ $mentor ? 'Data ini tampil di katalog dan halaman publikmu. Lengkapi agar pengguna mengenal pengalamanmu.' : ($admin ? 'Kelola data akun administrator.' : 'Lengkapi profilmu agar mentor mengenal minat dan tujuan kariermu.') }}
    </p>
  </div>
  @if ($mentor)
    @php($st = $profil->status_verifikasi)
    <span class="rounded-full px-4 py-2 text-xs font-bold {{ $st === 'terverifikasi' ? 'bg-emerald-100 text-emerald-700' : ($st === 'ditolak' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700') }}">
      {{ $st === 'terverifikasi' ? '✔ Terverifikasi' : ($st === 'ditolak' ? 'Verifikasi ditolak' : 'Menunggu verifikasi admin') }}
    </span>
  @endif
</div>
@if ($mentor && $profil->status_verifikasi === 'ditolak' && $profil->catatan_verifikasi)
  <p class="mt-4 rounded-xl bg-red-50 px-4 py-3 text-sm text-red-700"><b>Catatan admin:</b> {{ $profil->catatan_verifikasi }}</p>
@endif

<div class="mt-8 grid gap-6 xl:grid-cols-[1fr_320px]">
  <div class="space-y-6">

    {{-- ===== Form profil ===== --}}
    <form method="POST" action="{{ route('profil.update') }}" enctype="multipart/form-data" class="space-y-6">
      @csrf @method('PUT')

      <section class="rounded-3xl bg-white p-6 shadow-sm ring-1 ring-slate-100" x-data="{ nama: '{{ addslashes($u->name) }}', pratinjau: null }">
        <h2 class="text-lg font-bold text-slate-900">Foto &amp; akun</h2>
        @unless ($admin)
        <div class="mt-4 flex flex-wrap items-center gap-5">
          <span class="grid h-24 w-24 shrink-0 place-items-center overflow-hidden rounded-3xl bg-brand-100 text-3xl font-bold text-brand">
            <img x-show="pratinjau" :src="pratinjau" alt="" class="h-full w-full object-cover" x-cloak>
            @if ($foto)<img x-show="!pratinjau" src="{{ $foto }}" alt="Foto profil" class="h-full w-full object-cover">@else<span x-show="!pratinjau">{{ mb_substr($u->name, 0, 1) }}</span>@endif
          </span>
          <div class="text-sm">
            <label class="font-semibold text-slate-700">Ganti foto</label>
            <input type="file" name="foto" accept="image/png,image/jpeg,image/webp" @change="pratinjau = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : null" class="mt-1 block text-sm text-slate-600 file:mr-3 file:rounded-xl file:border-0 file:bg-brand-100 file:px-4 file:py-2 file:text-sm file:font-bold file:text-brand">
            <p class="mt-1 text-xs text-slate-400">JPG, PNG, atau WEBP. Maksimal 2 MB.</p>
            @if ($foto)<label class="mt-2 flex items-center gap-2 text-xs text-slate-500"><input type="checkbox" name="hapus_foto" value="1" class="rounded border-slate-300 text-brand focus:ring-brand"> Hapus foto saat ini</label>@endif
            @error('foto')<p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p>@enderror
          </div>
        </div>
        @endunless

        <div class="mt-5 grid gap-4 md:grid-cols-2">
          <div><label for="name" class="block text-sm font-semibold text-slate-700">Nama lengkap</label>
            <input id="name" name="name" value="{{ old('name', $u->name) }}" required class="{{ $input }}">
            @error('name')<p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p>@enderror</div>
          <div><label for="email" class="block text-sm font-semibold text-slate-700">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email', $u->email) }}" required class="{{ $input }}">
            @error('email')<p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p>@enderror</div>
        </div>
      </section>

      @if ($mentor)
      <section class="rounded-3xl bg-white p-6 shadow-sm ring-1 ring-slate-100">
        <h2 class="text-lg font-bold text-slate-900">Profil mentor</h2>
        <div class="mt-4 grid gap-4 md:grid-cols-2">
          <div><label for="jabatan" class="block text-sm font-semibold text-slate-700">Jabatan</label>
            <input id="jabatan" name="jabatan" value="{{ old('jabatan', $profil->jabatan) }}" placeholder="mis. Senior Product Designer" required class="{{ $input }}">
            @error('jabatan')<p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p>@enderror</div>
          <div><label for="bidang_karier_id" class="block text-sm font-semibold text-slate-700">Bidang karier</label>
            <select id="bidang_karier_id" name="bidang_karier_id" class="{{ $input }}">
              <option value="">Pilih bidang</option>
              @foreach ($bidang as $b)<option value="{{ $b->id }}" @selected(old('bidang_karier_id', $profil->bidang_karier_id) == $b->id)>{{ $b->nama }}</option>@endforeach
            </select>
            @error('bidang_karier_id')<p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p>@enderror</div>
          <div><label for="tahun_pengalaman" class="block text-sm font-semibold text-slate-700">Pengalaman (tahun)</label>
            <input id="tahun_pengalaman" type="number" min="0" max="60" name="tahun_pengalaman" value="{{ old('tahun_pengalaman', $profil->tahun_pengalaman) }}" required class="{{ $input }}">
            @error('tahun_pengalaman')<p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p>@enderror</div>
        </div>
        <div class="mt-4"><label for="pengalaman_kerja" class="block text-sm font-semibold text-slate-700">Pengalaman kerja</label>
          <textarea id="pengalaman_kerja" name="pengalaman_kerja" rows="4" placeholder="Ringkas perjalanan kariermu" class="{{ $input }}">{{ old('pengalaman_kerja', $profil->pengalaman_kerja) }}</textarea>
          @error('pengalaman_kerja')<p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p>@enderror</div>
        <div class="mt-4"><label for="topik_mentoring" class="block text-sm font-semibold text-slate-700">Topik mentoring</label>
          <textarea id="topik_mentoring" name="topik_mentoring" rows="3" placeholder="Apa yang bisa dipelajari mentee darimu?" class="{{ $input }}">{{ old('topik_mentoring', $profil->topik_mentoring) }}</textarea>
          @error('topik_mentoring')<p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p>@enderror</div>
      </section>

      <section class="rounded-3xl bg-white p-6 shadow-sm ring-1 ring-slate-100">
        <div class="flex items-center justify-between"><h2 class="text-lg font-bold text-slate-900">Keterampilan</h2>
          <span class="text-xs font-semibold text-slate-400">{{ count($dipilih) }} dipilih · minimal 3</span></div>
        <div class="mt-4 grid gap-3 md:grid-cols-2">
          @forelse ($semuaSkill as $k)
            <div class="flex items-center gap-3 rounded-xl border border-slate-200 px-3 py-2">
              <input type="checkbox" name="skill[]" value="{{ $k->id }}" id="sk{{ $k->id }}" @checked(in_array($k->id, $dipilih)) class="rounded border-slate-300 text-brand focus:ring-brand">
              <label for="sk{{ $k->id }}" class="flex-1 cursor-pointer text-sm font-semibold text-slate-700">{{ $k->nama }}</label>
              <select name="level[{{ $k->id }}]" class="rounded-lg border-slate-200 py-1 pl-2 pr-7 text-xs focus:border-brand focus:ring-brand">
                @foreach (['pemula' => 'Pemula', 'menengah' => 'Menengah', 'mahir' => 'Mahir', 'ahli' => 'Ahli'] as $nilai => $label)
                  <option value="{{ $nilai }}" @selected(old("level.$k->id", $skillSaya[$k->id] ?? 'menengah') === $nilai)>{{ $label }}</option>
                @endforeach
              </select>
            </div>
          @empty
            <p class="text-sm text-slate-500 md:col-span-2">Belum ada daftar keterampilan. Tambahkan lewat kolom di bawah.</p>
          @endforelse
        </div>
        @error('skill.*')<p class="mt-2 text-xs font-semibold text-red-600">{{ $message }}</p>@enderror
        <div class="mt-4"><label for="skill_baru" class="block text-sm font-semibold text-slate-700">Keterampilan lain <span class="font-normal text-slate-400">(opsional)</span></label>
          <input id="skill_baru" name="skill_baru" value="{{ old('skill_baru') }}" placeholder="Pisahkan dengan koma, mis. Figma, Riset Pengguna" class="{{ $input }}">
          <p class="mt-1 text-xs text-slate-400">Keterampilan baru otomatis diberi level Menengah.</p></div>
      </section>

      @elseif (! $admin)
      <section class="rounded-3xl bg-white p-6 shadow-sm ring-1 ring-slate-100">
        <h2 class="text-lg font-bold text-slate-900">Tentang kamu</h2>
        <div class="mt-4 grid gap-4 md:grid-cols-2">
          <div><label for="pendidikan" class="block text-sm font-semibold text-slate-700">Pendidikan</label>
            <input id="pendidikan" name="pendidikan" value="{{ old('pendidikan', $profil->pendidikan) }}" placeholder="mis. S1 Teknologi Informasi" class="{{ $input }}">
            @error('pendidikan')<p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p>@enderror</div>
          <div><label for="kota" class="block text-sm font-semibold text-slate-700">Kota</label>
            <input id="kota" name="kota" value="{{ old('kota', $profil->kota) }}" placeholder="mis. Malang" class="{{ $input }}">
            @error('kota')<p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p>@enderror</div>
          <div><label for="minat" class="block text-sm font-semibold text-slate-700">Minat</label>
            <input id="minat" name="minat" value="{{ old('minat', $profil->minat) }}" placeholder="mis. Data Analysis, UI/UX" class="{{ $input }}">
            @error('minat')<p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p>@enderror</div>
          <div><label for="tujuan_karier" class="block text-sm font-semibold text-slate-700">Tujuan karier</label>
            <input id="tujuan_karier" name="tujuan_karier" value="{{ old('tujuan_karier', $profil->tujuan_karier) }}" placeholder="mis. Menjadi Data Analyst" class="{{ $input }}">
            @error('tujuan_karier')<p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p>@enderror</div>
        </div>
        <div class="mt-4"><label for="bio" class="block text-sm font-semibold text-slate-700">Bio singkat</label>
          <textarea id="bio" name="bio" rows="4" placeholder="Ceritakan sedikit tentang dirimu" class="{{ $input }}">{{ old('bio', $profil->bio) }}</textarea>
          @error('bio')<p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p>@enderror</div>
      </section>
      @endif

      <button class="rounded-xl bg-brand px-8 py-3 text-sm font-bold text-white shadow-lg shadow-brand/30">Simpan Profil</button>
    </form>

    {{-- ===== Ganti kata sandi ===== --}}
    <form method="POST" action="{{ route('profil.password') }}" class="rounded-3xl bg-white p-6 shadow-sm ring-1 ring-slate-100">
      @csrf @method('PUT')
      <h2 class="text-lg font-bold text-slate-900">Ganti kata sandi</h2>
      <div class="mt-4 grid gap-4 md:grid-cols-3">
        <div><label for="password_lama" class="block text-sm font-semibold text-slate-700">Kata sandi saat ini</label>
          <input id="password_lama" type="password" name="password_lama" autocomplete="current-password" class="{{ $input }}">
          @error('password_lama', 'password')<p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p>@enderror</div>
        <div><label for="password" class="block text-sm font-semibold text-slate-700">Kata sandi baru</label>
          <input id="password" type="password" name="password" autocomplete="new-password" class="{{ $input }}">
          @error('password', 'password')<p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p>@enderror</div>
        <div><label for="password_confirmation" class="block text-sm font-semibold text-slate-700">Ulangi kata sandi baru</label>
          <input id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password" class="{{ $input }}"></div>
      </div>
      <button class="mt-5 rounded-xl bg-brand-100 px-6 py-3 text-sm font-bold text-brand">Ganti Kata Sandi</button>
    </form>
  </div>

  {{-- ===== Kolom samping ===== --}}
  <aside class="space-y-6">
    <section class="rounded-3xl bg-white p-6 text-center shadow-sm ring-1 ring-slate-100">
      <span class="mx-auto grid h-20 w-20 place-items-center overflow-hidden rounded-3xl bg-brand-100 text-2xl font-bold text-brand">
        @if ($foto)<img src="{{ $foto }}" alt="" class="h-full w-full object-cover">@else{{ mb_substr($u->name, 0, 1) }}@endif
      </span>
      <p class="mt-3 font-bold text-slate-900">{{ $u->name }}</p>
      <p class="text-sm text-slate-500">{{ $mentor ? ($profil->jabatan ?: 'Mentor') : ($admin ? 'Administrator' : ($profil->tujuan_karier ?: 'Pengguna')) }}</p>
      <p class="mt-1 text-xs text-slate-400">{{ $u->email }}</p>
    </section>

    @if ($mentor)
    <section class="rounded-3xl bg-white p-6 shadow-sm ring-1 ring-slate-100">
      <h3 class="font-bold text-slate-900">Status tampil di katalog</h3>
      <ul class="mt-3 space-y-2.5 text-sm">
        @foreach ($syarat as $teks => $ok)
          <li class="flex items-start gap-2.5"><span class="mt-0.5 grid h-5 w-5 shrink-0 place-items-center rounded-full text-[11px] font-bold {{ $ok ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-400' }}">{{ $ok ? '✓' : '–' }}</span><span class="{{ $ok ? 'text-slate-700' : 'text-slate-500' }}">{{ $teks }}</span></li>
        @endforeach
      </ul>
      @if (collect($syarat)->every(fn ($v) => $v))
        <a href="{{ route('katalog.detail', $u) }}" class="mt-4 inline-block text-sm font-bold text-brand">Lihat halaman publikku →</a>
      @endif
    </section>
    @elseif ($lengkap !== null)
    <section class="rounded-3xl bg-gradient-to-br from-[#4a1fe0] to-brand p-6 text-white">
      <p class="text-[11px] font-bold uppercase tracking-widest text-white/70">Kelengkapan profil</p>
      <p class="mt-2 text-3xl font-extrabold">{{ $lengkap }}%</p>
      <div class="mt-3 h-2 rounded-full bg-white/20"><div class="h-2 rounded-full bg-white" style="width: {{ $lengkap }}%"></div></div>
      <p class="mt-3 text-xs text-white/70">Isi minat, pendidikan, dan bio untuk mencapai 100%.</p>
    </section>
    @endif
  </aside>
</div>
@endif
@endsection