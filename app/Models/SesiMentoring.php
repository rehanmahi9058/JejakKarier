<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class SesiMentoring extends Model
{
    protected $table = 'sesi_mentoring';
    public const AKTIF = ['diajukan', 'dikonfirmasi'];
    protected $fillable = ['slot_mentoring_id','pengguna_id','mentor_id','tanggal','jam_mulai','jam_selesai','tujuan','status','catatan','tautan_pertemuan'];
    protected $casts = ['tanggal' => 'date'];
    public function slot(): BelongsTo { return $this->belongsTo(SlotMentoring::class, 'slot_mentoring_id'); }
    public function pengguna(): BelongsTo { return $this->belongsTo(User::class, 'pengguna_id'); }
    public function mentor(): BelongsTo { return $this->belongsTo(User::class, 'mentor_id'); }

    /** Bisnis #4: tautan hanya untuk mentor pemilik & pemesan, setelah dikonfirmasi. */
    public function tautanTerlihatOleh(User $u): bool
    {
        return in_array($this->status, ['dikonfirmasi', 'selesai'], true)
            && in_array($u->id, [$this->pengguna_id, $this->mentor_id], true);
    }
}
