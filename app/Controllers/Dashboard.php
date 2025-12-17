<?php

namespace App\Controllers;

use App\Models\Project;
use Carbon\Carbon;
use App\Libraries\LogEnum;
use App\Libraries\EventLogEnum;
use App\Traits\HasLogActivity;

class Dashboard extends BaseController
{
    use HasLogActivity;

    public function index(): string
    {
        Carbon::setLocale('id');
        $data = [
            'title' => 'Beranda',
            'route' => 'dashboard',
            'today' => Carbon::now()->isoFormat('dddd, D MMMM Y'),
            'date' => Carbon::now()->format('Y-m-d'),
            'rekapTriwulan' => $this->getProjectPerTriwulan()
        ];
        $this->logActivity([
            'log_name' => LogEnum::VIEW,
            'description' => session('username') . ' mengakses Dashboard',
            'event' => EventLogEnum::VERIFIED,
            'subject' => '-',
        ]);
        return $this->render('dashboard', $data);
    }

    public function getProjectSummary()
    {
        try {
            $today = date('Y-m-d');
            $totalProjects = Project::count();
            $activeProgresses = ['belum terlaksana', 'dalam proses', 'ditahan', 'ditunda', 'diturunkan'];

            // ini untuk dapetin akumulasi data project yang ada. dihitung berdasarkan tanggal mulai serta selesai dari database. kemudian output datanya dihasilkan dari tanggal di komputer masing-masing
            $belumJatuhTempo = Project::whereNotNull('tanggal_selesai')
                ->where('tanggal_selesai', '>=', $today)
                ->whereIn('progress', $activeProgresses)
                ->count();

            $sudahJatuhTempo = Project::whereNotNull('tanggal_selesai')
                ->where('tanggal_selesai', '<', $today)
                ->whereIn('progress', $activeProgresses)
                ->count();

            
            $sampleOverdue = Project::whereNotNull('tanggal_selesai')
                ->where('tanggal_selesai', '<', $today)
                ->whereIn('progress', $activeProgresses)
                ->select('nama_proyek', 'tanggal_selesai', 'progress')
                ->limit(5)
                ->get();
            
            log_message('debug', "Sample overdue projects: " . json_encode($sampleOverdue->toArray()));

            $progressData = Project::selectRaw('progress, COUNT(*) as total')
                ->groupBy('progress')
                ->get()
                ->keyBy('progress');

            $progressSummary = [
                'belum_terlaksana' => $progressData['belum terlaksana']->total ?? 0,
                'dalam_proses' => $progressData['dalam proses']->total ?? 0,
                'selesai' => $progressData['selesai']->total ?? 0,
                'ditahan' => $progressData['ditahan']->total ?? 0,
                'ditunda' => $progressData['ditunda']->total ?? 0,
                'diturunkan' => $progressData['diturunkan']->total ?? 0,
            ];

            $aktivitasData = Project::selectRaw('aktivitas_proyek, COUNT(*) as jumlah_project')
                ->whereNotNull('aktivitas_proyek')
                ->where('aktivitas_proyek', '!=', '')
                ->groupBy('aktivitas_proyek')
                ->get()
                ->keyBy('aktivitas_proyek');

            $aktivitasMapping = [
                'belum_sama_sekali' => 'Belum sama sekali',
                'administrasi' => 'Administrasi',
                'konsep_pengembangan_fitur_belum_selesai' => 'Konsep pengembangan fitur belum selesai',
                'konsep_pengembangan_fitur_telah_selesai' => 'Konsep pengembangan fitur telah selesai',
                'proses_development' => 'Proses Development',
                'proses_sit' => 'Proses SIT',
                'proses_uat' => 'Proses UAT',
                'penyesuaian_catatan_dan_pengembangan_fitur_selesai' => 'Penyesuaian catatan dan pengembangan fitur selesai',
            ];

            $aktivitasSummary = [];
            foreach ($aktivitasMapping as $key => $dbValue) {
                $jumlah = isset($aktivitasData[$dbValue]) ? $aktivitasData[$dbValue]->jumlah_project : 0;
                $aktivitasSummary[$key] = $jumlah;
            }

            return $this->response->setJSON([
                'status' => 200,
                'data' => [
                    'keseluruhan' => [
                        'total_project' => $totalProjects,
                    ],
                    'deadline' => [
                        'belum_jatuh_tempo' => $belumJatuhTempo,
                        'sudah_jatuh_tempo' => $sudahJatuhTempo,
                    ],
                    'progress' => $progressSummary,
                    'aktivitas' => $aktivitasSummary,
                    'triwulan' => $this->getProjectPerTriwulan(),
                ],
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Dashboard summary error: ' . $e->getMessage());
            return $this->response->setJSON([
                'status' => 500,
                'message' => 'Error loading dashboard data: ' . $e->getMessage(),
            ]);
        }
    }

    // Tambahkan fungsi debugging untuk mengecek data
    public function debugProjects()
    {
        try {
            $today = date('Y-m-d');
            $activeProgresses = ['belum terlaksana', 'dalam proses', 'ditahan','selesai', 'ditunda', 'diturunkan'];

            // Cek semua project dengan tanggal_selesai
            $allProjectsWithDate = Project::whereNotNull('tanggal_selesai')
                ->select('nama_proyek', 'tanggal_selesai', 'progress')
                ->orderBy('tanggal_selesai', 'asc')
                ->get();

            // Cek project yang sudah jatuh tempo tapi semua status
            $overdueAllStatus = Project::whereNotNull('tanggal_selesai')
                ->where('tanggal_selesai', '<', $today)
                ->select('nama_proyek', 'tanggal_selesai', 'progress')
                ->get();

            // Cek project yang sudah jatuh tempo dengan status aktif
            $overdueActiveOnly = Project::whereNotNull('tanggal_selesai')
                ->where('tanggal_selesai', '<', $today)
                ->whereIn('progress', $activeProgresses)
                ->select('nama_proyek', 'tanggal_selesai', 'progress')
                ->get();

            return $this->response->setJSON([
                'status' => 200,
                'today' => $today,
                'active_progresses' => $activeProgresses,
                'all_projects_with_date' => $allProjectsWithDate,
                'overdue_all_status' => $overdueAllStatus,
                'overdue_active_only' => $overdueActiveOnly,
                'counts' => [
                    'all_with_date' => $allProjectsWithDate->count(),
                    'overdue_all' => $overdueAllStatus->count(),
                    'overdue_active' => $overdueActiveOnly->count()
                ]
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => 500,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function getProjectPerTriwulan()
    {
        $rows = Project::selectRaw('
                CASE
                    WHEN triwulan LIKE "Triwulan I" THEN "I"
                    WHEN triwulan LIKE "I" THEN "I"
                    WHEN triwulan LIKE "Triwulan II" THEN "II"
                    WHEN triwulan LIKE "II" THEN "II"
                    WHEN triwulan LIKE "Triwulan III" THEN "III"
                    WHEN triwulan LIKE "III" THEN "III"
                    WHEN triwulan LIKE "Triwulan IV" THEN "IV"
                    WHEN triwulan LIKE "IV" THEN "IV"
                    ELSE "Lainnya"
                END as triwulan_label, COUNT(*) as total
            ')
            ->groupBy('triwulan_label')
            ->orderByRaw('FIELD(triwulan_label,"I","II","III","IV","Lainnya")')
            ->get();

        $result = [
            'I' => 0,
            'II' => 0,
            'III' => 0,
            'IV' => 0,
            'Lainnya' => 0
        ];

        foreach ($rows as $row) {
            $result[$row->triwulan_label] = $row->total;
        }
        return $result;
    }

    public function listProjects()
    {
        try {
            $filter = $this->request->getGet('filter');
            $today = date('Y-m-d');
            $activeProgresses = ['belum terlaksana', 'dalam proses', 'selesai','ditahan', 'ditunda', 'diturunkan'];
            
            $query = Project::with(['pic1', 'pic2', 'pic3', 'unitKerja'])
                ->orderBy('id_proyek', 'asc');

            if ($filter === 'belum-jatuh') {
                $query->whereNotNull('tanggal_selesai')
                      ->where('tanggal_selesai', '>=', $today)
                      ->whereIn('progress', $activeProgresses);
            } elseif ($filter === 'sudah-jatuh') {
                $query->whereNotNull('tanggal_selesai')
                      ->where('tanggal_selesai', '<', $today)
                      ->whereIn('progress', $activeProgresses);
            } elseif (strpos($filter, 'triwulan-') === 0) {
                $triwulan = str_replace('triwulan-', '', $filter);
                $triwulanUpper = strtoupper($triwulan);
                $query->where(function($q) use ($triwulanUpper) {
                    $q->where('triwulan', 'triwulan ' . $triwulanUpper)
                      ->orWhere('triwulan', $triwulanUpper);
                });
            }

            $projects = $query->get();

            $data = [];
            foreach($projects as $idx => $p) {
                $picNames = [];
                if ($p->pic1) $picNames[] = $p->pic1->name.' ('.($p->pic1->nomor_absen ?? '-') . ')';
                if ($p->pic2) $picNames[] = $p->pic2->name.' ('.($p->pic2->nomor_absen ?? '-') . ')';
                if ($p->pic3) $picNames[] = $p->pic3->name.' ('.($p->pic3->nomor_absen ?? '-') . ')';
                $data[] = [
                    'no' => $idx+1,
                    'nama_proyek' => $p->nama_proyek,
                    'project_owner' => optional($p->unitKerja)->name ?? '-',
                    'tanggal_mulai' => $p->tanggal_mulai,
                    'tanggal_selesai' => $p->tanggal_selesai,
                    'pic' => implode(', ', $picNames) ?: '-',
                    'progress' => $p->progress,
                ];
            }
            return $this->response->setJSON([ 'status'=>200, 'data'=>$data ]);
        } catch (\Exception $e){
            return $this->response->setJSON([ 'status'=>500, 'message'=>$e->getMessage() ]);
        }
    }
}
