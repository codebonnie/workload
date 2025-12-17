<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTableProjects extends Migration
{
    public function up()
    {
    $this->forge->addField([
            'id_proyek' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'nama_proyek' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'catatan_disposisi' => [
            'type' => 'VARCHAR',
            'constraint' => 255,
            
            ],
            'deskripsi_projek' => [
                'type' => 'TEXT',
            ],
            'jenis_pengembangan' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'project_owner' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'tanggal_mulai' => [
                'type' => 'DATE',
            ],
            'tanggal_selesai' => [
                'type' => 'DATE',
            ],
            'dokumen_brd' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'dokumen_urf' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'dokumen_kajian_biaya_manfaat' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'dokumen_sit' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'dokumen_uat' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'dokumen_to' => [
                'type' => 'VARCHAR',
                'constraint'=> 255,
            ],
            'dokumen_pir' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'pic_1' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'pic_2' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'pic_3' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'progress' => [
                'type' => 'ENUM',
                'constraint' => ['belum terlaksana', 'on progress', 'selesai', 'hold', 'pending', 'drop'],
                'default' => 'belum terlaksana',
            ],
            'aktivitas_proyek' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'keterangan' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'kd_unit_kerja' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
            ],
            'alamat_aplikasi' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'server_app' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'status' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'platform' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'bahasa_pemrograman' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'framework' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'version' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
            ],
            'database' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'backup_realtime' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'cpu' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'tipe_server' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'os' => [
                'type' => 'VARCHAR',
                'constraint' => 150,
            ],
            'memory' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
            ],
            'pengembang_aplikasi' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
            ],
            'pusat_data' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
            ],
            'penyelenggara_data' => [
                'type'=> 'VARCHAR',
                'constraint' => 100,
            ],
            'drc' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'penyelenggara_drc' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'frekuensi' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'tanggal_implementasi' => [
                'type' => 'DATE',
            ],
            'jenis_kepemilikan' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'tingkat_kritikalitas' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
            ],
            'koneksi_dengan_pihak_luar' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'created_by' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
            ],
            'created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
            'updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => true
            ],
            ]);

           $this->forge->addKey('id_proyek', true);
           $this->forge->createTable('projects');
    }

    public function down()
    {
        $this->forge->dropTable('projects');
    }
}
