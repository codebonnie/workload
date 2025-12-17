<?php

namespace App\Traits;

use App\Models\Histori;
use App\Models\Project;
use App\Models\User;
use Exception;

trait HasHistoriTracking
{
   protected $historiModel;
   protected $projectModel;

   protected function initHistoriModel()
   {
    if(!$this->historiModel){
        $this->historiModel = new Histori();
    }
    if (!$this->projectModel){
        $this->projectModel = new Project();
    }
   }

   protected function tracking($projectId, $kegiatan)
   {
    $this->initHistoriModel();

    try {
        $project = $this->projectModel->find($projectId);

        if (!$project) {
            log_message('error', 'Project tidak ditemukan: ' . $projectId);
            return false;
        }

        $historiData = [
            'id_proyek' => $projectId,
            'catatan_disposisi' => $project->catatan_disposisi ?? '',
            'catatan_tindak_lanjut' => $project->catatan_tindak_lanjut ?? '',
            'name' => session('username') ?? 'System',
            'role' => session('role') ?? 'User',
            'kegiatan' => $kegiatan,
            'kode_unit_kerja' => session('kode_unit_kerja') ?? $project->kd_unit_kerja ?? '',
            'waktu' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s')
        ];

        $result = $this->historiModel->insert($historiData);

        if($result) {
            $this->projectModel->update($projectId, [
                'catatan_disposisi' => null,
                'catatan_tindak_lanjut' => null,
            ]);

            log_message('info', 'Project histori: ' . $kegiatan . 'dengan id project: ' . $projectId);
            return true;
        }

        return false;
    } catch (Exception $e) {
        log_message('error', 'Error tracking: ' . $e ->getMessage());
        return false;
    }
   }
   public function CreatedTrack(string $projectId, array $projectData = [])
    {
        $historiData = [
            'id_proyek' => $projectId,
            // Mengambil catatan dari data yang dikirim controller
            'catatan_disposisi' => $projectData['catatan_disposisi'] ?? '-',
            'catatan_tindak_lanjut' => '-', // Selalu '-' saat proyek baru
            'name' => session('username') ?? 'System',
            'role' => session('role') ?? 'User',
            'kegiatan' => 'Menambahkan Project Baru',
            'kode_unit_kerja' => session('kode_unit_kerja') ?? $projectData['project_owner'] ?? '',
            'waktu' => date('Y-m-d H:i:s')
        ];

        $historiModel = new Histori();
        if (!$historiModel->insert($historiData)) {
            throw new Exception('Gagal menyimpan histori untuk proyek baru.');
        }

        return true;
    }
   public function UpdatedTrack(string $projectId)
   {
    $project = (new Project())->find($projectId);
    if (!$project) {
        throw new Exception('Project tidak ditemukan. ');
    }

    $historiData = [
        'id_proyek' => $projectId,
        'catatan_disposisi' => $project->catatan_disposisi ?? '-',
        'catatan_tindak_lanjut' => $project->catatan_tindak_lanjut ?? '-',
        'name' => session('username') ?? 'System',
        'role' => session('role') ?? 'User',
        'kegiatan' => 'Mengedit data Project',
        'kode_unit_kerja' => session('kd_unit_kerja'),
        'waktu' => date('Y-m-d H:i:s')
    ];

    $historiModel = new Histori();
    if (!$historiModel->insert($historiData)) {
        throw new Exception('Gagal menyimpan histori terhadap perubahan proyek');
    }

    return true;
   }
   public function DeletedTrack($projectId)
   {
    return $this->tracking($projectId, 'Menghapus Project');
   }
   public function ChangedProgress(string $projectId)
    {
        // Kunci: Membaca ulang data dari database untuk mendapatkan catatan terbaru
        $project = (new Project())->find($projectId);
        if (!$project) {
            throw new Exception('Project tidak ditemukan saat akan mencatat histori.');
        }
        
        $historiData = [
            'id_proyek' => $projectId,
            'catatan_disposisi' => $project->catatan_disposisi ?? '-',
            'catatan_tindak_lanjut' => $project->catatan_tindak_lanjut ?? '-',
            'name' => session('username') ?? 'System',
            'role' => session('role') ?? 'User',
            'kegiatan' => 'Memperbarui Detail & Progress',
            'kode_unit_kerja' => session('kode_unit_kerja') ?? $project->project_owner ?? '',
            'waktu' => date('Y-m-d H:i:s')
        ];

        $historiModel = new Histori();
        if (!$historiModel->insert($historiData)) {
            throw new Exception('Gagal menyimpan ke histori terhadap perubahan proyek.');
        }
        return true;
    }
   public function HasNotes($projectId)
   {
    $this->initHistoriModel();

    $project = $this->projectModel->find($projectId);

    if(!$project) {
        return false;
    }

   return !empty($project->catatan_disposisi) || !empty($project->catatan_tindak_lanjut);
   }
}