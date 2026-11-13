<?php
namespace App\Http\Controllers;

use App\Models\{BidangKarier, SesiMentoring, User};
use Illuminate\Http\Request;

class MentoringController extends Controller
{
    public function index(Request $r)
    {
        $u = $r->user();
        abort_unless($u->peran === 'pengguna', 403, 'Halaman ini untuk pengguna.');

        $mentor = User::layakKatalog()
            ->with(['profilMentor.bidang', 'keterampilan'])
            ->withCount(['slot as slot_tersedia_count' => fn ($s) => $s->where('status', 'tersedia')->whereDate('tanggal', '>=', today())])
            ->withCount(['sesiMasuk as sesi_selesai_count' => fn ($s) => $s->where('status', 'selesai')])
            ->when(trim((string) $r->query('q')), function ($q, $cari) {
                $q->where(fn ($w) => $w->where('name', 'like', "%{$cari}%")
                    ->orWhereHas('profilMentor', fn ($p) => $p->where('jabatan', 'like', "%{$cari}%"))
                    ->orWhereHas('keterampilan', fn ($k) => $k->where('nama', 'like', "%{$cari}%")));
            })
            ->when($r->query('bidang'), fn ($q, $b) => $q->whereHas('profilMentor', fn ($p) => $p->where('bidang_karier_id', $b)))
            ->orderByDesc('sesi_selesai_count')->orderBy('name')
            ->get();

        $sesi = $u->sesiSaya()->with('mentor')->orderByDesc('tanggal')->orderByDesc('jam_mulai')->get();
        $aktif = $sesi->whereIn('status', SesiMentoring::AKTIF)->count();

        return view('mentoring.index', ['mentor' => $mentor, 'bidang' => BidangKarier::orderBy('nama')->get(), 'sesi' => $sesi, 'aktif' => $aktif]);
    }
}
