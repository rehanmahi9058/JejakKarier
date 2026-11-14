<?php
namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\SesiMentoring;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Halaman "Mentoring" milik mentor: sesi 1-on-1 yang masuk, tampil per bulan (kalender + tabel).
 * Aksi (konfirmasi, tolak, batalkan, selesai) tetap memakai SesiController@ubahStatus (route sesi.aksi).
 */
class SesiMentorController extends Controller
{
    private const BULAN = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    private const PER_HALAMAN = 6;

    public function index(Request $r)
    {
        $u = $r->user();
        $tab = in_array($r->query('tab'), ['diajukan', 'dikonfirmasi', 'selesai', 'riwayat'], true) ? $r->query('tab') : 'semua';
        $cari = trim((string) $r->query('q'));

        $tanggal = $this->tanggal($r->query('tanggal'));
        $bulan = $this->bulan($r->query('bulan'), $tanggal);
        $awal = $bulan->copy()->startOfMonth();
        $akhir = $bulan->copy()->endOfMonth();

        // Seluruh sesi pada bulan terpilih: dasar statistik, kalender, dan jumlah tab.
        $bulanan = $u->sesiMasuk()->with('pengguna')
            ->whereBetween('tanggal', [$awal->toDateString(), $akhir->toDateString()])
            ->orderBy('tanggal')->orderBy('jam_mulai')->get();
        $n = fn (string ...$s) => $bulanan->whereIn('status', $s)->count();
        $jumlah = [
            'semua' => $bulanan->count(),
            'diajukan' => $n('diajukan'),
            'dikonfirmasi' => $n('dikonfirmasi'),
            'selesai' => $n('selesai'),
            'riwayat' => $n('ditolak', 'dibatalkan'),
        ];
        $mentee = $bulanan->where('status', 'dikonfirmasi')->map(fn ($s) => $s->pengguna?->name)->filter()->unique()->take(2)
            ->map(fn ($nama) => preg_replace('/^(\S+)\s+(\S).*$/u', '$1 $2.', $nama))->implode(', ');

        // Tabel daftar sesi (ikut tab, pencarian, dan filter tanggal). Yang butuh respons tampil paling atas.
        $sesi = $u->sesiMasuk()->with('pengguna.profilPengguna')
            ->when($tanggal, fn ($q) => $q->whereDate('tanggal', $tanggal->toDateString()),
                fn ($q) => $q->whereBetween('tanggal', [$awal->toDateString(), $akhir->toDateString()]))
            ->when($tab === 'riwayat', fn ($q) => $q->whereIn('status', ['ditolak', 'dibatalkan']))
            ->when(in_array($tab, ['diajukan', 'dikonfirmasi', 'selesai'], true), fn ($q) => $q->where('status', $tab))
            ->when($cari !== '', function ($q) use ($cari) {
                $q->where(function ($w) use ($cari) {
                    $w->whereHas('pengguna', fn ($p) => $p->where('name', 'like', "%{$cari}%"))
                        ->orWhere('tujuan', 'like', "%{$cari}%");
                    if (preg_match('/(\d+)/', $cari, $m)) $w->orWhere('id', (int) $m[1]);
                });
            })
            ->orderByRaw("FIELD(status, 'diajukan', 'dikonfirmasi', 'selesai', 'ditolak', 'dibatalkan')")
            ->orderBy('tanggal')->orderBy('jam_mulai')
            ->paginate(self::PER_HALAMAN)->withQueryString();

        // Kalender bulanan (minggu dimulai Minggu).
        $perHari = $bulanan->groupBy(fn ($s) => $s->tanggal->toDateString());
        $sel = [];
        for ($i = 0; $i < $awal->dayOfWeek; $i++) $sel[] = null;
        for ($d = 1; $d <= $bulan->daysInMonth; $d++) {
            $tgl = $awal->copy()->day($d)->toDateString();
            $sel[] = ['tgl' => $tgl, 'hari' => $d, 'sesi' => $perHari->get($tgl, collect())];
        }

        // Permintaan menunggu di bulan lain: jangan sampai terlewat.
        $luar = $u->sesiMasuk()->where('status', 'diajukan')->whereNotBetween('tanggal', [$awal->toDateString(), $akhir->toDateString()]);
        $jumlahLuar = (clone $luar)->count();
        $bulanLuar = $jumlahLuar > 0 ? Carbon::parse((clone $luar)->orderBy('tanggal')->value('tanggal'))->format('Y-m') : null;

        return view('mentor.sesi.index', [
            'u' => $u,
            'profil' => $u->profilMentor()->with('bidang')->first(),
            'totalSelesai' => $u->sesiMasuk()->where('status', 'selesai')->count(),
            'jumlah' => $jumlah,
            'mentee' => $mentee,
            'sesi' => $sesi,
            'sel' => $sel,
            'bulan' => $bulan,
            'bulanLabel' => self::BULAN[$bulan->month] . ' ' . $bulan->year,
            'bulanSebelum' => $bulan->copy()->subMonthNoOverflow()->format('Y-m'),
            'bulanSesudah' => $bulan->copy()->addMonthNoOverflow()->format('Y-m'),
            'opsiBulan' => $this->opsiBulan($bulan),
            'tanggal' => $tanggal,
            'tab' => $tab,
            'cari' => $cari,
            'jumlahLuar' => $jumlahLuar,
            'bulanLuar' => $bulanLuar,
        ]);
    }

