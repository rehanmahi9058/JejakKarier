<?php
namespace App\Http\Controllers;

use App\Models\{LangkahKarier, User};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PetaKarierController extends Controller
{
    private function pengguna(Request $r): User
    {
        abort_unless($r->user()->peran === 'pengguna', 403, 'Peta karier hanya untuk pengguna.');
        return $r->user();
    }

    /** Bisnis #10: langkah hanya boleh diubah pemiliknya. */
    private function milik(Request $r, LangkahKarier $l): void
    {
        abort_unless($l->rencana->user_id === $r->user()->id, 403);
    }

    public function index(Request $r)
    {
        $u = $this->pengguna($r);
        $rencana = $u->rencanaKarier()->with(['langkah' => fn ($q) => $q->orderByRaw("FIELD(status,'proses','belum','selesai')")->orderBy('target_tanggal')])->latest()->first();
        $rekom = User::layakKatalog()->with('profilMentor')
            ->withCount(['slot as slot_tersedia_count' => fn ($s) => $s->where('status', 'tersedia')->whereDate('tanggal', '>=', today())])
            ->orderByDesc('slot_tersedia_count')->first();
        return view('peta-karier.index', [
            'rencana' => $rencana, 'progres' => $rencana?->progres() ?? 0, 'rekom' => $rekom,
            'selesai' => $rencana?->langkah->where('status', 'selesai')->count() ?? 0,
            'total' => $rencana?->langkah->count() ?? 0,
        ]);
    }

    public function simpanTujuan(Request $r)
    {
        $u = $this->pengguna($r);
        $data = $r->validate(['tujuan' => ['required', 'string', 'max:160'], 'target_tanggal' => ['nullable', 'date']],
            ['tujuan.required' => 'Tujuan karier wajib diisi.', 'tujuan.max' => 'Tujuan maksimal 160 karakter.', 'target_tanggal.date' => 'Tanggal target tidak valid.']);
        $rencana = $u->rencanaKarier()->latest()->first();
        $rencana ? $rencana->update($data) : $u->rencanaKarier()->create($data);
        return redirect()->route('peta-karier.index')->with('sukses', 'Tujuan karier disimpan.');
    }

    public function tambahLangkah(Request $r)
    {
        $u = $this->pengguna($r);
        $data = $r->validate(['judul' => ['required', 'string', 'max:200'], 'target_tanggal' => ['nullable', 'date']],
            ['judul.required' => 'Judul langkah wajib diisi.', 'judul.max' => 'Judul maksimal 200 karakter.', 'target_tanggal.date' => 'Tanggal target tidak valid.']);
        DB::transaction(function () use ($u, $data) {
            $rencana = $u->rencanaKarier()->latest()->lockForUpdate()->firstOrFail();
            $rencana->langkah()->create($data);
            $rencana->sinkronStatus(); // langkah baru (belum) -> tujuan kembali aktif
        });
        return back()->with('sukses', 'Langkah ditambahkan.');
    }

    public function ubahLangkah(Request $r, LangkahKarier $langkah)
    {
        $this->pengguna($r); $this->milik($r, $langkah);
        $data = $r->validate(['status' => ['required', 'in:belum,proses,selesai']], ['status.in' => 'Status tidak valid.']);
        DB::transaction(function () use ($langkah, $data) {
            $langkah->update($data);
            $langkah->rencana->sinkronStatus(); // bisnis #7: semua selesai -> tercapai
        });
        return back();
    }

    public function hapusLangkah(Request $r, LangkahKarier $langkah)
    {
        $this->pengguna($r); $this->milik($r, $langkah);
        DB::transaction(function () use ($langkah) {
            $rencana = $langkah->rencana;
            $langkah->delete();
            $rencana->sinkronStatus();
        });
        return back()->with('sukses', 'Langkah dihapus.');
    }
}
