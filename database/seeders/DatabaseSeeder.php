<?php
namespace Database\Seeders;

use App\Models\{BidangKarier, EntriPortofolio, Keterampilan, LangkahKarier, ProfilMentor, ProfilPengguna, RencanaKarier, SlotMentoring, User};
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $bidang = collect(['Teknologi & Software', 'Desain Kreatif', 'Sains Data & AI', 'Digital Marketing', 'Manajemen Bisnis'])
                ->mapWithKeys(fn ($n) => [$n => BidangKarier::firstOrCreate(['nama' => $n])->id]);

            $ket = collect(['Laravel', 'Figma', 'React', 'Python', 'SEO', 'Copywriting', 'SQL', 'REST API', 'Design System', 'Tableau', 'Docker'])
                ->mapWithKeys(fn ($n) => [$n => Keterampilan::firstOrCreate(['nama' => $n])->id]);

            User::create(['name' => 'Admin JejakKarier', 'email' => 'admin@jejakkarier.test', 'password' => 'password123', 'peran' => 'admin']);

            // Mentor lengkap (terverifikasi, >=3 keterampilan, >=1 portofolio terbit)
            $mentor = [
                ['Dian Maharani', 'dian@jejakkarier.test', 'Senior Product Designer', 'Desain Kreatif', ['Figma', 'Design System', 'Copywriting'], 'Redesain Aplikasi Dompet Digital'],
                ['Bima Ardiansyah', 'bima@jejakkarier.test', 'Backend Engineer', 'Teknologi & Software', ['Laravel', 'SQL', 'REST API', 'Docker'], 'Arsitektur API Pembayaran'],
                ['Aisyah Wardani', 'aisyah@jejakkarier.test', 'Data Analyst', 'Sains Data & AI', ['Python', 'SQL', 'Tableau'], 'Analisis Churn Pelanggan'],
            ];
            foreach ($mentor as [$nama, $email, $jabatan, $bd, $skills, $judul]) {
                $u = User::create(['name' => $nama, 'email' => $email, 'password' => 'password123', 'peran' => 'mentor']);
                ProfilMentor::create([
                    'user_id' => $u->id, 'bidang_karier_id' => $bidang[$bd], 'jabatan' => $jabatan, 'tahun_pengalaman' => 6,
                    'topik_mentoring' => 'Review portofolio, karier, wawancara', 'status_verifikasi' => 'terverifikasi', 'diverifikasi_pada' => now(),
                ]);
                foreach ($skills as $s) $u->keterampilan()->attach($ket[$s], ['level' => 'mahir']);
                $p = EntriPortofolio::create(['user_id' => $u->id, 'judul' => $judul, 'jenis' => 'proyek', 'status' => 'terbit',
                    'terbit_pada' => now(), 'deskripsi' => 'Studi kasus lengkap dari riset hingga hasil.', 'tanggal_mulai' => now()->subYear(), 'tanggal_selesai' => now()->subMonths(6)]);
                $p->keterampilan()->attach(collect($skills)->map(fn ($s) => $ket[$s])->all());
                foreach ([1, 2, 3] as $h) {
                    SlotMentoring::create(['mentor_id' => $u->id, 'tanggal' => now()->addDays($h)->toDateString(),
                        'jam_mulai' => '19:00', 'jam_selesai' => '19:45', 'tautan_pertemuan' => "https://meet.google.com/jk-{$u->id}-{$h}"]);
                }
            }

            // Mentor yang masih menunggu verifikasi (untuk uji admin)
            $m = User::create(['name' => 'Rina Kusuma', 'email' => 'rina@jejakkarier.test', 'password' => 'password123', 'peran' => 'mentor']);
            ProfilMentor::create(['user_id' => $m->id, 'jabatan' => 'Lead UX Designer', 'status_verifikasi' => 'menunggu']);

            // Pengguna contoh + peta karier
            $p = User::create(['name' => 'Nadia Putri', 'email' => 'nadia@jejakkarier.test', 'password' => 'password123', 'peran' => 'pengguna']);
            ProfilPengguna::create(['user_id' => $p->id, 'kota' => 'Malang', 'minat' => 'UI/UX']);
            $r = RencanaKarier::create(['user_id' => $p->id, 'tujuan' => 'Menjadi Product Designer', 'target_tanggal' => now()->addMonths(6)]);
            foreach ([['Pelajari riset pengguna', 'selesai'], ['Buat 2 studi kasus', 'proses'], ['Ikuti sesi ulasan portofolio', 'belum']] as [$j, $s]) {
                LangkahKarier::create(['rencana_karier_id' => $r->id, 'judul' => $j, 'status' => $s, 'target_tanggal' => now()->addMonths(2)]);
            }
        });
    }
}
