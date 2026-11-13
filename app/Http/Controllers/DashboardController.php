<?php
namespace App\Http\Controllers;

use App\Models\SesiMentoring;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $r)
    {
        $u = $r->user();
        if ($u->adalahAdmin()) return redirect()->route('admin.ringkasan');
        if ($u->adalahMentor()) return view('dashboard.index'); // placeholder, dibuat di tahap mentor

        $p = $u->profilPengguna;
        $terisi = collect([$p?->minat, $p?->kota, $p?->bio])->filter(fn ($v) => filled($v))->count();
        $lengkap = (int) round($terisi / 3 * 100);

        $sesiTerdekat = $u->sesiSaya()->with('mentor')->where('status', 'dikonfirmasi')
            ->whereDate('tanggal', '>=', today())->orderBy('tanggal')->orderBy('jam_mulai')->first();

        $rencana = $u->rencanaKarier()->with(['langkah' => fn ($q) => $q->orderBy('target_tanggal')])->latest()->first();
        $progres = $rencana?->progres() ?? 0;
        $langkahBerikut = $rencana?->langkah->firstWhere('status', '!=', 'selesai');

        $aktif = $u->sesiSaya()->whereIn('status', SesiMentoring::AKTIF)->count();
        $selesai = $u->sesiSaya()->where('status', 'selesai')->count();

        return view('dashboard.pengguna', compact('lengkap', 'sesiTerdekat', 'rencana', 'progres', 'langkahBerikut', 'aktif', 'selesai'));
    }
}
