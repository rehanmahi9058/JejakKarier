<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, BelongsToMany};
class EntriPortofolio extends Model
{
    protected $table = 'entri_portofolio';
    protected $fillable = ['user_id','judul','jenis','tautan','tanggal_mulai','tanggal_selesai','deskripsi','gambar','status','terbit_pada'];
    protected $casts = ['tanggal_mulai' => 'date', 'tanggal_selesai' => 'date', 'terbit_pada' => 'datetime'];
    public function mentor(): BelongsTo { return $this->belongsTo(User::class, 'user_id'); }
    public function keterampilan(): BelongsToMany
    {
        return $this->belongsToMany(Keterampilan::class, 'entri_keterampilan', 'entri_portofolio_id', 'keterampilan_id');
    }
}