    /** Catatan mentor di Ringkasan: simpan sementara, atau simpan lalu tandai sesi selesai. */
    public function simpanCatatan(Request $r, SesiMentoring $sesi)
    {
        abort_unless($r->user()->id === $sesi->mentor_id, 403);
        $d = $r->validate([
            'umpan_balik' => ['nullable', 'string', 'max:5000'],
            'checklist_json' => ['nullable', 'string', 'max:6000'],
            'action_json' => ['nullable', 'string', 'max:6000'],
            'aksi' => ['required', 'in:simpan,selesai'],
        ]);

        $bersih = function (?string $json, array $kunci): array {
            $arr = json_decode($json ?? '[]', true);
            if (! is_array($arr)) return [];
            $hasil = [];
            foreach (array_slice($arr, 0, 12) as $i) {
                if (! is_array($i)) continue;
                $teks = trim((string) ($i['teks'] ?? ''));
                if ($teks === '') continue;
                $baris = ['teks' => mb_substr($teks, 0, 200)];
                foreach ($kunci as $k => $tipe) {
                    $baris[$k] = $tipe === 'bool' ? (bool) ($i[$k] ?? false) : mb_substr(trim((string) ($i[$k] ?? '')), 0, 40);
                }
                $hasil[] = $baris;
            }
            return $hasil;
        };

        DB::transaction(function () use ($sesi, $d, $bersih) {
            $s = SesiMentoring::whereKey($sesi->id)->lockForUpdate()->firstOrFail();
            if (! in_array($s->status, ['dikonfirmasi', 'selesai'], true)) {
                throw ValidationException::withMessages(['catatan' => 'Catatan hanya bisa diisi untuk sesi yang sudah dikonfirmasi.']);
            }
            if ($d['aksi'] === 'selesai' && $s->status !== 'dikonfirmasi') {
                throw ValidationException::withMessages(['catatan' => 'Sesi ini sudah ditandai selesai.']);
            }
            $s->fill([
                'umpan_balik' => filled($d['umpan_balik'] ?? null) ? trim($d['umpan_balik']) : null,
                'checklist' => $bersih($d['checklist_json'] ?? null, ['selesai' => 'bool']),
                'action_items' => $bersih($d['action_json'] ?? null, ['label' => 'str']),
            ]);
            if ($d['aksi'] === 'selesai') $s->status = 'selesai';
            $s->save();
        });

        return $d['aksi'] === 'selesai'
            ? redirect()->route('dashboard')->with('sukses', 'Sesi ditandai selesai dan catatan tersimpan.')
            : redirect()->to(route('dashboard', ['sesi' => $sesi->id]).'#ruang')->with('sukses', 'Catatan tersimpan.');
    }

    private function tanggal(?string $v): ?Carbon
    {
        try { return $v ? Carbon::createFromFormat('!Y-m-d', $v) : null; } catch (\Throwable) { return null; }
    }

    private function bulan(?string $v, ?Carbon $tanggal): Carbon
    {
        try { if ($v) return Carbon::createFromFormat('!Y-m', $v)->startOfMonth(); } catch (\Throwable) {}
        return ($tanggal ?? now())->copy()->startOfMonth();
    }

    /** Pilihan bulan: 3 bulan ke belakang sampai 8 bulan ke depan dari hari ini, plus bulan terpilih. */
    private function opsiBulan(Carbon $terpilih): array
    {
        $o = [];
        $m = now()->startOfMonth()->subMonths(3);
        for ($i = 0; $i < 12; $i++, $m->addMonthNoOverflow()) $o[$m->format('Y-m')] = self::BULAN[$m->month] . ' ' . $m->year;
        $o[$terpilih->format('Y-m')] ??= self::BULAN[$terpilih->month] . ' ' . $terpilih->year;
        ksort($o);
        return $o;
    }
}
