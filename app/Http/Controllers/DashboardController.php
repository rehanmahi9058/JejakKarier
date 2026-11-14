<?php
namespace App\Http\Controllers;

use App\Models\SesiMentoring;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $r)
    {
        $u = $r->user();
        if ($u->adalahAdmin()) return redirect()->route('admin.ringkasan');
        if ($u->adalahMentor()) return $this->mentor($u, $r);

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

    /** Dashboard mentor (Ringkasan): ruang sesi (live / berikutnya), catatan mentor, dan tabel riwayat sesi. */
    private function mentor(User $u, Request $r)
    {
        $profil = $u->profilMentor;
        $terbit = $u->portofolio()->where('status', 'terbit')->count();
        $jumlahKeterampilan = $u->keterampilan()->count();

        // Jam sesi disimpan sebagai waktu WIB; config app masih UTC, jadi zona dipaksa di sini.
        $wib = 'Asia/Jakarta';
        $sekarang = now($wib);
        $waktu = fn ($s, $jam) => Carbon::parse($s->tanggal->toDateString().' '.$jam, $wib);

        // Sesi yang ditampilkan di "ruang": pilihan manual (?sesi=), lalu yang sedang live, lalu yang berikutnya,
        // lalu sesi lampau yang belum ditandai selesai.
        $fokus = null;
        if ($id = (int) $r->query('sesi')) {
            $fokus = $u->sesiMasuk()->whereIn('status', ['dikonfirmasi', 'selesai'])->find($id);
        }
        if (! $fokus) {
            $kandidat = $u->sesiMasuk()->where('status', 'dikonfirmasi')->whereDate('tanggal', '>=', $sekarang->toDateString())
                ->orderBy('tanggal')->orderBy('jam_mulai')->limit(20)->get();
            $fokus = $kandidat->first(fn ($s) => $waktu($s, $s->jam_mulai)->lte($sekarang) && $waktu($s, $s->jam_selesai)->gte($sekarang))
                ?? $kandidat->first(fn ($s) => $waktu($s, $s->jam_selesai)->gt($sekarang))
                ?? $u->sesiMasuk()->where('status', 'dikonfirmasi')->orderByDesc('tanggal')->orderByDesc('jam_mulai')->first();
        }

        $ruang = null;
        if ($fokus) {
            $fokus->load('pengguna.profilPengguna');
            $ruang = [
                'mulaiMs' => $waktu($fokus, $fokus->jam_mulai)->getTimestamp() * 1000,
                'selesaiMs' => $waktu($fokus, $fokus->jam_selesai)->getTimestamp() * 1000,
                'sekarangMs' => $sekarang->getTimestamp() * 1000,
                'durasi' => $waktu($fokus, $fokus->jam_mulai)->diffInMinutes($waktu($fokus, $fokus->jam_selesai)),
                'riwayat' => $u->sesiMasuk()->where('pengguna_id', $fokus->pengguna_id)->where('status', 'selesai')->count(),
            ];
        }

        // Tabel riwayat: jumlah per status dalam satu query, daftar mengikuti tab.
        $hitung = $u->sesiMasuk()->selectRaw('status, COUNT(*) AS n')->groupBy('status')->pluck('n', 'status');
        $jumlah = [
            'semua' => (int) $hitung->sum(),
            'dikonfirmasi' => (int) ($hitung['dikonfirmasi'] ?? 0),
            'diajukan' => (int) ($hitung['diajukan'] ?? 0),
            'selesai' => (int) ($hitung['selesai'] ?? 0),
            'batal' => (int) (($hitung['ditolak'] ?? 0) + ($hitung['dibatalkan'] ?? 0)),
        ];
        $tab = in_array($r->query('tab'), ['dikonfirmasi', 'diajukan', 'selesai', 'batal'], true) ? $r->query('tab') : 'semua';

        $sesi = $u->sesiMasuk()->with('pengguna.profilPengguna')
            ->when($tab === 'batal', fn ($q) => $q->whereIn('status', ['ditolak', 'dibatalkan']))
            ->when(in_array($tab, ['dikonfirmasi', 'diajukan', 'selesai'], true), fn ($q) => $q->where('status', $tab))
            ->orderByRaw("FIELD(status, 'dikonfirmasi', 'diajukan', 'selesai', 'ditolak', 'dibatalkan')")
            ->orderByRaw("CASE WHEN status IN ('dikonfirmasi', 'diajukan') THEN tanggal END ASC")
            ->orderByRaw("CASE WHEN status NOT IN ('dikonfirmasi', 'diajukan') THEN tanggal END DESC")
            ->orderBy('jam_mulai')
            ->paginate(8)->withQueryString()->fragment('riwayat');

        return view('dashboard.mentor', compact('profil', 'terbit', 'jumlahKeterampilan', 'fokus', 'ruang', 'sekarang', 'jumlah', 'tab', 'sesi'));
    }
}
