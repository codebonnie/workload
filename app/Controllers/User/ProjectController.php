<?php

namespace App\Controllers\User;

use App\Libraries\LogEnum;
use App\Models\Project;
use App\Models\Histori;
use App\Models\User; 
use App\Traits\HasHistoriTracking;
use App\Traits\HasLogActivity;
use App\Libraries\EventLogEnum;
use App\Controllers\BaseController;
use Exception;

class ProjectController extends BaseController
{
    use HasHistoriTracking;
    
    use HasLogActivity;
    protected $helpers = ['form'];

    public function index(): string
    {
        if (!in_array('view project', session('permissions'))) {
            $this->response->redirect(base_url('dashboard'));
        }

        $users = User::where('status', 'ACTIVE')
            ->whereIn('role', ['ASSISTANT MANAGER', 'STAFF PTI'])
            ->orderBy('name')
            ->get(['id', 'name', 'nomor_absen']); 

        $data = [
            'title' => 'Daftar Project',
            'route' => 'project',
            'role' => session('role'),
            'users' => $users
        ];

        $this->logActivity([
            'log_name' => LogEnum::VIEW,
            'description' => session('username') . ' mengakses Halaman ' . $data['title'],
            'event' => EventLogEnum::VERIFIED,
            'subject' => '-',
        ]);

        return $this->render('user/project.blade.php', $data);
    }

    public function dataTables()
    {
        $draw = $this->request->getGet('draw');
        $start = $this->request->getGet('start');
        $length = $this->request->getGet('length');

        $order = $this->request->getGet('order') ?? [];
        $columns = $this->request->getGet('columns') ?? [];
        $search = $this->request->getGet('search') ?? [];

        $columnIndex = 0;
        $columnName = 'nama_proyek';
        $columnSortOrder = 'asc';

        if (!empty($order) && is_array($order) && isset($order[0])) {
            $columnIndex = isset($order[0]['column']) ? (int)$order[0]['column'] : 0; 
            $columnSortOrder = isset($order[0]['dir']) ? $order[0]['dir'] : 'asc'; 
            
            if (!empty($columns) && is_array($columns) && isset($columns[$columnIndex]['data'])) {
                $columnName = $columns[$columnIndex]['data'];
            }
        }

        $searchValue = '';
        if (!empty($search) && is_array($search) && isset($search['value'])) {
            $searchValue = $search['value'];
        }

        $role = session('role');
        $userId = session('user_id');
        $query = Project::query();

        $totalRecords = $query->count();
        
        $filteredQuery = clone $query;
        $totalRecordswithFilter = $filteredQuery->where(function($query) use ($searchValue) {
            if (!empty($searchValue)) {
                $query->where('nama_proyek', 'like', '%' . $searchValue . '%')
                      ->orWhere('project_owner', 'like', '%' . $searchValue . '%');
            }
        })->count();

        $records = $query->when(!empty($searchValue), function($query) use ($searchValue) {
                $query->where(function($q) use ($searchValue) {
                    $q->where('nama_proyek', 'like', '%' . $searchValue . '%') 
                      ->orWhere('project_owner', 'like', '%' . $searchValue . '%');
                });
            })
            ->orderBy($columnName, $columnSortOrder)
            ->with([
                'unitKerja', 
                'pic1:id,name,nomor_absen', 
                'pic2:id,name,nomor_absen', 
                'pic3:id,name,nomor_absen'
            ])
            ->skip($start)
            ->take($length)
            ->get()
            ->toArray(); 

        $data = array_map(function($item, $index) use ($start, $role) {
            $item['no'] = $start + $index + 1;
            $item['tanggal_mulai_selesai'] = ($item['tanggal_mulai'] ?? '') . ' s.d. ' . ($item['tanggal_selesai'] ?? '');
           
            $item['project_owner'] = $item['unit_kerja']['name'] ?? '-';
            $item['triwulan'] = $item['triwulan'] ?? ''; 
            
            $item['pic_1_username'] = isset($item['pic1']['name']) 
                ? $item['pic1']['name'] . ' (' . ($item['pic1']['nomor_absen'] ?? 'N/A') . ')' 
                : '-';
            
            $item['pic_2_username'] = isset($item['pic2']['name']) 
                ? $item['pic2']['name'] . ' (' . ($item['pic2']['nomor_absen'] ?? 'N/A') . ')' 
                : '-';
                
            $item['pic_3_username'] = isset($item['pic3']['name']) 
                ? $item['pic3']['name'] . ' (' . ($item['pic3']['nomor_absen'] ?? 'N/A') . ')' 
                : '-';

            if ($role === 'KEPALA BAGIAN') {
                $progress = strtolower($item['progress'] ?? '');
                if ($progress === 'belum terlaksana') {
                    $item['aksi'] = "
                        <div class='btn-group btn-group-sm' role='group'>
                            <button type='button' class='btn btn-sm btn-primary' onclick='editModal($index)'><i class='fal fa-edit'></i></button>
                            <button type='button' class='btn btn-sm btn-danger' onclick='deleteData($index)'><i class='fal fa-trash'></i></button>
                        </div>
                    ";
                } else {
                    $item['aksi'] = "
                        <button type='button' class='btn btn-sm btn-info' onclick='viewDetailModal($index)'>
                            <i class='fal fa-eye'></i> View
                        </button>
                    ";
                }
            } elseif (strtoupper($role) === 'STAFF PTI' || strtoupper($role) === 'ASSISTANT MANAGER') {
                $item['aksi'] = "
                    <div class='d-grid gap-1'>
                        <button type='button' class='btn btn-warning btn-sm' onclick='detailModal($index)'>
                            <i class='fal fa-edit'></i> Edit Detail & Progress
                        </button>
                        <button type='button' class='btn btn-info btn-sm' onclick='viewDetailModal($index)'>
                            <i class='fal fa-eye'></i> View
                        </button>
                    </div>
                ";
            } else {
                $item['aksi'] = "
                    <button type='button' class='btn btn-sm btn-info' onclick='viewDetailModal($index)'>
                        <i class='fal fa-eye'></i> View
                    </button>
                ";
            }
            return $item;
        }, $records, array_keys($records));

        $response = array(
            "draw" => intval($draw),
            "recordsTotal" => $totalRecords,
            "recordsFiltered" => $totalRecordswithFilter,
            "data" => $data
        );
        return $this->response->setJSON($response);
    }

