<?php
namespace App\Http\Controllers;

use App\Models\{ProfilMentor, User};
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Halaman administrator: ringkasan, profil admin, verifikasi mentor, dan kelola status akun. */
class AdminController extends Controller
{
    public function ringkasan()
    {
        $menunggu = ProfilMentor::where('status_verifikasi', 'menunggu')->count();
        $terverifikasi = ProfilMentor::where('status_verifikasi', 'terverifikasi')->count();
        $aktif = User::where('aktif', true)->count();
        $tinjau = ProfilMentor::with('user')->where('status_verifikasi', 'menunggu')->whereHas('user')->orderBy('created_at')->limit(5)->get();

        return view('admin.ringkasan', compact('menunggu', 'terverifikasi', 'aktif', 'tinjau'));
    }

    public function profil(Request $r)
    {
        return view('admin.profil', ['u' => $r->user()]);
    }

    public function ubahProfil(Request $r)
    {
        $u = $r->user();
        $d = $r->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:190', Rule::unique('users', 'email')->ignore($u->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'password_lama' => ['required_with:password', 'nullable', 'current_password'],
        ], [
            'name.required' => 'Nama wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email ini sudah dipakai akun lain.',
            'password.min' => 'Kata sandi baru minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
            'password_lama.required_with' => 'Isi kata sandi saat ini untuk mengganti kata sandi.',
            'password_lama.current_password' => 'Kata sandi saat ini salah.',
        ]);

        $u->name = $d['name'];
        $u->email = $d['email'];
        if (filled($d['password'] ?? null)) $u->password = $d['password']; // di-hash oleh cast 'hashed'
        $u->save();

        return redirect()->route('admin.profil')->with('sukses', 'Profil administrator berhasil diperbarui.');
    }

    public function akun(Request $r)
    {
        $tab = $r->query('tab') === 'semua' ? 'semua' : 'verifikasi';
        $cari = trim((string) $r->query('q'));

        $antrian = ProfilMentor::with('user')->where('status_verifikasi', 'menunggu')->whereHas('user')->orderBy('created_at')->get();

        $akun = User::with('profilMentor')->where('peran', '!=', 'admin')
            ->when($cari !== '', fn ($q) => $q->where(fn ($w) => $w->where('name', 'like', "%{$cari}%")->orWhere('email', 'like', "%{$cari}%")))
            ->orderBy('name')->paginate(10)->withQueryString();

        return view('admin.akun', compact('tab', 'cari', 'antrian', 'akun'));
    }

    /** Verifikasi atau tolak mentor. Penolakan wajib disertai alasan. */
    public function verifikasi(Request $r, ProfilMentor $profil)
    {
        $d = $r->validate([
            'aksi' => ['required', 'in:verifikasi,tolak'],
            'catatan' => ['nullable', 'string', 'max:500', Rule::requiredIf($r->input('aksi') === 'tolak')],
        ], ['catatan.required' => 'Catatan verifikasi wajib diisi saat menolak mentor.']);

        abort_unless($profil->user?->adalahMentor(), 404);
        if ($profil->status_verifikasi !== 'menunggu') {
            return back()->withErrors(['verifikasi' => 'Mentor ini sudah diproses sebelumnya.']);
        }

        $ok = $d['aksi'] === 'verifikasi';
        $profil->update([
            'status_verifikasi' => $ok ? 'terverifikasi' : 'ditolak',
            'catatan_verifikasi' => filled($d['catatan'] ?? null) ? trim($d['catatan']) : null,
            'diverifikasi_pada' => now(),
        ]);

        return back()->with('sukses', $profil->user->name.($ok ? ' berhasil diverifikasi.' : ' ditolak.'));
    }

    /** Aktifkan / nonaktifkan akun. Akun nonaktif tidak bisa masuk dan disembunyikan dari katalog. */
    public function ubahStatus(Request $r, User $user)
    {
        abort_if($user->adalahAdmin() || $user->id === $r->user()->id, 403, 'Akun administrator tidak dapat diubah dari sini.');
        $user->update(['aktif' => ! $user->aktif]);

        return back()->with('sukses', $user->name.($user->aktif ? ' diaktifkan.' : ' dinonaktifkan.'));
    }
}
