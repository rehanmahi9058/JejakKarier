<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class ProfilMentor extends Model
{
    protected $table = 'profil_mentor';
    protected $fillable = ['user_id','bidang_karier_id','jabatan','pengalaman_kerja','tahun_pengalaman','topik_mentoring','foto','status_verifikasi','catatan_verifikasi','diverifikasi_pada'];
    protected $casts = ['diverifikasi_pada' => 'datetime'];
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function bidang(): BelongsTo { return $this->belongsTo(BidangKarier::class, 'bidang_karier_id'); }
}
