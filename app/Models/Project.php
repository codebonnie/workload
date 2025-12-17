<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use SoftDeletes;

    protected $table = 'projects';       
    protected $primaryKey = 'id_proyek';      

    protected $fillable = [
       'nama_proyek',
        'kategori',
        'catatan_disposisi',
        'deskripsi_proyek',
        'jenis_pengembangan',
        'triwulan',
        'tahun',
        'project_owner',
        'tanggal_mulai',
        'tanggal_selesai',
        'dokumen_izin_pengembangan',
        'dokumen_analisa_resiko',
        'dokumen_unit_testing',
        'dokumen_lainnya',
        'dokumen_review_source_code',
        'dokumen_pentest',
        'dokumen_brd',
        'dokumen_urf',
        'dokumen_kajian_biaya_manfaat',
        'dokumen_sit',
        'dokumen_uat',
        'dokumen_to',
        'dokumen_pir',
        'pic_1',
        'pic_2',
        'pic_3',
        'progress',
        'catatan_tindak_lanjut',
        'aktivitas_proyek',
        'keterangan',
        'catatan_tindak_lanjut',
        'kd_unit_kerja',
        'alamat_aplikasi',
        'server_app',
        'status',
        'platform',
        'bahasa_pemrograman',
        'framework',
        'version',
        'database',
        'backup_realtime',
        'cpu',
        'tipe_server',
        'os',
        'memory',
        'pengembang_aplikasi',
        'pusat_data',
        'penyelenggara_data',
        'drc',
        'penyelenggara_drc',
        'frekuensi',
        'tanggal_implementasi',
        'jenis_kepemilikan',
        'tingkat_kritikalitas',
        'skala_prioritas',
        'koneksi_dengan_pihak_luar',
        'github',
    ];

    // Relasi ke User (PIC 1, PIC 2, PIC 3)
    public function pic1()
    {
        return $this->belongsTo(\App\Models\User::class, 'pic_1');
    }
    public function pic2()
    {
        return $this->belongsTo(\App\Models\User::class, 'pic_2');
    }
    public function pic3()
    {
        return $this->belongsTo(\App\Models\User::class, 'pic_3');
    }
    public function unitKerja()
{
    return $this->belongsTo(UnitKerja::class, 'project_owner', 'kode');
}
    // Di migration file
public function up()
{
    Schema::table('projects', function (Blueprint $table) {
        $table->timestamp('dokumen_izin_pengembangan_upload_date')->nullable();
        $table->timestamp('dokumen_analisa_resiko')->nullable();
        $table->timestamp('dokumen_unit_testing')->nullable();
        $table->timestamp('dokumen_lainnya')->nullable();
        $table->timestamp('dokumen_review_source_code')->nullable();
        $table->timestamp('dokumen_pentest')->nullable();
        $table->timestamp('dokumen_brd_upload_date')->nullable();
        $table->timestamp('dokumen_urf_upload_date')->nullable();
        $table->timestamp('dokumen_kajian_biaya_manfaat_upload_date')->nullable();
        $table->timestamp('dokumen_sit_upload_date')->nullable();
        $table->timestamp('dokumen_uat_upload_date')->nullable();
        $table->timestamp('dokumen_to_upload_date')->nullable();
        $table->timestamp('dokumen_pir_upload_date')->nullable();
    });
}

}
