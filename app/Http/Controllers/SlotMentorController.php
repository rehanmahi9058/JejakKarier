<?php
namespace App\Http\Controllers;

use App\Models\{SlotMentoring, User};
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/** Kelola jadwal & slot mentoring milik mentor yang sedang login. */
class SlotMentorController extends Controller
{
    private const BULAN = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    private const PER_HALAMAN = 6;

    public function index(Request $r)
    {
        $u = $r->user();

        $tanggal = $this->tanggal($r->query('tanggal'));
        $bulan = $this->bulan($r->query('bulan'), $tanggal);
        $awal = $bulan->copy()->startOfMonth();
        $akhir = $bulan->copy()->endOfMonth();

        // Seluruh slot pada bulan terpilih: dasar statistik, kalender, dan jumlah tab.
        $bulanan = $u->slot()->with('sesi.pengguna')
            ->whereBetween('tanggal', [$awal->toDateString(), $akhir->toDateString()])
            ->orderBy('tanggal')->orderBy('jam_mulai')->get();
        $hitung = fn (string $s) => $bulanan->where('status', $s)->count();
        $tersedia = $hitung('tersedia');
        $dipesan = $hitung('dipesan');
        $ditutup = $hitung('ditutup');
        $buka = $tersedia + $dipesan;

        $statistik = [
            'total' => $bulanan->count(),
            'tersedia' => $tersedia,
            'dipesan' => $dipesan,
            'ditutup' => $ditutup,
            'terisi' => $buka > 0 ? (int) round($dipesan / $buka * 100) : 0,
            'mentee' => $bulanan->where('status', 'dipesan')->map(fn ($s) => $s->sesi?->pengguna?->name)->filter()->unique()->take(2)
                ->map(fn ($n) => preg_replace('/^(\S+)\s+(\S).*$/u', '$1 $2.', $n))->implode(', '),
        ];

        // Tabel daftar slot (ikut filter tab, pencarian, tanggal).
        $status = $r->query('status');
        $cari = trim((string) $r->query('q'));
        $slot = $u->slot()->with('sesi.pengguna')
            ->when($tanggal, fn ($q) => $q->whereDate('tanggal', $tanggal->toDateString()),
                fn ($q) => $q->whereBetween('tanggal', [$awal->toDateString(), $akhir->toDateString()]))
            ->when(in_array($status, ['tersedia', 'dipesan', 'ditutup'], true), fn ($q) => $q->where('status', $status))
            ->when($cari !== '', function ($q) use ($cari) {
                $q->where(function ($w) use ($cari) {
                    $w->whereHas('sesi.pengguna', fn ($p) => $p->where('name', 'like', "%{$cari}%"))
                        ->orWhere('tautan_pertemuan', 'like', "%{$cari}%");
                    if (preg_match('/(\d+)/', $cari, $m)) $w->orWhere('id', (int) $m[1]);
                });
            })
            ->orderBy('tanggal')->orderBy('jam_mulai')
            ->paginate(self::PER_HALAMAN)->withQueryString();

        // Kalender bulanan (minggu dimulai Minggu).
        $perHari = $bulanan->groupBy(fn ($s) => $s->tanggal->toDateString());
        $sel = [];
        for ($i = 0; $i < $awal->dayOfWeek; $i++) $sel[] = null;
        for ($d = 1; $d <= $bulan->daysInMonth; $d++) {
            $tgl = $awal->copy()->day($d)->toDateString();
            $sel[] = ['tgl' => $tgl, 'hari' => $d, 'slot' => $perHari->get($tgl, collect())];
        }

        $semuaSlot = $u->slot()->get(['jam_mulai', 'jam_selesai']);
        $durasi = $semuaSlot->map(fn ($s) => Carbon::parse($s->jam_mulai)->diffInMinutes(Carbon::parse($s->jam_selesai)));

        return view('mentor.slot', [
            'u' => $u,
            'profil' => $u->profilMentor()->with('bidang')->first(),
            'layak' => User::layakKatalog()->whereKey($u->id)->exists(),
            'selesai' => $u->sesiMasuk()->where('status', 'selesai')->count(),
            'durasiMin' => $durasi->min(), 'durasiMaks' => $durasi->max(),
            'statistik' => $statistik,
            'slot' => $slot,
            'sel' => $sel,
            'bulan' => $bulan,
            'bulanLabel' => self::BULAN[$bulan->month] . ' ' . $bulan->year,
            'bulanSebelum' => $bulan->copy()->subMonthNoOverflow()->format('Y-m'),
            'bulanSesudah' => $bulan->copy()->addMonthNoOverflow()->format('Y-m'),
            'opsiBulan' => $this->opsiBulan($bulan),
            'tanggal' => $tanggal,
            'status' => $status,
            'cari' => $cari,
            'tab' => ['semua' => $bulanan->count(), 'tersedia' => $tersedia, 'dipesan' => $dipesan, 'ditutup' => $ditutup],
            'bisaDipilih' => $slot->getCollection()->where('status', 'tersedia')->pluck('id')->map(fn ($i) => (string) $i)->values(),
        ]);
    }

