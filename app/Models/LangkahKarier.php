<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class LangkahKarier extends Model
{
    protected $table = 'langkah_karier';
    protected $fillable = ['rencana_karier_id', 'judul', 'target_tanggal', 'status'];
    protected $casts = ['target_tanggal' => 'date'];
    public function rencana(): BelongsTo { return $this->belongsTo(RencanaKarier::class, 'rencana_karier_id'); }
}
