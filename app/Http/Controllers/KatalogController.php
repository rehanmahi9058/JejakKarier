<?php
namespace App\Http\Controllers;

use App\Models\{BidangKarier, EntriPortofolio, Keterampilan, SesiMentoring, User};
use Illuminate\Http\Request;

class KatalogController extends Controller
{
    public function beranda()
    {
        $stat = [
            'portofolio' => EntriPortofolio::where('status', 'terbit')->count(),
            'mentor' => User::layakKatalog()->count(),
        ];
        $terbaru = $this->kueri(new Request())->limit(3)->get();
        return view('beranda', compact('stat', 'terbaru'));
    }

    public function index(Request $r)
    {
        $mentor = $this->kueri($r)->paginate(6)->withQueryString();
        $stat = [
            'portofolio' => EntriPortofolio::where('status', 'terbit')->count(),
            'mentor' => User::layakKatalog()->count(),
            'sesi' => SesiMentoring::where('status', 'selesai')->count(),
        ];
        return view('katalog.index', [
            'mentor' => $mentor, 'stat' => $stat,
            'bidang' => BidangKarier::orderBy('nama')->get(),
            'chip' => Keterampilan::whereIn('nama', ['Laravel', 'Figma', 'React', 'Python', 'SEO', 'Copywriting', 'SQL'])->orderBy('nama')->get(),
        ]);
    }

    /** Semua filter/urut/hitung dikerjakan MySQL (whereHas + withCount + subquery), bukan PHP. */
    private function kueri(Request $r)
    {
        $q = User::layakKatalog()
            ->with(['profilMentor.bidang', 'keterampilan'])
            ->with(['portofolio' => fn ($p) => $p->where('status', 'terbit')->latest('terbit_pada')->with('keterampilan')])
            ->withCount(['portofolio as terbit_count' => fn ($p) => $p->where('status', 'terbit')])
            ->withCount(['sesiMasuk as sesi_selesai_count' => fn ($s) => $s->where('status', 'selesai')])
            ->addSelect(['terbit_terakhir' => EntriPortofolio::select('terbit_pada')
                ->whereColumn('user_id', 'users.id')->where('status', 'terbit')->latest('terbit_pada')->limit(1)]);

        if ($cari = trim((string) $r->query('q'))) {
            $q->where(function ($w) use ($cari) {
                $w->where('name', 'like', "%{$cari}%")
                  ->orWhereHas('portofolio', fn ($p) => $p->where('status', 'terbit')->where('judul', 'like', "%{$cari}%"))
                  ->orWhereHas('keterampilan', fn ($k) => $k->where('nama', 'like', "%{$cari}%"));
            });
        }
        if ($bd = $r->query('bidang')) {
            $q->whereHas('profilMentor', fn ($p) => $p->where('bidang_karier_id', $bd));
        }
        if ($ket = $r->query('ket')) {
            $q->whereHas('keterampilan', fn ($k) => $k->where('keterampilan.id', $ket));
        }
        return $q->orderByDesc('terbit_terakhir');
    }

    public function detail(User $mentor)
    {
        abort_unless(User::layakKatalog()->whereKey($mentor->id)->exists(), 404);
        $mentor->load(['profilMentor.bidang', 'keterampilan',
            'portofolio' => fn ($p) => $p->where('status', 'terbit')->latest('terbit_pada')->with('keterampilan')]);
        $slot = $mentor->slot()->where('status', 'tersedia')
            ->where(fn ($w) => $w->whereDate('tanggal', '>', today())->orWhere(fn ($x) => $x->whereDate('tanggal', today())->where('jam_mulai', '>', now()->format('H:i:s'))))
            ->orderBy('tanggal')->orderBy('jam_mulai')->get();
        return view('katalog.detail', compact('mentor', 'slot'));
    }
}