    /** Tambah slot baru. Aturan: tidak di masa lalu, jam selesai setelah jam mulai, tidak bertabrakan. */
    public function store(Request $r)
    {
        $d = $r->validateWithBag('tambahSlot', [
            'tanggal' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'jam_mulai' => ['required', 'date_format:H:i'],
            'jam_selesai' => ['required', 'date_format:H:i', 'after:jam_mulai'],
            'tautan_pertemuan' => ['nullable', 'url', 'max:255'],
        ], [
            'tanggal.required' => 'Tanggal wajib diisi.', 'tanggal.date_format' => 'Format tanggal tidak valid.',
            'tanggal.after_or_equal' => 'Tanggal tidak boleh di masa lalu.',
            'jam_mulai.required' => 'Jam mulai wajib diisi.', 'jam_mulai.date_format' => 'Format jam mulai tidak valid.',
            'jam_selesai.required' => 'Jam selesai wajib diisi.', 'jam_selesai.date_format' => 'Format jam selesai tidak valid.',
            'jam_selesai.after' => 'Jam selesai harus setelah jam mulai.',
            'tautan_pertemuan.url' => 'Tautan pertemuan harus berupa URL lengkap, contoh https://meet.google.com/abc.',
        ]);

        $uId = $r->user()->id;
        DB::transaction(function () use ($d, $uId) {
            User::whereKey($uId)->lockForUpdate()->first(); // serialkan penambahan slot oleh mentor yang sama
            if (Carbon::parse("{$d['tanggal']} {$d['jam_mulai']}")->isPast())
                throw ValidationException::withMessages(['jam_mulai' => 'Jam mulai sudah lewat.'])->errorBag('tambahSlot');
            if (SlotMentoring::bertabrakan($uId, $d['tanggal'], $d['jam_mulai'], $d['jam_selesai']))
                throw ValidationException::withMessages(['jam_mulai' => 'Rentang waktu ini bertabrakan dengan slot lain milikmu.'])->errorBag('tambahSlot');
            SlotMentoring::create([
                'mentor_id' => $uId, 'tanggal' => $d['tanggal'], 'jam_mulai' => $d['jam_mulai'], 'jam_selesai' => $d['jam_selesai'],
                'status' => 'tersedia', 'tautan_pertemuan' => $d['tautan_pertemuan'] ?? null,
            ]);
        });

        return redirect()->route('mentor.slot.index', ['bulan' => substr($d['tanggal'], 0, 7)])->with('sukses', 'Slot baru berhasil ditambahkan.');
    }

    public function tutup(Request $r, SlotMentoring $slot)
    {
        $this->ubah($r, $slot, 'tersedia', fn ($s) => $s->update(['status' => 'ditutup']),
            'Hanya slot berstatus tersedia yang bisa ditutup. Slot yang sudah dipesan harus dibatalkan lewat sesinya.');
        return back()->with('sukses', 'Slot ditutup.');
    }

    public function buka(Request $r, SlotMentoring $slot)
    {
        $this->ubah($r, $slot, 'ditutup', function ($s) {
            if ($s->tanggal->copy()->setTimeFromTimeString($s->jam_mulai)->isPast()) $this->gagal('Slot ini sudah lewat dan tidak bisa dibuka kembali.');
            $s->update(['status' => 'tersedia']);
        }, 'Hanya slot yang ditutup yang bisa dibuka kembali.');
        return back()->with('sukses', 'Slot dibuka kembali.');
    }

    public function hapus(Request $r, SlotMentoring $slot)
    {
        $this->ubah($r, $slot, ['tersedia', 'ditutup'], fn ($s) => $s->delete(),
            'Slot yang sudah dipesan tidak bisa dihapus. Batalkan sesinya terlebih dahulu.');
        return back()->with('sukses', 'Slot dihapus.');
    }

    /** Tutup beberapa slot sekaligus. Slot milik orang lain atau yang sudah dipesan dilewati. */
    public function tutupMassal(Request $r)
    {
        $d = $r->validate(['ids' => ['required', 'array', 'max:100'], 'ids.*' => ['integer']], ['ids.required' => 'Pilih minimal satu slot.']);
        $n = SlotMentoring::where('mentor_id', $r->user()->id)->whereIn('id', $d['ids'])->where('status', 'tersedia')->update(['status' => 'ditutup']);
        return back()->with('sukses', "{$n} slot ditutup.");
    }

    /** Pastikan pemilik, kunci baris, cek status yang diizinkan, lalu jalankan aksi. */
    private function ubah(Request $r, SlotMentoring $slot, string|array $statusBoleh, \Closure $aksi, string $pesanGagal): void
    {
        abort_unless($slot->mentor_id === $r->user()->id, 403);
        DB::transaction(function () use ($slot, $statusBoleh, $aksi, $pesanGagal) {
            $s = SlotMentoring::whereKey($slot->id)->lockForUpdate()->firstOrFail(); // cegah balapan dengan pemesanan
            if (! in_array($s->status, (array) $statusBoleh, true)) $this->gagal($pesanGagal);
            $aksi($s);
        });
    }

    private function gagal(string $pesan): never { throw ValidationException::withMessages(['slot' => $pesan]); }

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