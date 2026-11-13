<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ProfilPengguna extends Model
{
    protected $table = 'profil_pengguna';
    protected $fillable = ['user_id', 'pendidikan', 'minat', 'kota', 'tujuan_karier', 'bio', 'foto'];
}