    public function export()
    {
        if (!in_array('view project', session('permissions'))) {
            return redirect()->to(base_url('dashboard'))->with('error', 'Akses ditolak');
        }

        try {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            $spreadsheet->getProperties()
                ->setCreator(session('username') ?? 'System')
                ->setLastModifiedBy(session('username') ?? 'System')
                ->setTitle('Data Project Export')
                ->setSubject('Project Data')
                ->setDescription('Export data project dari sistem');

            $headers = [
                'A1' => 'No',
                'B1' => 'Nama Proyek',
                'C1' => 'Kategori',
                'D1' => 'Project Owner',
                'E1' => 'Triwulan',
                'F1' => 'Catatan Disposisi',
                'G1' => 'Tanggal Mulai',
                'H1' => 'Tanggal Selesai',
                'I1' => 'PIC 1',
                'J1' => 'PIC 2',
                'K1' => 'PIC 3',
                'L1' => 'Progress',
                'M1' => 'Catatan Tindak Lanjut',
                'N1' => 'Aktivitas Proyek',
                'O1' => 'Deskripsi Proyek',
                'P1' => 'Jenis Pengembangan',
                'Q1' => 'Alamat Aplikasi',
                'R1' => 'Server App',
                'S1' => 'Status',
                'T1' => 'Platform',
                'U1' => 'Bahasa Pemrograman',
                'V1' => 'Framework',
                'W1' => 'Version',
                'X1' => 'Database',
                'Y1' => 'Backup Realtime',
                'Z1' => 'CPU',
                'AA1' => 'Tipe Server',
                'AB1' => 'OS',
                'AC1' => 'Memory',
                'AD1' => 'Pengembang Aplikasi',
                'AE1' => 'Pusat Data',
                'AF1' => 'Penyelenggara Data',
                'AG1' => 'DRC',
                'AH1' => 'Penyelenggara DRC',
                'AI1' => 'Frekuensi',
                'AJ1' => 'Tanggal Implementasi',
                'AK1' => 'Jenis Kepemilikan',
                'AL1' => 'Tingkat Kritikalitas',
                'AM1' => 'Skala Prioritas',
                'AN1' => 'Koneksi dengan Pihak Luar',
                'AO1' => 'Keterangan'
            ];

            foreach ($headers as $cell => $header) {
                $sheet->setCellValue($cell, $header);
            }

            $headerStyle = [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF']
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4472C4']
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
                    ]
                ]
            ];
            $sheet->getStyle('A1:AO1')->applyFromArray($headerStyle);

            $projects = Project::with([
                'unitKerja:id,name',
                'pic1:id,name,nomor_absen',
                'pic2:id,name,nomor_absen',
                'pic3:id,name,nomor_absen'
            ])->orderBy('id_proyek', 'asc')->get();

