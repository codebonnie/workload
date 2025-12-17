<?php

namespace App\Controllers\User;

use App\Libraries\LogEnum;
use App\Models\Onhands;
use App\Models\User;
use App\Traits\HasLogActivity;
use App\Libraries\EventLogEnum;
use App\Controllers\BaseController;
use Exception;
use CodeIgniter\Files\File;

class OnhandsController extends BaseController
{
    use HasLogActivity;

    protected $helpers = ['form'];
    public function index()
    {
       if(!in_array('view onhands', session('permissions'))) {
        $this->response->redirect(base_url('dashboard'));
       }

       $users = User::where('status', 'ACTIVE')
       ->whereIn('role', ['ASSISTANT MANAGER', 'STAFF PTI'])
       ->orderBy('name')
       ->get(['id', 'name', 'nomor_absen']);

       $data = [
        'title' => 'Daftar Onhands',
        'route' => 'onhands',
        'role' => session('role'),
        'users' => $users
       ];

       $this->logActivity([
        'log_name' => LogEnum::VIEW,
        'description' => session('username') . ' mengakses halaman ' . $data['title'],
        'event' => EventLogEnum::VERIFIED,
        'subject' => '-',
       ]);

       return $this->render('user/onhands.blade.php', $data);
    }
    
    /**
     * Return single Onhands project data for editing
     */
    public function get()
    {
        $id = $this->request->getPost('id_proyek') ?? $this->request->getPost('id');
        if (!$id) {
            return $this->response->setJSON([
                'status' => 400,
                'messages' => 'ID Proyek tidak ditemukan',
                'token' => csrf_hash()
            ]);
        }

        try {
            $onhands = Onhands::with(['unitKerja', 'pic1:id,name,nomor_absen', 'pic2:id,name,nomor_absen', 'pic3:id,name,nomor_absen'])->find($id);
            if (!$onhands) {
                return $this->response->setJSON([
                    'status' => 404,
                    'messages' => 'Data tidak ditemukan',
                    'token' => csrf_hash()
                ]);
            }

            $data = $onhands->toArray();
            $data['dokumen_lainnya'] = $onhands->dokumen_lainnya ? json_decode($onhands->dokumen_lainnya, true) : [];

            return $this->response->setJSON([
                'status' => 200,
                'data' => $data,
                'token' => csrf_hash()
            ]);
        } catch (Exception $e) {
            log_message('error', 'Get Onhands error: ' . $e->getMessage());
            return $this->response->setJSON([
                'status' => 500,
                'messages' => 'Gagal mengambil data: ' . $e->getMessage(),
                'token' => csrf_hash()
            ]);
        }
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
        $columnName = 'name_proyek';
        $columnSortOrder = 'asc';

        if(!empty($order) && is_array($order) && isset($order[0])) {
            $columnIndex = isset($order[0]['column']) ? (int)$order[0]['column'] : 0;
            $columnSortOrder = isset($order[0]['dir']) ? $order[0]['dir'] : 'asc';

            if (!empty($columns) && is_array($columns) && is_array($columns) && isset($columns[$columnIndex]['data'])) {
                $columnName = $columns[$columnIndex]['data'];
            }
        }

        $searchValue = '';
        if(!empty($search) && is_array($search) && isset($search['value'])) {
            $searchValue = $search['value'];

            $role = session('role');
            $userId = session('user_id');
            $query = Onhands::query();

            $totalRecords = $query->count();

            $filteredQuery = clone $query;
            $totalRecordswithFilter = $filteredQuery->where(function($query) use($searchValue) {
                if (!empty($searchValue)) {
                    $query->where('nama_proyek', 'like', '%' . $searchValue . '%')
                          ->orWhere('project_owner', 'like', '%'. $searchValue . '%');
                }
            })->count();

            $records = $query->when(!empty($searchValue), function($query) use ($searchValue) {
                $query->where( function($q) use ($searchValue) {
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
            $item['tanggal_mulai_selesai'] = ($item['tanggal_mulai'] ?? ''). ' s.d ' . ($item['tanggal_selesai'] ?? '');

            $item['project_owner'] = $item['unit_kerja']['name'] ?? '-';

            $item['pic_1_username'] = isset($item['pic1']['name'])
            ? $item['pic1']['name'] . ' (' . ($item['pic1']['nomor_absen'] ?? 'N/A'). ')'
            : '-';

            $item['pic_2_username'] = isset($item['pic2']['name'])
            ? $item['pic2']['name'] . ' (' . ($item['pic2']['nomor_absen'] ?? 'N/A'). ')'
            : '-';

            $item['pic_3_username'] = isset($item['pic3']['name'])
            ? $item['pic3']['name'] . ' (' . ($item['pic3']['nomor_absen'] ?? 'N/A'). ')'
            : '-';

            if($role === 'Kepala Bagian') {
                $item['aksi'] = "
                <button type ='button' class='btn btn-sm btn-info' conclick='viewDetailModal($index)'>
                    <i class = 'fal fa-eye'></i> View 
                </button>
                ";
            } elseif (in_array(strtoupper(session('role')), ['Staff Pti', 'Assistant Manager'])) {
                $item['aksi'] = "
                <div class = 'btn-gourp btn-group-sm' role='group'>
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
        $lastProject = Onhands::orderBy('id_proyek', 'desc')
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
        if(in_array(strtoupper(session('role')), ['Staff Pti', 'Assistant Manager'])) {
            return $this->response->setJSON([
                'status' => 403,
                'message' => 'Access Forbidden',
                'token' => csrf_hash()
            ]);
        }

        $data = $this->sanitizePostData([
            'id_proyek' => '',
            'nama_proyek' => '',
            'project_owner' => '',
            'catatan_disposisi' => '',
            'tanggal_mulai' => '',
            'tanggal_selesai' => '',
            'pic_1' => '',
            'pic_2' => '',
            'pic_3' => '',
            'progress' => 'Belum Terlaksana',
        ]);


        $data['project_owner'] = $this->request->getPost('project_owner');
        $validate = $this->validate([
            'id_proyek' => 'required|max_length[50]|is_unique[onhands.id_proyek]',
            'nama_proyek' => 'required|max_length[255]',
            'project_owner' => 'required|max_length[50]',
            'catatan_disposisi' => 'permit_empty|max_length[1000]',
            'tanggal_mulai' => 'required|valid_date',
            'tanggal_selesai' => 'required|valid_date',
            'pic_1' => 'required|max_length[255]',
            'pic_2' => 'permit_empty|max_length[255]',
            'pic_3' => 'permit_empty|max_length[255]',
            'progress' => 'required|in_list[Belum Terlaksana,Dalam Proses,Selesai,Ditahan,Ditunda,Diturunkan]',
            'dokumen.*' => 'permit_empty|uploaded[dokumen]|mime_in[dokumen,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-powerpoint,application/vnd.openxmlformats-officedocument.presentationml.presentation,image/jpeg,image/png,image/jpg,application/zip,application/x-rar-compressed]|max_size[dokumen,10240]'
        ]);
        
        if(!$validate) {
            return $this->response->setJSON([
                'status' => 400,
                'messages' => validation_list_errors(),
                'token' => csrf_hash()
            ]);
        }

        try {
            // Prepare data from request
            $postData = $this->request->getPost();
            $data = [
                'id_proyek' => $postData['id_proyek'],
                'nama_proyek' => $postData['nama_proyek'],
                'project_owner' => $postData['project_owner'],
                'catatan_disposisi' => $postData['catatan_disposisi'] ?? '',
                'tanggal_mulai' => $postData['tanggal_mulai'],
                'tanggal_selesai' => $postData['tanggal_selesai'],
                'pic_1' => $postData['pic_1'],
                'pic_2' => $postData['pic_2'] ?? null,
                'pic_3' => $postData['pic_3'] ?? null,
                'progress' => $postData['progress']
            ];

            // Handle file uploads
            $files = $this->request->getFileMultiple('dokumen');
            $uploadedFiles = [];

            if (!empty($files) && is_array($files)) {
                $uploadDir = WRITEPATH . 'uploads/onhands/' . date('Y/m/d');
                
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                foreach ($files as $file) {
                    if ($file->isValid() && !$file->hasMoved()) {
                        $newName = $file->getRandomName();
                        $file->move($uploadDir, $newName);
                        
                        $relativePath = 'uploads/onhands/' . date('Y/m/d') . '/' . $newName;
                        $uploadedFiles[] = $relativePath;
                    }
                }
            }

            // Add uploaded files to data as JSON string
            if (!empty($uploadedFiles)) {
                $data['dokumen_lainnya'] = json_encode($uploadedFiles);
            }

            $onhands = Onhands::create($data);

            $this->logActivity([
                'log_name' => LogEnum::DATA,
                'description' => 'Tambah Data Onhands',
                'event' => EventLogEnum::CREATED,
                'subject_id' => $onhands['id_proyek'],
                'subject' => Onhands::class,
                'properties' => json_encode($data)
            ]);

            return $this->response->setJSON([
                'status' => 200,
                'messages' => 'Data berhasil ditambahkan',
                'token' => csrf_hash(),
                'trigger_counter_update' => true
            ]);
        } catch (Exception $e) {
            log_message('error', 'Onhands create error: ' . $e->getMessage());
            return $this->response->setJSON([
                'status' => 500,
                'messages' => 'Gagal menambahkan data: ' . $e->getMessage(),
                'token' => csrf_hash()
            ]);
        }
    }

    public function edit()
    {
        if(!in_array(strtoupper(session('role')), ['STAFF PTI', 'ASSISTANT MANAGER'])) {
            return $this->response->setJSON([
                'status' => 403,
                'message' => 'Access Forbidden',
                'token' => csrf_hash()
            ]);
        }

        $postData = $this->request->getPost();
        $original_id = $postData['original_id_proyek'] ?? null;
        
        if (!$original_id) {
            return $this->response->setJSON([
                'status' => 400,
                'messages' => 'ID Proyek tidak ditemukan',
                'token' => csrf_hash()
            ]);
        }

        $validate = $this->validate([
            'id_proyek' => 'required|max_length[50]',
            'nama_proyek' => 'required|max_length[255]',
            'project_owner' => 'required|max_length[50]',
            'catatan_disposisi' => 'permit_empty|max_length[1000]',
            'tanggal_mulai' => 'required|valid_date',
            'tanggal_selesai' => 'required|valid_date',
            'pic_1' => 'required|max_length[255]',
            'pic_2' => 'permit_empty|max_length[255]',
            'pic_3' => 'permit_empty|max_length[255]',
            'progress' => 'required|in_list[Belum Terlaksana,Dalam Proses,Selesai,Ditahan,Ditunda,Diturunkan]',
            'dokumen.*' => 'permit_empty|uploaded[dokumen]|mime_in[dokumen,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-powerpoint,application/vnd.openxmlformats-officedocument.presentationml.presentation,image/jpeg,image/png,image/jpg,application/zip,application/x-rar-compressed]|max_size[dokumen,10240]'
        ]);
        
        if (!$validate) {
            return $this->response->setJSON([
                'status' => 400,
                'messages' => validation_list_errors(),
                'token' => csrf_hash()
            ]);
        }

        try {
            $onhands = Onhands::find($original_id);
            if (!$onhands) {
                return $this->response->setJSON([
                    'status' => 404,
                    'messages' => 'Data tidak ditemukan',
                    'token' => csrf_hash()
                ]);
            }

            $old = $onhands->toArray();

            $data = [
                'id_proyek' => $postData['id_proyek'],
                'nama_proyek' => $postData['nama_proyek'],
                'project_owner' => $postData['project_owner'],
                'catatan_disposisi' => $postData['catatan_disposisi'] ?? '',
                'tanggal_mulai' => $postData['tanggal_mulai'],
                'tanggal_selesai' => $postData['tanggal_selesai'],
                'pic_1' => $postData['pic_1'],
                'pic_2' => $postData['pic_2'] ?? null,
                'pic_3' => $postData['pic_3'] ?? null,
                'progress' => $postData['progress']
            ];

            // Handle file removals requested by client
            $removeFiles = $this->request->getPost('remove_files') ?? [];
            if (!empty($removeFiles) && is_array($removeFiles)) {
                $existingFiles = $onhands->dokumen_lainnya ? json_decode($onhands->dokumen_lainnya, true) : [];
                $remainingFiles = array_filter($existingFiles, function($f) use ($removeFiles) {
                    return !in_array($f, $removeFiles);
                });

                // delete removed files from disk
                foreach ($removeFiles as $filePath) {
                    $fullPath = WRITEPATH . $filePath;
                    if (file_exists($fullPath)) {
                        @unlink($fullPath);
                    }
                }

                if (!empty($remainingFiles)) {
                    $data['dokumen_lainnya'] = json_encode(array_values($remainingFiles));
                } else {
                    $data['dokumen_lainnya'] = null;
                }
            }

            // Handle file uploads
            $files = $this->request->getFileMultiple('dokumen');
            if (!empty($files) && is_array($files)) {
                $uploadDir = WRITEPATH . 'uploads/onhands/' . date('Y/m/d');
                
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $uploadedFiles = [];
                foreach ($files as $file) {
                    if ($file->isValid() && !$file->hasMoved()) {
                        $newName = $file->getRandomName();
                        $file->move($uploadDir, $newName);
                        
                        $relativePath = 'uploads/onhands/' . date('Y/m/d') . '/' . $newName;
                        $uploadedFiles[] = $relativePath;
                    }
                }

                if (!empty($uploadedFiles)) {
                    $existingFiles = $old['dokumen_lainnya'] ? json_decode($old['dokumen_lainnya'], true) : [];
                    $allFiles = array_merge($existingFiles, $uploadedFiles);
                    $data['dokumen_lainnya'] = json_encode($allFiles);
                }
            }

            $onhands->update($data);

            $this->logActivity([
                'log_name' => LogEnum::DATA,
                'description' => 'Update Data Onhands',
                'event' => EventLogEnum::UPDATED,
                'subject_id' => $onhands->id_proyek,
                'subject' => Onhands::class,
                'properties' => json_encode([
                    'old' => $old,
                    'new' => $onhands->toArray()
                ])
            ]);

            return $this->response->setJSON([
                'status' => 200,
                'messages' => 'Data berhasil diubah',
                'token' => csrf_hash()
            ]);
        } catch (Exception $e) {
            log_message('error', 'Onhands update error: ' . $e->getMessage());
            return $this->response->setJSON([
                'status' => 500,
                'messages' => 'Gagal mengubah data: ' . $e->getMessage(),
                'token' => csrf_hash()
            ]);
        }
    }
        public function progress()
    {
        $progressList = [
            ['id' => 'Belum Terlaksana', 'text' => 'Belum Terlaksana'],
            ['id' => 'Dalam Proses', 'text' => 'Dalam Proses'],
            ['id' => 'Selesai', 'text' => 'Selesai'],
            ['id' => 'Ditahan', 'text' => 'Ditahan'],
            ['id' => 'Ditunda', 'text' => 'Ditunda'],
            ['id' => 'Diturunkan', 'text' => 'Diturunkan'],
        ];
        return $this->response->setJSON([
            'status' => 200,
            'data' => $progressList,
            'token' => csrf_hash()
        ]);
    }
     public function delete()
    {
        if (!in_array(strtoupper(session('role')), ['STAFF PTI','ASSISTANT MANAGER'])) {
            return $this->response->setJSON([
                'status'=> 403,
                'message'=> 'Akses Ditolak.',
                'token' => csrf_hash()
            ]);
        }

        try {
            $id = $this->request->getPost('id');
            
            if (!$id) {
                return $this->response->setJSON([
                    'status' => 400,
                    'messages' => 'ID tidak ditemukan',
                    'token' => csrf_hash()
                ]);
            }

            $onhands = Onhands::find($id);
            
            if (!$onhands) {
                return $this->response->setJSON([
                    'status' => 404,
                    'messages' => 'Data tidak ditemukan',
                    'token' => csrf_hash()
                ]);
            }

            if (!empty($onhands->dokumen_lainnya)) {
                $files = json_decode($onhands->dokumen_lainnya, true);
                if (is_array($files)) {
                    foreach ($files as $filePath) {
                        $fullPath = WRITEPATH . $filePath;
                        if (file_exists($fullPath)) {
                            unlink($fullPath);
                        }
                    }
                }
            }

            $oldData = $onhands->toArray();
            $onhands->delete();

            $this->logActivity([
                'log_name' => LogEnum::DATA,
                'description' => 'Delete Data Onhands',
                'event' => EventLogEnum::DELETED,
                'subject_id'=> $onhands->id_proyek,
                'subject' => Onhands::class,
                'properties' => json_encode($oldData)
            ]);

            return $this->response->setJSON([
                'status' => 200,
                'messages' => 'Data Berhasil Dihapus',
                'token' => csrf_hash()
            ]);
        } catch (Exception $e) {
            log_message('error', 'Onhands delete error: ' . $e->getMessage());
            return $this->response->setJSON([
                'status' => 500,
                'messages' => 'Gagal menghapus data: ' . $e->getMessage(),
                'token' => csrf_hash()
            ]);
        }
    }

    public function getDocuments()
    {
        $id = $this->request->getPost('id');
        
        if (!$id) {
            return $this->response->setJSON([
                'status' => 400,
                'messages' => 'ID tidak ditemukan',
                'token' => csrf_hash()
            ]);
        }

        try {
            $onhands = Onhands::find($id);
            
            if (!$onhands) {
                return $this->response->setJSON([
                    'status' => 404,
                    'messages' => 'Data tidak ditemukan',
                    'token' => csrf_hash()
                ]);
            }

            $documents = [];
            if (!empty($onhands->dokumen_lainnya)) {
                $files = json_decode($onhands->dokumen_lainnya, true);
                if (is_array($files)) {
                    foreach ($files as $filePath) {
                        $fullPath = WRITEPATH . $filePath;
                        log_message('info', "Checking file: {$filePath}, Full path: {$fullPath}, Exists: " . (file_exists($fullPath) ? 'YES' : 'NO'));
                        
                        if (file_exists($fullPath)) {
                            $documents[] = [
                                'path' => $filePath,
                                'name' => basename($filePath),
                                'size' => filesize($fullPath),
                                'url' => base_url('downloadDocument/Onhands?file=' . urlencode($filePath))
                            ];
                        }
                    }
                }
            }

            return $this->response->setJSON([
                'status' => 200,
                'data' => $documents,
                'token' => csrf_hash()
            ]);
        } catch (Exception $e) {
            log_message('error', 'Get documents error: ' . $e->getMessage());
            return $this->response->setJSON([
                'status' => 500,
                'messages' => 'Gagal mengambil dokumen: ' . $e->getMessage(),
                'token' => csrf_hash()
            ]);
        }
    }

    public function downloadDocument()
    {
        $filePath = $this->request->getGet('file');
        
        if (!$filePath) {
            return $this->response->setStatusCode(400)->setBody('File not specified');
        }

        if (strpos($filePath, '..') !== false || strpos($filePath, '\\') !== false) {
            return $this->response->setStatusCode(403)->setBody('Invalid file path');
        }

        $fullPath = WRITEPATH . $filePath;

        if (!file_exists($fullPath)) {
            log_message('error', 'File not found: ' . $fullPath);
            return $this->response->setStatusCode(404)->setBody('File not found');
        }

        try {
            $filename = basename($fullPath);
            $fileSize = filesize($fullPath);
            
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Content-Length: ' . $fileSize);
            header('Pragma: no-cache');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Expires: 0');
            
            // Clear output buffer
            if (ob_get_level()) {
                ob_end_clean();
            }
            
            // Read and output file
            readfile($fullPath);
            exit;
        } catch (Exception $e) {
            log_message('error', 'Download document error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setBody('Error downloading file');
        }
    }

    public function exportToExcel()
    {
        try {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            $spreadsheet->getProperties()
                ->setCreator(session('username') ?? 'System')
                ->setLastModifiedBy(session('username') ?? 'System')
                ->setTitle('Data Onhands Export')
                ->setSubject('Onhands Data')
                ->setDescription('Export data onhands dari sistem');

            $headers = [
                'A1' => 'No',
                'B1' => 'Nama Proyek',
                'C1' => 'Project Owner',
                'D1' => 'Catatan Disposisi',
                'E1' => 'Tanggal Mulai',
                'F1' => 'Tanggal Selesai',
                'G1' => 'PIC 1',
                'H1' => 'PIC 2',
                'I1' => 'PIC 3',
                'J1' => 'Progress'
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
            $sheet->getStyle('A1:J1')->applyFromArray($headerStyle);

            $onhands = Onhands::with([
                'unitKerja:id,name',
                'pic1:id,name,nomor_absen',
                'pic2:id,name,nomor_absen',
                'pic3:id,name,nomor_absen'
            ])->orderBy('id_proyek', 'asc')->get();

            $row = 2;
            foreach ($onhands as $index => $item) {
                $pic1 = $item->pic1 ? $item->pic1->name . ' (' . ($item->pic1->nomor_absen ?? 'N/A') . ')' : '-';
                $pic2 = $item->pic2 ? $item->pic2->name . ' (' . ($item->pic2->nomor_absen ?? 'N/A') . ')' : '-';
                $pic3 = $item->pic3 ? $item->pic3->name . ' (' . ($item->pic3->nomor_absen ?? 'N/A') . ')' : '-';

                $sheet->setCellValue('A' . $row, $index + 1);
                $sheet->setCellValue('B' . $row, $item->nama_proyek ?? '-');
                $sheet->setCellValue('C' . $row, $item->unitKerja->name ?? '-');
                $sheet->setCellValue('D' . $row, $item->catatan_disposisi ?? '-');
                $sheet->setCellValue('E' . $row, $item->tanggal_mulai ? date('d/m/Y', strtotime($item->tanggal_mulai)) : '-');
                $sheet->setCellValue('F' . $row, $item->tanggal_selesai ? date('d/m/Y', strtotime($item->tanggal_selesai)) : '-');
                $sheet->setCellValue('G' . $row, $pic1);
                $sheet->setCellValue('H' . $row, $pic2);
                $sheet->setCellValue('I' . $row, $pic3);
                $sheet->setCellValue('J' . $row, $item->progress ?? '-');

                $row++;
            }

            foreach (range('A', 'J') as $column) {
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
                $sheet->getStyle('A2:J' . ($row - 1))->applyFromArray($dataStyle);
            }

            for ($i = 2; $i < $row; $i++) {
                $sheet->getRowDimension($i)->setRowHeight(-1);
            }

            $filename = 'Data_Onhands_' . date('Y-m-d_H-i-s') . '.xlsx';

            $this->logActivity([
                'log_name' => LogEnum::DATA,
                'description' => session('username') . ' melakukan export data onhands ke Excel',
                'event' => EventLogEnum::VERIFIED,
                'subject' => 'Export Excel',
                'properties' => json_encode([
                    'filename' => $filename,
                    'total_records' => count($onhands),
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
}