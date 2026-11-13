<?php
namespace App\Http\Controllers;

use App\Models\{SesiMentoring, SlotMentoring, User};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SesiController extends Controller
{
    /** Bisnis #2, #9: pemesanan atomik. Hanya peran pengguna. */
    public function pesan(Request $r, SlotMentoring $slot)
    {
        $user = $r->user();
        abort_unless($user->peran === 'pengguna', 403, 'Hanya pengguna yang dapat memesan sesi.');
        $data = $r->validate(['tujuan' => ['required', 'string', 'max:500']],
            ['tujuan.required' => 'Tujuan sesi wajib diisi.', 'tujuan.max' => 'Tujuan sesi maksimal 500 karakter.']);

        DB::transaction(function () use ($user, $slot, $data) {
            // Kunci baris pengguna: mencegah dua tab memesan bersamaan melewati kuota.
            User::whereKey($user->id)->lockForUpdate()->first();
            // Kunci baris slot: dua pengguna tidak bisa memesan slot yang sama.
            $s = SlotMentoring::whereKey($slot->id)->lockForUpdate()->firstOrFail();

            if ($s->status !== 'tersedia') $this->gagal('Maaf, slot ini sudah dipesan atau ditutup.');
            if ($s->tanggal->copy()->setTimeFromTimeString($s->jam_mulai)->isPast()) $this->gagal('Slot ini sudah lewat.');
            if (! User::layakKatalog()->whereKey($s->mentor_id)->exists()) $this->gagal('Mentor ini belum tersedia untuk dipesan.');

            $aktif = SesiMentoring::where('pengguna_id', $user->id)->whereIn('status', SesiMentoring::AKTIF)->count();
            if ($aktif >= 2) $this->gagal('Kuota habis: kamu hanya boleh punya maksimal 2 sesi aktif.');

            SesiMentoring::create([
                'slot_mentoring_id' => $s->id, 'pengguna_id' => $user->id, 'mentor_id' => $s->mentor_id,
                'tanggal' => $s->tanggal, 'jam_mulai' => $s->jam_mulai, 'jam_selesai' => $s->jam_selesai,
                'tujuan' => $data['tujuan'], 'status' => 'diajukan', 'tautan_pertemuan' => $s->tautan_pertemuan,
            ]);
            $s->update(['status' => 'dipesan']);
        });

        return redirect()->route('mentoring.index')->with('sukses', 'Sesi berhasil diajukan. Menunggu konfirmasi mentor.');
    }

    /** Bisnis #3: batal/tolak melepas slot kembali ke tersedia. Aksi: konfirmasi, tolak, batalkan, selesai. */
    public function ubahStatus(Request $r, SesiMentoring $sesi, string $aksi)
    {
        $u = $r->user();
        $adalahMentor = $u->id === $sesi->mentor_id;
        $adalahPemesan = $u->id === $sesi->pengguna_id;
        abort_unless($adalahMentor || $adalahPemesan, 403);

        DB::transaction(function () use ($sesi, $aksi, $adalahMentor, $adalahPemesan, $r) {
            $sesi = SesiMentoring::whereKey($sesi->id)->lockForUpdate()->firstOrFail();
            $catatan = $r->input('catatan');

            match (true) {
                $aksi === 'konfirmasi' && $adalahMentor && $sesi->status === 'diajukan'
                    => $sesi->update(['status' => 'dikonfirmasi', 'catatan' => $catatan, 'tautan_pertemuan' => $r->input('tautan_pertemuan', $sesi->tautan_pertemuan)]),
                $aksi === 'selesai' && $adalahMentor && $sesi->status === 'dikonfirmasi'
                    => $sesi->update(['status' => 'selesai', 'catatan' => $catatan]),
                in_array($aksi, ['tolak', 'batalkan'], true) && in_array($sesi->status, SesiMentoring::AKTIF, true)
                    && (($aksi === 'tolak' && $adalahMentor && $sesi->status === 'diajukan') || ($aksi === 'batalkan' && ($adalahMentor || $adalahPemesan)))
                    => $this->lepasSlot($sesi, $aksi === 'tolak' ? 'ditolak' : 'dibatalkan', $catatan),
                default => $this->gagal('Aksi tidak diizinkan untuk status sesi ini.'),
            };
        });
        return back()->with('sukses', 'Status sesi diperbarui.');
    }

    private function lepasSlot(SesiMentoring $sesi, string $status, ?string $catatan): void
    {
        $slotId = $sesi->slot_mentoring_id;
        $sesi->update(['status' => $status, 'catatan' => $catatan, 'slot_mentoring_id' => null, 'tautan_pertemuan' => null]);
        if ($slotId) SlotMentoring::whereKey($slotId)->update(['status' => 'tersedia']);
    }

    private function gagal(string $pesan): never { throw ValidationException::withMessages(['slot' => $pesan]); }
}
