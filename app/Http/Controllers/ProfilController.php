<?php
namespace App\Http\Controllers;

use App\Models\{BidangKarier, Keterampilan, ProfilMentor, ProfilPengguna};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Storage};
use Illuminate\Validation\Rule;

class ProfilController extends Controller
{
    public function edit(Request $r)
    {
        $u = $r->user();
        $mentor = $u->adalahMentor();
        $profil = match (true) {
            $mentor => ProfilMentor::firstOrCreate(['user_id' => $u->id]),
            $u->adalahAdmin() => null,
            default => ProfilPengguna::firstOrCreate(['user_id' => $u->id]),
        };

        $data = ['u' => $u, 'profil' => $profil, 'bidang' => collect(), 'semuaSkill' => collect(), 'skillSaya' => collect(), 'syarat' => [], 'lengkap' => null];

        if ($mentor) {
            $data['bidang'] = BidangKarier::orderBy('nama')->get();
            $data['semuaSkill'] = Keterampilan::orderBy('nama')->get();
            $data['skillSaya'] = $u->keterampilan()->pluck('users_keterampilan.level', 'keterampilan.id');
            // Syarat tampil di katalog (sama dengan User::scopeLayakKatalog)
            $data['syarat'] = [
                'Profil diverifikasi admin' => $profil->status_verifikasi === 'terverifikasi',
                'Minimal 1 entri portofolio berstatus terbit' => $u->portofolio()->where('status', 'terbit')->exists(),
                'Minimal 3 keterampilan' => $u->keterampilan()->count() >= 3,
                'Akun aktif' => $u->aktif,
            ];
        } elseif ($profil) {
            // Rumus sama dengan kartu "Kelengkapan profil" di dashboard pengguna
            $data['lengkap'] = (int) round(collect([$profil->minat, $profil->kota, $profil->bio])->filter(fn ($v) => filled($v))->count() / 3 * 100);
        }

        return view('profil.edit', $data);
    }

    public function update(Request $r)
    {
        $u = $r->user();
        $mentor = $u->adalahMentor();

        $aturan = [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:190', Rule::unique('users', 'email')->ignore($u->id)],
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'hapus_foto' => ['nullable', 'boolean'],
        ];
        $kolom = [];
        if ($mentor) {
            $kolom = ['bidang_karier_id', 'jabatan', 'tahun_pengalaman', 'pengalaman_kerja', 'topik_mentoring'];
            $aturan += [
                'bidang_karier_id' => ['nullable', 'exists:bidang_karier,id'],
                'jabatan' => ['required', 'string', 'max:120'],
                'tahun_pengalaman' => ['required', 'integer', 'min:0', 'max:60'],
                'pengalaman_kerja' => ['nullable', 'string', 'max:2000'],
                'topik_mentoring' => ['nullable', 'string', 'max:1000'],
                'skill' => ['nullable', 'array'], 'skill.*' => ['exists:keterampilan,id'],
                'level' => ['nullable', 'array'], 'level.*' => ['in:pemula,menengah,mahir,ahli'],
                'skill_baru' => ['nullable', 'string', 'max:300'],
            ];
        } elseif (! $u->adalahAdmin()) {
            $kolom = ['pendidikan', 'minat', 'kota', 'tujuan_karier', 'bio'];
            $aturan += [
                'pendidikan' => ['nullable', 'string', 'max:120'],
                'minat' => ['nullable', 'string', 'max:255'],
                'kota' => ['nullable', 'string', 'max:80'],
                'tujuan_karier' => ['nullable', 'string', 'max:160'],
                'bio' => ['nullable', 'string', 'max:1000'],
            ];
        }

        $v = $r->validate($aturan, [
            'name.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email ini sudah dipakai akun lain.',
            'foto.image' => 'File foto harus berupa gambar.',
            'foto.mimes' => 'Foto harus berformat JPG, PNG, atau WEBP.',
            'foto.max' => 'Ukuran foto maksimal 2 MB.',
            'jabatan.required' => 'Jabatan wajib diisi.',
            'tahun_pengalaman.required' => 'Lama pengalaman wajib diisi (isi 0 jika belum ada).',
            'tahun_pengalaman.integer' => 'Lama pengalaman harus berupa angka.',
            'tahun_pengalaman.max' => 'Lama pengalaman maksimal 60 tahun.',
        ]);

        DB::transaction(function () use ($r, $u, $v, $mentor, $kolom) {
            $u->update(['name' => $v['name'], 'email' => $v['email']]);
            if ($u->adalahAdmin()) return;

            $profil = $mentor ? ProfilMentor::firstOrCreate(['user_id' => $u->id]) : ProfilPengguna::firstOrCreate(['user_id' => $u->id]);
            $data = collect($v)->only($kolom)->all();

            if ($r->boolean('hapus_foto') || $r->hasFile('foto')) {
                if ($profil->foto) Storage::disk('public')->delete($profil->foto);
                $data['foto'] = $r->hasFile('foto') ? $r->file('foto')->store('foto-profil', 'public') : null;
            }
            $profil->update($data);

            if ($mentor) $this->simpanKeterampilan($u, $v);
        });

        return redirect()->route('profil.edit')->with('sukses', 'Profil berhasil diperbarui.');
    }

    public function ubahPassword(Request $r)
    {
        $r->validateWithBag('password', [
            'password_lama' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'password_lama.required' => 'Kata sandi saat ini wajib diisi.',
            'password_lama.current_password' => 'Kata sandi saat ini salah.',
            'password.required' => 'Kata sandi baru wajib diisi.',
            'password.min' => 'Kata sandi baru minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
        ]);

        $r->user()->update(['password' => $r->password]); // di-hash otomatis oleh cast 'hashed'

        return redirect()->route('profil.edit')->with('sukses', 'Kata sandi berhasil diganti.');
    }

    // Keterampilan yang dicentang + keterampilan baru (dipisah koma)
    private function simpanKeterampilan($u, array $v): void
    {
        $pilih = [];
        foreach ($v['skill'] ?? [] as $id) $pilih[(int) $id] = $v['level'][$id] ?? 'menengah';
        foreach (array_filter(array_map('trim', explode(',', $v['skill_baru'] ?? ''))) as $nama) {
            $id = Keterampilan::firstOrCreate(['nama' => mb_substr($nama, 0, 80)])->id;
            $pilih[$id] ??= 'menengah';
        }
        $u->keterampilan()->sync(collect($pilih)->map(fn ($level) => ['level' => $level])->all());
    }
}