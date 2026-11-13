<?php
namespace App\Http\Controllers;

use App\Http\Requests\DaftarRequest;
use App\Models\{ProfilMentor, ProfilPengguna, User};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB};

class AuthController extends Controller
{
    public function formDaftar() { return view('auth.register'); }

    public function daftar(DaftarRequest $r)
    {
        $user = DB::transaction(function () use ($r) {
            $u = User::create(['name' => $r->name, 'email' => $r->email, 'password' => $r->password, 'peran' => $r->peran]);
            $r->peran === 'mentor'
                ? ProfilMentor::create(['user_id' => $u->id, 'status_verifikasi' => 'menunggu'])
                : ProfilPengguna::create(['user_id' => $u->id]);
            return $u;
        });
        Auth::login($user);
        $r->session()->regenerate();
        return redirect()->route($user->adalahMentor() ? 'dashboard' : 'dashboard');
    }

    public function formMasuk() { return view('auth.login'); }

    public function masuk(Request $r)
    {
        $data = $r->validate([
            'email' => ['required', 'email'], 'password' => ['required'], 'tab' => ['nullable', 'in:pengguna,mentor'],
        ], ['email.required' => 'Email wajib diisi.', 'email.email' => 'Format email tidak valid.', 'password.required' => 'Kata sandi wajib diisi.']);

        $user = User::where('email', $data['email'])->first();
        if (! $user || ! Auth::attempt(['email' => $data['email'], 'password' => $data['password']], $r->boolean('ingat'))) {
            return back()->withErrors(['email' => 'Email atau kata sandi salah.'])->onlyInput('email', 'tab');
        }
        if (! $user->aktif) {
            Auth::logout();
            return back()->withErrors(['email' => 'Akun kamu dinonaktifkan oleh administrator.'])->onlyInput('email', 'tab');
        }
        $tab = $data['tab'] ?? 'pengguna';
        if (! $user->adalahAdmin() && $user->peran !== $tab) {
            Auth::logout();
            return back()->withErrors(['email' => "Akun ini terdaftar sebagai {$user->peran}. Pilih tab \"" . ucfirst($user->peran) . "\" untuk masuk."])->onlyInput('email', 'tab');
        }
        $r->session()->regenerate();
        return redirect()->intended($user->adalahAdmin() ? route('admin.ringkasan') : route('dashboard'));
    }

    public function keluar(Request $r)
    {
        Auth::logout();
        $r->session()->invalidate();
        $r->session()->regenerateToken();
        return redirect()->route('beranda');
    }
}