            $row = 2;
            foreach ($projects as $index => $project) {
                $pic1 = $project->pic1 ? $project->pic1->name . ' (' . ($project->pic1->nomor_absen ?? 'N/A') . ')' : '-';
                $pic2 = $project->pic2 ? $project->pic2->name . ' (' . ($project->pic2->nomor_absen ?? 'N/A') . ')' : '-';
                $pic3 = $project->pic3 ? $project->pic3->name . ' (' . ($project->pic3->nomor_absen ?? 'N/A') . ')' : '-';

                $sheet->setCellValue('A' . $row, $index + 1);
                $sheet->setCellValue('B' . $row, $project->nama_proyek ?? '-');
                $sheet->setCellValue('C' . $row, $project->kategori ?? '-');
                $sheet->setCellValue('D' . $row, $project->unitKerja->name ?? '-');
                $sheet->setCellValue('E' . $row, $project->triwulan ?? '-');
                $sheet->setCellValue('F' . $row, $project->catatan_disposisi ?? '-');
                $sheet->setCellValue('G' . $row, $project->tanggal_mulai ? date('d/m/Y', strtotime($project->tanggal_mulai)) : '-');
                $sheet->setCellValue('H' . $row, $project->tanggal_selesai ? date('d/m/Y', strtotime($project->tanggal_selesai)) : '-');
                $sheet->setCellValue('I' . $row, $pic1);
                $sheet->setCellValue('J' . $row, $pic2);
                $sheet->setCellValue('K' . $row, $pic3);
                $sheet->setCellValue('L' . $row, $project->progress ?? '-');
                $sheet->setCellValue('M' . $row, $project->catatan_tindak_lanjut ?? '-');
                $sheet->setCellValue('N' . $row, $project->aktivitas_proyek ?? '-');
                $sheet->setCellValue('O' . $row, $project->deskripsi_proyek ?? '-');
                $sheet->setCellValue('P' . $row, $project->jenis_pengembangan ?? '-');
                $sheet->setCellValue('Q' . $row, $project->alamat_aplikasi ?? '-');
                $sheet->setCellValue('R' . $row, $project->server_app ?? '-');
                $sheet->setCellValue('S' . $row, $project->status ?? '-');
                $sheet->setCellValue('T' . $row, $project->platform ?? '-');
                $sheet->setCellValue('U' . $row, $project->bahasa_pemrograman ?? '-');
                $sheet->setCellValue('V' . $row, $project->framework ?? '-');
                $sheet->setCellValue('W' . $row, $project->version ?? '-');
                $sheet->setCellValue('X' . $row, $project->database ?? '-');
                $sheet->setCellValue('Y' . $row, $project->backup_realtime ?? '-');
                $sheet->setCellValue('Z' . $row, $project->cpu ?? '-');
                $sheet->setCellValue('AA' . $row, $project->tipe_server ?? '-');
                $sheet->setCellValue('AB' . $row, $project->os ?? '-');
                $sheet->setCellValue('AC' . $row, $project->memory ?? '-');
                $sheet->setCellValue('AD' . $row, $project->pengembang_aplikasi ?? '-');
                $sheet->setCellValue('AE' . $row, $project->pusat_data ?? '-');
                $sheet->setCellValue('AF' . $row, $project->penyelenggara_data ?? '-');
                $sheet->setCellValue('AG' . $row, $project->drc ?? '-');
                $sheet->setCellValue('AH' . $row, $project->penyelenggara_drc ?? '-');
                $sheet->setCellValue('AI' . $row, $project->frekuensi ?? '-');
                $sheet->setCellValue('AJ' . $row, $project->tanggal_implementasi ? date('d/m/Y', strtotime($project->tanggal_implementasi)) : '-');
                $sheet->setCellValue('AK' . $row, $project->jenis_kepemilikan ?? '-');
                $sheet->setCellValue('AL' . $row, $project->tingkat_kritikalitas ?? '-');
                $sheet->setCellValue('AM' . $row, $project->skala_prioritas ?? '-');
                $sheet->setCellValue('AN' . $row, $project->koneksi_dengan_pihak_luar ?? '-');
                $sheet->setCellValue('AO' . $row, $project->keterangan ?? '-');

                $row++;
            }

            foreach (range('A', 'AO') as $column) {
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }

