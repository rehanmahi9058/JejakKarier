<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasOne};
class SlotMentoring extends Model
{
    protected $table = 'slot_mentoring';
    protected $fillable = ['mentor_id','tanggal','jam_mulai','jam_selesai','status','tautan_pertemuan'];
    protected $casts = ['tanggal' => 'date'];
    public function mentor(): BelongsTo { return $this->belongsTo(User::class, 'mentor_id'); }
    public function sesi(): HasOne { return $this->hasOne(SesiMentoring::class); }

    /** Bisnis #6: apakah rentang waktu bertumpang tindih dengan slot lain mentor yang sama? */
    public static function bertabrakan(int $mentorId, string $tanggal, string $mulai, string $selesai, ?int $kecualiId = null): bool
    {
        return static::where('mentor_id', $mentorId)->whereDate('tanggal', $tanggal)
            ->where('jam_mulai', '<', $selesai)->where('jam_selesai', '>', $mulai)
            ->when($kecualiId, fn ($q) => $q->where('id', '!=', $kecualiId))->exists();
    }
}
