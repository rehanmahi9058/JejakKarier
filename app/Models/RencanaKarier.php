<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
class RencanaKarier extends Model
{
    protected $table = 'rencana_karier';
    protected $fillable = ['user_id', 'tujuan', 'target_tanggal', 'status'];
    protected $casts = ['target_tanggal' => 'date'];
    public function langkah(): HasMany { return $this->hasMany(LangkahKarier::class); }

    public function progres(): int
    {
        $total = $this->langkah()->count();
        return $total ? (int) round($this->langkah()->where('status', 'selesai')->count() / $total * 100) : 0;
    }

    /** Bisnis #7: panggil setiap kali langkah ditambah/diubah/dihapus. */
    public function sinkronStatus(): void
    {
        $total = $this->langkah()->count();
        $selesai = $this->langkah()->where('status', 'selesai')->count();
        $this->update(['status' => ($total > 0 && $total === $selesai) ? 'tercapai' : 'aktif']);
    }
}