            if ($row > 2) {
                $dataStyle = [
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
                        ]
                    ],
                    'alignment' => [
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP,
                        'wrapText' => true
                    ]
                ];
                $sheet->getStyle('A2:AO' . ($row - 1))->applyFromArray($dataStyle);
            }

            for ($i = 2; $i < $row; $i++) {
                $sheet->getRowDimension($i)->setRowHeight(-1);
            }

            $filename = 'Data_Project_' . date('Y-m-d_H-i-s') . '.xlsx';

            $this->logActivity([
                'log_name' => LogEnum::DATA,
                'description' => session('username') . ' melakukan export data project ke Excel',
                'event' => EventLogEnum::VERIFIED,
                'subject' => 'Export Excel',
                'properties' => json_encode([
                    'filename' => $filename,
                    'total_records' => count($projects),
                    'exported_at' => date('Y-m-d H:i:s')
                ])
            ]);

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
            header('Cache-Control: max-age=1');
            header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
            header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
            header('Cache-Control: cache, must-revalidate');
            header('Pragma: public');

            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save('php://output');
            exit();

        } catch (Exception $e) {
            log_message('error', 'Export Excel error: ' . $e->getMessage());
            
            return redirect()->back()->with('error', 'Gagal melakukan export: ' . $e->getMessage());
        }
    }

    private function sanitizePostData(array $fieldDefaults): array
    {
        $data = [];
        foreach ($fieldDefaults as $field => $default) {
            $value = $this->request->getPost($field);
            $data[$field] = ($value === null || $value === 'null' || $value === '') ? $default : $value;
        }
        return $data;
    }

    private function generateProjectNumber(): string
    {
        // Get the highest number
        $lastProject = Project::orderBy('id_proyek', 'desc')
            ->first();
        
        if ($lastProject) {
            // Convert current id to integer and add 1
            $newNumber = intval($lastProject->id_proyek) + 1;
        } else {
            $newNumber = 1;
        }
        
        return (string)$newNumber;
    }

    public function generateNumber()
    {
        try {
            $newNumber = $this->generateProjectNumber();
            
            return $this->response->setJSON([
                'status' => 200,
                'data' => [
                    'id_proyek' => $newNumber
                ],
                'token' => csrf_hash()
            ]);
        } catch (Exception $e) {
            log_message('error', 'Generate project number error: ' . $e->getMessage());
            return $this->response->setJSON([
                'status' => 500,
                'messages' => 'Gagal generate nomor proyek: ' . $e->getMessage(),
                'token' => csrf_hash()
            ]);
        }
    }

    public function post()
    {
        if (session('role') !== 'KEPALA BAGIAN') {
            return $this->response->setJSON([
                'status' => 403,
                'message' => 'Akses ditolak.',
                'token' => csrf_hash()
            ]);
        }

        log_message('debug', 'RAW POST: ' . json_encode($_POST));
        log_message('debug', 'getPost triwulan: ' . $this->request->getPost('triwulan'));

        $kategori = $this->request->getPost('kategori') ?? 'reguler';
        
        if ($kategori === 'reguler') {
            $data = $this->sanitizePostData([
                'id_proyek'         => '',
                'nama_proyek'       => '',
                'triwulan'          => '',
                'tahun'             => '',
                'kategori'          => 'reguler',
                'catatan_disposisi' => '', 
                'tanggal_mulai'     => '',
                'tanggal_selesai'   => '',
                'project_owner'     => '',
                'pic_1'             => '',
                'pic_2'             => '', 
                'pic_3'             => '', 
                'progress'          => '',
                'aktivitas_proyek'  => 'Belum sama sekali',
            ]);
            // Auto-generate id_proyek if not provided (match Onhands numbering: simple incrementing integer: 1, 2, 3, dst)
            if (empty($data['id_proyek'])) {
                $data['id_proyek'] = $this->generateProjectNumber();
                log_message('debug', 'Auto-generated id_proyek for Project (reguler): ' . $data['id_proyek']);
            }
            
            $validate = $this->validate([
                'id_proyek'         => 'required|max_length[50]|is_unique[projects.id_proyek]',
                'nama_proyek'       => 'required|max_length[255]', 
                'kategori'          => 'required|in_list[reguler,khusus]',
                'triwulan'          => 'required|in_list[I,II,III,IV]',
                'tahun'             => 'required|numeric|exact_length[4]',
                'catatan_disposisi' => 'permit_empty|max_length[1000]', 
                'tanggal_mulai'     => 'required|valid_date',
                'tanggal_selesai'   => 'required|valid_date',
                'project_owner'     => 'required|max_length[50]', 
                'pic_1'             => 'required|max_length[50]', 
                'pic_2'             => 'permit_empty|max_length[50]', 
                'pic_3'             => 'permit_empty|max_length[50]', 
                'progress'          => 'required|in_list[belum terlaksana,dalam proses,selesai,ditahan,ditunda,diturunkan]'
            ]);
            
        } else if ($kategori === 'khusus') {
            $data = $this->sanitizePostData([
                'id_proyek'         => '',
                'nama_proyek'       => '',
                'triwulan'          => '',
                'kategori'          => 'khusus',
                'catatan_disposisi' => '', 
                'tanggal_mulai'     => '',
                'tanggal_selesai'   => '',
                'project_owner'     => '', 
                'pic_1'             => '',
                'pic_2'             => '', 
                'pic_3'             => '', 
                'progress'          => '',
                'aktivitas_proyek'  => 'Belum sama sekali',
            ]);
            $data['project_owner'] = $this->request->getPost('project_owner');
            // Auto-generate id_proyek if not provided (match Onhands numbering: simple incrementing integer: 1, 2, 3, dst)
            if (empty($data['id_proyek'])) {
                $data['id_proyek'] = $this->generateProjectNumber();
                log_message('debug', 'Auto-generated id_proyek for Project (khusus): ' . $data['id_proyek']);
            }
            $validate = $this->validate([
                'id_proyek'         => 'required|max_length[50]|is_unique[projects.id_proyek]',
                'nama_proyek'       => 'required|max_length[255]', 
                'kategori'          => 'required|in_list[reguler,khusus]',
                'triwulan'          => 'required|in_list[I,II,III,IV]',
                'tanggal_mulai'     => 'required|valid_date',
                'tanggal_selesai'   => 'required|valid_date',
                'project_owner'   => 'required|max_length[50]',
                'pic_1'             => 'required|max_length[50]', 
                'progress'          => 'required|in_list[belum terlaksana,dalam proses,selesai,ditahan,ditunda,diturunkan]'
            ]);
        } else {
            return $this->response->setJSON([
                'status' => 400,
                'messages' => 'Kategori project tidak valid.',
                'token' => csrf_hash()
            ]);
        }

        if (!$validate) {
            $errors = $this->validator->getErrors();
            log_message('error', 'Validation failed: ' . json_encode($errors));
            log_message('error', 'POST data: ' . json_encode($data));
            return $this->response->setJSON([
                'status' => 400,
                'messages' => validation_list_errors(),
                'debug_data' => $data, 
                'detailed_errors' => $errors, 
                'token' => csrf_hash()
            ]);
        }

        if (in_array($data['triwulan'], ['I', 'II', 'III', 'IV'])) {
            $data['triwulan'] = 'triwulan ' . $data['triwulan']; 
        }

        try {
            $project = new Project();
            $result = $project->insert($data);

            log_message('debug', 'Insert result: ' . ($result ? 'SUCCESS' : 'FAILED'));
            log_message('debug', 'Final data: ' . json_encode($data));

            $this->logActivity([
                'log_name' => LogEnum::DATA,
                'description' => 'Tambah Data',
                'event' => EventLogEnum::CREATED,
                'subject_id' => $data['id_proyek'],
                'subject' => Project::class,
                'properties' => json_encode($data)
            ]);

            $tracking = '';
            try {
                $this->CreatedTrack($data['id_proyek'], $data);
                $tracking = '. Trace to history';
                log_message('info', 'Trace of created success' . $data['id_proyek']);
            } catch (Exception $ET) {
                log_message('error', 'Trace of created error: ' . $ET->getMessage());
                return $this->response->setJSON([
                    'status' => 200,
                    'messages' => 'Data berhasil diubah namun gagal menyimpan ke histori' . $ET->getMessage(),
                    'token' => csrf_hash()
                ]);
            }
            return $this->response->setJSON([
                'status' => 200,
                'messages' => 'Data berhasil ditambahkan' . $tracking,
                'token' => csrf_hash(),
                'trigger_counter_update' => true
            ]);
        } catch (Exception $e) {
            log_message('error', 'Insert failed: ' . $e->getMessage());
            return $this->response->setJSON([
                'status' => 500,
                'messages' => 'Gagal menambah data: ' . $e->getMessage(),
                'token' => csrf_hash()
            ]);
        } 
    }

    public function edit()
    {
        $role = session('role');
        $id = $this->request->getPost('id_proyek');
        if (!$id) {
            return $this->response->setJSON([
                'status' => 400,
                'message' => 'ID project tidak ditemukan.',
                'token' => csrf_hash()
            ]);
        }

        if (strtoupper($role) === 'STAFF PTI' || strtoupper($role) === 'ASSISTANT MANAGER') {
            $progress = $this->request->getPost('progress') ?? '';
            $validate = $this->validate([
                'progress' => 'required|in_list[belum terlaksana,dalam proses,selesai,ditahan,ditunda,diturunkan]'
            ]);
            if (!$validate) {
                return $this->response->setJSON([
                    'status' => 400,
                    'message' => validation_list_errors(),
                    'token' => csrf_hash()
                ]);
            }

            $project = Project::where('id_proyek', $id)->first();
            if (!$project) {
                return $this->response->setJSON([
                    'status' => 404,
                    'message' => 'Project tidak ditemukan.',
                    'token' => csrf_hash()
                ]);
            }
            $old = $project->toArray();
            $project->update(['progress' => $progress]);
            $this->logActivity([
                'log_name' => LogEnum::DATA,
                'description' => 'Update Progress (Staff/PTI/ASSISTANT MANAGER)',
                'event' => EventLogEnum::UPDATED,
                'subject_id' => $project->id_proyek,
                'subject' => Project::class,
                'properties' => json_encode([
                    'old' => $old,
                    'new' => $project->toArray()
                ])
            ]);

            $tracking = '';
            try {
                $this->ChangedProgress($project->id_proyek);
                $trackingMessage = '. Success of trace histori';
                log_message('info', 'Tracking histori project berikut: ' . $project->id_proyek);
            } catch (Exception $ET) {
                log_message('error', 'Trace was error' . $ET->getMessage());
                return $this->response->setJSON([
                    'status' => 500,
                    'messages' => 'Data berhasil diubah, namun gagal menyimpan pada histori' . $ET ->getMessage(),
                    'token' => csrf_hash()
                ]);
            }
            return $this->response->setJSON([
                'status' => 200,
                'messages' => 'Progress berhasil diubah' . $trackingMessage,
                'token' => csrf_hash()
            ]);
        }

        if ($role !== 'KEPALA BAGIAN') {
            return $this->response->setJSON([
                'status' => 403,
                'message' => 'Akses ditolak.',
                'token' => csrf_hash()
            ]);
        }

        $project = Project::where('id_proyek', $id)->first();
        if (!$project) {
            return $this->response->setJSON([
                'status' => 404,
                'message' => 'Project tidak ditemukan.',
                'token' => csrf_hash()
            ]);
        }

        $kategori = $this->request->getPost('kategori') ?? $project->kategori ?? 'reguler';
        
        $rawData = $this->request->getPost();
        unset($rawData['id_proyek']);
        unset($rawData['original_id_proyek']); 

        $data = [];
        foreach ($rawData as $key => $value) {
            $data[$key] = ($value === null || $value === 'null') ? '' : $value;
        }

        if ($kategori === 'reguler') {
            $validate = $this->validate([
                'nama_proyek'       => 'required|max_length[255]',
                'kategori'          => 'required|in_list[reguler,khusus]',
                'triwulan'          => 'required|in_list[I,II,III,IV]',
                'tahun'             => 'required|numeric|exact_length[4]',
                'catatan_disposisi' => 'permit_empty|max_length[1000]', 
                'project_owner'     => 'required|max_length[50]',
                'pic_1'             => 'required|max_length[50]',
                'pic_2'             => 'permit_empty|max_length[50]', 
                'pic_3'             => 'permit_empty|max_length[50]', 
                'tanggal_mulai'     => 'required|valid_date',
                'tanggal_selesai'   => 'required|valid_date',
                'progress'          => 'required|in_list[belum terlaksana,dalam proses,selesai,ditahan,ditunda,diturunkan]'
            ]);
        } else if ($kategori === 'khusus') {
            $validate = $this->validate([
                'nama_proyek'       => 'required|max_length[255]',
                'kategori'          => 'required|in_list[reguler,khusus]',
                'triwulan'          => 'required|in_list[I,II,III,IV]',
                'project_owner'   => 'required|max_length[50]',
                'pic_1'             => 'required|max_length[50]',
                'tanggal_mulai'     => 'required|valid_date',
                'tanggal_selesai'   => 'required|valid_date',
                'progress'          => 'required|in_list[belum terlaksana,dalam proses,selesai,ditahan,ditunda,diturunkan]'
            ]);
        }

        if (!$validate) {
            return $this->response->setJSON([
                'status' => 400, 
                'message' => validation_list_errors(),
                'token' => csrf_hash()
            ]);
        }

        if (in_array($data['triwulan'], ['I', 'II', 'III', 'IV'])) {
            $data['triwulan'] = 'triwulan ' . $data['triwulan']; 
        }

        $old = $project->toArray();
        $project->update($data);
        
        $this->logActivity([
            'log_name' => LogEnum::DATA,
            'description' => 'Update Data',
            'event' => EventLogEnum::UPDATED,
            'subject_id' => $project->id_proyek,
            'subject' => Project::class,
            'properties' => json_encode([
                'old' => $old,
                'new' => $project->toArray()
            ])
        ]);

        $trackingMessage = '';
        try {
            $this->UpdatedTrack($project->id_proyek);
            $trackingMessage = '. Success of trace history';
            log_message('info', 'Tracking histori project berikut: ' . $project->id_proyek);
        } catch (Exception $ET) {
            log_message('error', 'Trace was failed' . $ET->getMessage());
            return $this->response->setJSON([
                'status' => 500,
                'messages' => 'Data berhasil diubah, namun gagal menyimpan ke histori' . $ET->getMessage(),
                'token' => csrf_hash()
            ]);
        }
        return $this->response->setJSON([
            'status' => 200,
            'messages' => 'Data berhasil diubah' . $trackingMessage,
            'token' => csrf_hash()
        ]);
    }

    public function delete()
    {
        if (session('role') !== 'KEPALA BAGIAN') {
            return $this->response->setJSON([
                'status' => 403,
                'message' => 'Akses ditolak.',
                'token' => csrf_hash()
            ]);
        }

        $id = $this->request->getPost('id');

        $project = Project::find($id);
        $project->delete();

        $this->logActivity([
            'log_name' => LogEnum::DATA,
            'description' => 'Delete Data',
            'event' => EventLogEnum::DELETED,
            'subject_id' => $project->id,
            'subject' => Project::class,
            'properties' => json_encode($project->toArray())
        ]);

        return $this->response->setJSON([
            'status' => 200,
            'messages' => 'Data Berhasil dihapus!',
            'token' => csrf_hash()
        ]);
    }

    public function progress()
    {
        $progressList = [
            ['id' => 'belum terlaksana', 'text' => 'Belum Terlaksana'],
            ['id' => 'dalam proses', 'text' => 'Dalam Proses'],
            ['id' => 'selesai', 'text' => 'Selesai'],
            ['id' => 'ditahan', 'text' => 'Ditahan'],
            ['id' => 'ditunda', 'text' => 'Ditunda'],
            ['id' => 'diturunkan', 'text' => 'Diturunkan'],
        ];
        return $this->response->setJSON([
            'status' => 200,
            'data' => $progressList,
            'token' => csrf_hash()
        ]);
    }

    public function updateDetail()
    {
        $role = strtoupper(trim(session('role')));

        if (!in_array($role, ['STAFF PTI', 'ASSISTANT MANAGER'])) {
            return $this->response->setJSON([
                'status' => 403,
                'message' => 'Akses ditolak.',
                'token' => csrf_hash()
            ]);
        }

        $id = $this->request->getPost('id_proyek');
        if (!$id) {
            return $this->response->setJSON([
                'status' => 400,
                'message' => 'ID proyek tidak ditemukan.',
                'token' => csrf_hash()
            ]);
        }

        $project = Project::where('id_proyek', $id)->first();
        if (!$project) {
            return $this->response->setJSON([
                'status' => 404,
                'message' => 'Project tidak ditemukan.',
                'token' => csrf_hash()
            ]);
        }

        $aktivitasProyek = $this->request->getPost('aktivitas_proyek') ?? '';
        $progress = $this->request->getPost('progress') ?? '';
        $triwulan = $this->request->getPost('triwulan') ?? '';

        if (!$aktivitasProyek) {
            return $this->response->setJSON([
                'status' => 400,
                'message' => 'Aktivitas Proyek harus dipilih.',
                'token' => csrf_hash()
            ]);
        }

        if (!$progress) {
            return $this->response->setJSON([
                'status' => 400,
                'message' => 'Progress harus dipilih.',
                'token' => csrf_hash()
            ]);
        }

        if (!$triwulan) {
            return $this->response->setJSON([
                'status' => 400,
                'message' => 'Triwulan harus dipilih.',
                'token' => csrf_hash()
            ]);
        }

        if (!in_array($triwulan, ['I', 'II', 'III', 'IV'])) {
            return $this->response->setJSON([
                'status' => 400,
                'message' => 'Triwulan tidak valid.',
                'token' => csrf_hash()
            ]);
        }

        $validProgress = ['belum terlaksana', 'dalam proses', 'selesai', 'ditahan', 'ditunda', 'diturunkan'];
        if (!in_array($progress, $validProgress)) {
            return $this->response->setJSON([
                'status' => 400,
                'message' => 'Progress tidak valid.',
                'token' => csrf_hash()
            ]);
        }

        $fields = [
            'triwulan',
            'deskripsi_proyek', 'jenis_pengembangan', 'aktivitas_proyek', 'keterangan',
            'alamat_aplikasi', 'server_app', 'status', 'platform',
            'bahasa_pemrograman', 'framework', 'version', 'database', 'backup_realtime',
            'cpu', 'tipe_server', 'os', 'memory', 'pengembang_aplikasi', 'pusat_data',
            'penyelenggara_data', 'drc', 'penyelenggara_drc', 'frekuensi', 'tanggal_implementasi',
            'jenis_kepemilikan', 'tingkat_kritikalitas', 'skala_prioritas', 'koneksi_dengan_pihak_luar', 'catatan_tindak_lanjut',
            'github'
        ];

        $dokumenFields = [
            'dokumen_izin_pengembangan','dokumen_analisa_resiko','dokumen_unit_testing','dokumen_lainnya','dokumen_review_source_code','dokumen_pentest','dokumen_brd', 'dokumen_urf', 'dokumen_kajian_biaya_manfaat',
            'dokumen_sit', 'dokumen_uat', 'dokumen_to', 'dokumen_pir'
        ];

        $updateData = [];
        foreach ($fields as $field) {
            $value = $this->request->getPost($field);
            $updateData[$field] = ($value === 'null') ? '' : $value;
        }

        if (in_array($updateData['triwulan'], ['I', 'II', 'III', 'IV'])) {
            $updateData['triwulan'] = 'triwulan ' . $updateData['triwulan']; 
        }

        $updateData['progress'] = $progress;

        $uploadPath = WRITEPATH . 'uploads/dokumen_proyek/';
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        foreach ($dokumenFields as $field) {
            $file = $this->request->getFile($field);
            
            if ($file && $file->isValid() && !$file->hasMoved()) {
                $oldFilePath = $project->$field;

                $hash = random_int(1000, 9999);
                $newVersion = +1;

                if ($oldFilePath && file_exists(WRITEPATH . 'uploads/' . $oldFilePath)) {
                    unlink(WRITEPATH . 'uploads/' . $oldFilePath);
                }

                if ($oldFilePath) {
                     if (file_exists(WRITEPATH . 'uploads/' . $oldFilePath)) {
                        unlink(WRITEPATH . 'uploads/' . $oldFilePath);
                     }

                }

                if ($file->getSize() <= 0) {
                    return $this->response->setJSON([
                        'status' => 400,
                        'message' => "File {$field} kosong atau corrupt.",
                        'token' => csrf_hash()
                    ]);
                }

                if ($file->getSize() > 10 * 1024 * 1024) {
                    return $this->response->setJSON([
                        'status' => 400,
                        'message' => "File {$field} terlalu besar (maksimal 10MB).",
                        'token' => csrf_hash()
                    ]);
                }

                $versionField = $field . '_version';
                $currentVersion = $project->$versionField ?? 0;
                $newVersion = $currentVersion + 1;

                $now = date('Ymd_His');
                sleep(1);

                $docType = str_replace('dokumen_', '', $field);
                $dateStr = date('dmY');
                $idProyek = $project->id_proyek;
                $extension = $file->getClientExtension();
                $filename = "dokumen_{$docType}_{$dateStr}_{$hash}_{$project->$idProyek}_({$newVersion}).{$extension}";

                try {
                    if (!$file->move($uploadPath, $filename)) {
                        throw new Exception("Gagal memindahkan file");
                    }

                    $uploadedFilePath = $uploadPath . $filename;
                    if (!file_exists($uploadedFilePath)) {
                        throw new Exception("File tidak ditemukan setelah upload");
                    }

                    if (filesize($uploadedFilePath) <= 0) {
                        unlink($uploadedFilePath); 
                        throw new Exception("File tersimpan kosong");
                    }

                    chmod($uploadedFilePath, 0644);
                    
                    $updateData[$field] = 'dokumen_proyek/' . $filename;
                    $updateData[$field . '_upload_date'] = date('Y-m-d H:i:s');
                    $updateData[$field . '_version'] = $newVersion;
                    
                    log_message('info', "File uploaded successfully: {$uploadedFilePath}, size: " . filesize($uploadedFilePath));

                } catch (\Exception $e) {
                    return $this->response->setJSON([
                        'status' => 500,
                        'message' => "Gagal mengupload {$field}: " . $e->getMessage(),
                        'token' => csrf_hash()
                    ]);
                }
            } else {
                $updateData[$field] = $project->$field;
            }
        }

        $old = $project->toArray();
        $project->update($updateData);

        $this->logActivity([
            'log_name' => LogEnum::DATA,
            'description' => 'Update Detail & Progress Project (Staff/PTI/ASSISTANT MANAGER)',
            'event' => EventLogEnum::UPDATED,
            'subject_id' => $project->id_proyek,
            'subject' => Project::class,
            'properties' => json_encode([
                'old' => $old,
                'new' => $project->toArray()
            ])
        ]);

        $trackingMessage = '';
        try{
           $catatan = $this->request->getPost('catatan_disposisi');

           $this->ChangedProgress($project->id_proyek);
           log_message('info', 'Trace was success for this project: ' . $project->id_proyek);
        } catch (Exception $ET) {
            log_message('error', 'Trace was failed' . $ET->getMessage());
        }

        $updatedProject = (new Project())->find($project->id_proyek);
        return $this->response->setJSON([
            'status' => 200,
            'messages' => 'Detail project dan progress berhasil diperbarui.',
            'token' => csrf_hash(),
            'trigger_counter_update' => true
        ]);
    }

    public function getProjectCounts()
    {
        $role = session('role');
        $userId = session('user_id');

        if (!in_array(strtoupper($role), ['STAFF PTI', 'ASSISTANT MANAGER'])) {
            return $this->response->setJSON([
                'status' => 403,
                'message' => 'Akses ditolak.'
            ]);
        }

        $projectBaruCount = Project::where(function($q) use ($userId) {
            $q->where('pic_1', $userId)
              ->orWhere('pic_2', $userId)
              ->orWhere('pic_3', $userId);
        })
        ->where('aktivitas_proyek', 'Belum sama sekali')
        ->where('progress', 'belum terlaksana')
        ->count();

        return $this->response->setJSON([
            'status' => 200,
            'data' => [
                'project_baru_count' => $projectBaruCount,
                'timestamp' => date('H:i:s d/m/Y')
            ]
        ]);
    }

    public function getDetail()
    {
        $id = $this->request->getGet('id_proyek');
        $project = Project::with([
            'unitKerja:id,name',
            'pic1:id,name,nomor_absen',
            'pic2:id,name,nomor_absen',
            'pic3:id,name,nomor_absen'
        ])
        ->where('id_proyek', $id)
        ->first();

        if (!$project) {
            return $this->response->setJSON([
                'status'  => 404,
                'message' => 'Data tidak ditemukan'
            ]);
        }

        $responseData = $project->toArray();
        
        $responseData['triwulan'] = $this->TriwulanForFrontend($project->triwulan);
        $responseData['tahun'] = $project->tahun;
        $responseData['progress_id'] = $responseData['progress'];
        $responseData['project_owner'] = $responseData['unit_kerja']['name'] ?? '';
        $responseData['kode_unit_kerja'] = $project->project_owner; 
        
        if ($project->tanggal_mulai) {
            $responseData['tanggal_mulai'] = date('d/m/Y', strtotime($project->tanggal_mulai)); 
            $responseData['tanggal_mulai_raw'] = $project->tanggal_mulai; 
        }
        
        if ($project->tanggal_selesai) {
            $responseData['tanggal_selesai'] = date('d/m/Y', strtotime($project->tanggal_selesai)); 
            $responseData['tanggal_selesai_raw'] = $project->tanggal_selesai; 
        }
        
        $docTypes = ['izin_pengembangan','analisa_resiko','unit_testing','lainnya','review_source_code','pentest','brd', 'urf', 'kajian_biaya_manfaat', 'sit', 'uat', 'to', 'pir'];
        
        foreach ($docTypes as $docType) {
            $fieldName = 'dokumen_' . $docType;
            $responseData[$fieldName] = $project->$fieldName;
            $uploadDateField = $fieldName . '_upload_date';
            
            if (!isset($responseData[$fieldName])) {
                $responseData[$fieldName] = null;
            }
            
            if (isset($project->$uploadDateField) && $project->$uploadDateField) {
                $responseData[$uploadDateField] = $project->$uploadDateField;
            } else {
                $responseData[$uploadDateField] = $project->updated_at;
            }
        }

        return $this->response->setJSON([
            'status' => 200,
            'data'   => $responseData,
            'token'  => csrf_hash()
        ]);
    }
    private function TriwulanForFrontend($dbTriwulan)
    {
        $mapping = [
            'Triwulan I' => 'I',
            'Triwulan II' => 'II', 
            'Triwulan III' => 'III',
            'Triwulan IV' => 'IV'
        ];
        
        return $mapping[$dbTriwulan] ?? $dbTriwulan;
    }

    public function downloadDocument($id_proyek, $doc_type)
    {
        try {
            while (ob_get_level()) {
                ob_end_clean();
            }
            
            $allowedTypes = ['izin_pengembangan','analisa_resiko','unit_testing','lainnya','review_source_code','pentest','brd', 'urf', 'kajian_biaya_manfaat', 'sit', 'uat', 'to', 'pir'];
            
            if (!in_array($doc_type, $allowedTypes)) {
                throw new \CodeIgniter\Exceptions\PageNotFoundException('Document type not found');
            }

            $project = Project::where('id_proyek', $id_proyek)->first();
            if (!$project) {
                throw new \CodeIgniter\Exceptions\PageNotFoundException('Project not found');
            }

            $fieldName = 'dokumen_' . $doc_type;
            $fileNameWithPath = $project->$fieldName;

            if (!$fileNameWithPath) {
                throw new \CodeIgniter\Exceptions\PageNotFoundException('Document not found in database');
            }

            $filePath = WRITEPATH . 'uploads/' . $fileNameWithPath;

            $displayName = basename($fileNameWithPath);

            if (!file_exists($filePath)) {
                log_message('error', "File not found: {$filePath}");
                throw new \CodeIgniter\Exceptions\PageNotFoundException('File not found on server');
            }

            $fileSize = filesize($filePath);
            if ($fileSize <= 0) {
                log_message('error', "Empty file: {$filePath}");
                throw new \CodeIgniter\Exceptions\PageNotFoundException('File is empty');
            }
            if (!is_readable($filePath)) {
                log_message('error', "File not readable: {$filePath}");
                throw new \CodeIgniter\Exceptions\PageNotFoundException('File cannot be read');
            }

            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $filePath);
            finfo_close($finfo);
            
            if (!$mimeType) {
                $mimeType = 'application/octet-stream';
            }

            header('Content-Type: ' . $mimeType);
            header('Content-Disposition: inline; filename="' . $displayName . '"');
            header('Content-Length: ' . $fileSize);
            header('Cache-Control: private, max-age=0, must-revalidate');
            header('Pragma: public');

            readfile($filePath);

            $extension = pathinfo($fileNameWithPath, PATHINFO_EXTENSION);
            

            $displayName = basename($fileNameWithPath);

            return $this->response->download($filePath, null)->setFileName($displayName);
            
            if (headers_sent()) {
                throw new \Exception('Headers already sent, cannot download file');
            }
            
            while (!feof($handle)) {
                $chunk = fread($handle, 8192);
                echo $chunk;
                flush();
            }
            
            fclose($handle);
            exit();

        } catch (\CodeIgniter\Exceptions\PageNotFoundException $e) {
            throw $e;
        } catch (\Exception $e) {
            log_message('error', 'Document download error: ' . $e->getMessage());
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Error downloading file: ' . $e->getMessage());
        }
    }
    public function getHistori($id_proyek)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403, 'Akses anda ditolak');
        }

        $histori = Histori::where('id_proyek', $id_proyek)
        ->orderBy('waktu', 'DESC')
        ->get();

        return $this->response->setJSON([
            'status' => 200,
            'data' => $histori
        ]);
    }
    
}
