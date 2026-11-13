<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\{BelongsToMany, HasMany, HasOne};
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;
    protected $fillable = ['name', 'email', 'password', 'peran', 'aktif'];
    protected $hidden = ['password', 'remember_token'];
    protected function casts(): array { return ['password' => 'hashed', 'aktif' => 'boolean', 'email_verified_at' => 'datetime']; }

    public function profilPengguna(): HasOne { return $this->hasOne(ProfilPengguna::class); }
    public function profilMentor(): HasOne { return $this->hasOne(ProfilMentor::class); }
    public function portofolio(): HasMany { return $this->hasMany(EntriPortofolio::class); }
    public function slot(): HasMany { return $this->hasMany(SlotMentoring::class, 'mentor_id'); }
    public function sesiSaya(): HasMany { return $this->hasMany(SesiMentoring::class, 'pengguna_id'); }
    public function sesiMasuk(): HasMany { return $this->hasMany(SesiMentoring::class, 'mentor_id'); }
    public function rencanaKarier(): HasMany { return $this->hasMany(RencanaKarier::class); }
    public function keterampilan(): BelongsToMany
    {
        return $this->belongsToMany(Keterampilan::class, 'users_keterampilan', 'users_id', 'keterampilan_id')->withPivot('level');
    }

        /** URL foto profil (disk 'public'); null kalau belum ada. Butuh `php artisan storage:link`. */
    public function fotoUrl(): ?string
    {
        $foto = $this->adalahMentor() ? $this->profilMentor?->foto : $this->profilPengguna?->foto;
        return $foto ? \Illuminate\Support\Facades\Storage::disk('public')->url($foto) : null;
    }

    public function adalahAdmin(): bool { return $this->peran === 'admin'; }
    public function adalahMentor(): bool { return $this->peran === 'mentor'; }
    public function mentorTerverifikasi(): bool
    {
        return $this->adalahMentor() && $this->profilMentor?->status_verifikasi === 'terverifikasi';
    }

    /** Aturan katalog (bisnis #1): semua dihitung di SQL, bukan di PHP. */
    public function scopeLayakKatalog(Builder $q): Builder
    {
        return $q->where('peran', 'mentor')->where('aktif', true)
            ->whereHas('profilMentor', fn ($p) => $p->where('status_verifikasi', 'terverifikasi'))
            ->whereHas('portofolio', fn ($p) => $p->where('status', 'terbit'))
            ->has('keterampilan', '>=', 3);
    }
}
