<?php
namespace App\Http\Controllers;

use App\Models\{EntriPortofolio, Keterampilan};
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\{DB, Storage};
use Illuminate\Validation\Rule;

/** Portofolio milik mentor yang sedang login (bisnis #10: hanya boleh mengubah miliknya). */
class PortofolioController extends Controller
{
    public const JENIS = ['proyek' => 'Proyek', 'pengalaman' => 'Pengalaman', 'sertifikat' => 'Sertifikat', 'studi_kasus' => 'Studi Kasus', 'lainnya' => 'Lainnya'];
    private const PER_HALAMAN = 5;

    public function index(Request $r)
    {
        $u = $r->user();
        $tab = in_array($r->query('tab'), ['terbit', 'draf'], true) ? $r->query('tab') : 'semua';
        $cari = trim((string) $r->query('q'));

        // Satu query agregat untuk kartu ringkasan dan jumlah di tab.
        $agg = $u->portofolio()->selectRaw("COUNT(*) AS semua, COALESCE(SUM(status = 'terbit'), 0) AS terbit, COALESCE(SUM(status = 'draf'), 0) AS draf")->first();
        $jumlah = ['semua' => (int) $agg->semua, 'terbit' => (int) $agg->terbit, 'draf' => (int) $agg->draf];

        $entri = $u->portofolio()->with('keterampilan')
            ->when($tab !== 'semua', fn ($q) => $q->where('status', $tab))
            ->when($cari !== '', fn ($q) => $q->where(fn ($w) => $w->where('judul', 'like', "%{$cari}%")
                ->orWhereHas('keterampilan', fn ($k) => $k->where('nama', 'like', "%{$cari}%"))))
            ->latest('updated_at')->paginate(self::PER_HALAMAN)->withQueryString();

        // Status kurasi: kenapa portofolio tampil / belum tampil di katalog.
        $status = $u->profilMentor?->status_verifikasi;
        $skill = $u->keterampilan()->count();
        [$kurasi, $tampil] = match (true) {
            $status === 'ditolak' => ['Verifikasi ditolak admin. Perbarui profilmu lalu hubungi admin.', false],
            $status !== 'terverifikasi' => ['Menunggu verifikasi admin untuk sinkronisasi katalog', false],
            $skill < 3 => ["Lengkapi minimal 3 keterampilan di Profil Saya ({$skill}/3)", false],
            $jumlah['terbit'] < 1 => ['Terbitkan minimal 1 entri agar tampil di katalog', false],
            default => ['Portofoliomu tampil di katalog publik', true],
        };

        return view('mentor.portofolio.index', compact('entri', 'tab', 'cari', 'jumlah', 'kurasi', 'tampil'));
    }

    public function show(Request $r, EntriPortofolio $portofolio)
    {
        $this->milik($r, $portofolio);
        return view('mentor.portofolio.show', ['entri' => $portofolio->load('keterampilan')]);
    }

    public function create()
    {
        return view('mentor.portofolio.form', [
            'entri' => new EntriPortofolio(['jenis' => 'proyek', 'status' => 'draf']),
            'dipilih' => [], 'semuaSkill' => Keterampilan::orderBy('nama')->get(),
        ]);
    }

    public function store(Request $r)
    {
        $data = $this->validasi($r);
        DB::transaction(function () use ($r, $data) {
            $entri = new EntriPortofolio(Arr::except($data, ['skill', 'gambar']));
            $entri->user_id = $r->user()->id;
            $entri->terbit_pada = $data['status'] === 'terbit' ? now() : null;
            if ($r->hasFile('gambar')) $entri->gambar = $r->file('gambar')->store('portofolio', 'public');
            $entri->save();
            $entri->keterampilan()->sync($data['skill'] ?? []);
        });
        return redirect()->route('mentor.portofolio.index')->with('sukses', 'Entri portofolio ditambahkan.');
    }

    public function edit(Request $r, EntriPortofolio $portofolio)
    {
        $this->milik($r, $portofolio);
        return view('mentor.portofolio.form', [
            'entri' => $portofolio, 'dipilih' => $portofolio->keterampilan()->pluck('keterampilan.id')->all(),
            'semuaSkill' => Keterampilan::orderBy('nama')->get(),
        ]);
    }

    public function update(Request $r, EntriPortofolio $portofolio)
    {
        $this->milik($r, $portofolio);
        $data = $this->validasi($r);
        DB::transaction(function () use ($r, $portofolio, $data) {
            $portofolio->fill(Arr::except($data, ['skill', 'gambar']));
            $portofolio->terbit_pada = $data['status'] === 'terbit' ? ($portofolio->terbit_pada ?? now()) : null;
            if ($r->hasFile('gambar')) {
                if ($portofolio->gambar) Storage::disk('public')->delete($portofolio->gambar);
                $portofolio->gambar = $r->file('gambar')->store('portofolio', 'public');
            }
            $portofolio->save();
            $portofolio->keterampilan()->sync($data['skill'] ?? []);
        });
        return redirect()->route('mentor.portofolio.index')->with('sukses', 'Entri portofolio diperbarui.');
    }

    public function destroy(Request $r, EntriPortofolio $portofolio)
    {
        $this->milik($r, $portofolio);
        DB::transaction(function () use ($portofolio) {
            if ($portofolio->gambar) Storage::disk('public')->delete($portofolio->gambar);
            $portofolio->delete(); // entri_keterampilan ikut terhapus (cascadeOnDelete)
        });
        return redirect()->route('mentor.portofolio.index')->with('sukses', 'Entri portofolio dihapus.');
    }

    private function milik(Request $r, EntriPortofolio $e): void
    {
        abort_unless($e->user_id === $r->user()->id, 403);
    }

    private function validasi(Request $r): array
    {
        return $r->validate([
            'judul' => ['required', 'string', 'max:160'],
            'jenis' => ['required', Rule::in(array_keys(self::JENIS))],
            'tautan' => ['nullable', 'url', 'max:255'],
            'tanggal_mulai' => ['nullable', 'date'],
            'tanggal_selesai' => ['nullable', 'date', Rule::when($r->filled('tanggal_mulai'), ['after_or_equal:tanggal_mulai'])],
            'deskripsi' => ['nullable', 'string', 'max:2000'],
            'gambar' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'status' => ['required', 'in:terbit,draf'],
            'skill' => ['nullable', 'array'],
            'skill.*' => ['exists:keterampilan,id'],
        ], [
            'judul.required' => 'Judul wajib diisi.', 'judul.max' => 'Judul maksimal 160 karakter.',
            'jenis.required' => 'Pilih jenis entri.', 'jenis.in' => 'Jenis entri tidak valid.',
            'tautan.url' => 'Tautan harus berupa URL lengkap, mis. https://contoh.com.',
            'tanggal_mulai.date' => 'Tanggal mulai tidak valid.',
            'tanggal_selesai.date' => 'Tanggal selesai tidak valid.',
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai tidak boleh sebelum tanggal mulai.',
            'deskripsi.max' => 'Deskripsi maksimal 2000 karakter.',
            'gambar.image' => 'Berkas harus berupa gambar.', 'gambar.mimes' => 'Gambar harus berformat JPG atau PNG.', 'gambar.max' => 'Ukuran gambar maksimal 2 MB.',
            'status.required' => 'Pilih status entri.', 'status.in' => 'Status tidak valid.',
            'skill.*.exists' => 'Keterampilan yang dipilih tidak ditemukan.',
        ]);
    }
}
