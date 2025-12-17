<?php

namespace App\Controllers\Log;

use App\Libraries\LogEnum;
use App\Models\Histori;
use App\Models\Project;
use App\Traits\HasHistoriProject;
use App\Traits\HasLogActivity;
use App\Controllers\BaseController;

class HistoriController extends BaseController
{
    use HasLogActivity;

    protected $historiModel;
    protected $projectModel;

    public function __construct()
    {
        $this->historiModel = new Histori();
        $this->projectModel = new Project();
    }

    public function index(): string
    {
        $data = [
            'title' => 'Histori Project',
            'route' => 'log/histori'
        ];

        $this->logActivity([
            'log_name' => LogEnum::VIEW,
            'description' => session('username') . ' mengakses Halaman ' . $data['title'],
            'event' => 'VERIFIED',
            'subject' => '-',
        ]);

        return view('log/histori', $data);
    }

    public function dataTables()
    {
        $draw = $this->request->getGet('draw');
        $start = $this->request->getGet('start');
        $length = $this->request->getGet('length');

        $columnIndex = $this->request->getGet('order')[0]['column'];
        $columnName = $this->request->getGet('columns')[$columnIndex]['data'];
        $columnSortOrder = $this->request->getGet('order')[0]['dir'];
        $searchValue = $this->request->getGet('search')['value'];

        $totalRecords = $this->historiModel->countAll();

        $builder = $this->historiModel->select([
            'histori.*',
            'projects.nama_project'
        ])->join('projects', 'projects.id = histori.id_proyek', 'left');

        if (!empty($searchValue)) {
            $builder->groupStart()
                   ->like('histori.name', $searchValue)
                   ->orLike('histori.kegiatan', $searchValue)
                   ->orLike('histori.catatan_disposisi', $searchValue)
                   ->orLike('projects.nama_project', $searchValue)
                   ->orLike('histori.catatan_tindak_lanjut', $searchValue)
                   ->groupEnd();
        }

        $totalRecordswithFilter = $builder->countAllResults(false);
        $records = $builder->orderBy($columnName, $columnSortOrder)
                          ->limit($length, $start)
                          ->get()
                          ->getResultArray();

        $data = array_map(function ($item) {
            $item['waktu'] = date("d-m-Y H:i:s", strtotime($item['waktu']));
            $item['created_at'] = date("d-m-Y H:i:s", strtotime($item['created_at']));

            switch (strtolower($item['kegiatan'])) {
                case 'menambah project':
                case 'create':
                case 'tambah':
                    $item['kegiatan'] = sprintf('<span class="badge badge-pill badge-success">%s</span>', $item['kegiatan']);
                    break;
                case 'mengubah project':
                case 'update':
                case 'edit':
                    $item['kegiatan'] = sprintf('<span class="badge badge-pill badge-warning">%s</span>', $item['kegiatan']);
                    break;
                case 'menghapus project':
                case 'delete':
                case 'hapus':
                    $item['kegiatan'] = sprintf('<span class="badge badge-pill badge-danger">%s</span>', $item['kegiatan']);
                    break;
                default:
                    $item['kegiatan'] = sprintf('<span class="badge badge-pill badge-info">%s</span>', $item['kegiatan']);
            }

            $item['aksi'] = "
                <button type='button' class='btn btn-primary btn-xs btn-icon rounded-circle' data-toggle='modal' data-target='#detail-modal' onclick='open_modal(" . $item['id_histori'] . ")'>
                    <i class='fal fa-info-circle'></i>
                </button>
            ";

            return $item;
        }, $records);

        $response = array(
            "draw" => intval($draw),
            "iTotalRecords" => $totalRecords,
            "iTotalDisplayRecords" => $totalRecordswithFilter,
            "aaData" => $data
        );

        return $this->response->setJSON($response);
    }

    
    public function showHistori($id)
    {
        try {
            $histori = $this->historiModel->select([
                'histori.*',
                'projects.nama_project'
            ])
            ->join('projects', 'projects.id = histori.id_proyek', 'left')
            ->find($id);

            if (!$histori) {
                return $this->response->setJSON('Data histori tidak ditemukan');
            }

            // Format data untuk modal
            $details = [];
            $details[] = "<strong>ID Histori:</strong> " . $histori->id_histori;
            $details[] = "<strong>ID Project:</strong> " . $histori->id_proyek;
            $details[] = "<strong>Nama Project:</strong> " . ($histori->nama_project ?: '-');
            $details[] = "<strong>Kegiatan:</strong> " . $histori->kegiatan;
            $details[] = "<strong>Catatan Disposisi:</strong> " . ($histori->catatan_disposisi ?: '-');
            $details[] = "<strong>Catatan Tindak Lanjut:</strong> " . ($histori->catatan_tindak_lanjut ?: '-');
            $details[] = "<strong>User:</strong> " . $histori->name;
            $details[] = "<strong>Role:</strong> " . $histori->role;
            $details[] = "<strong>Unit Kerja:</strong> " . ($histori->kode_unit_kerja ?: '-');
            $details[] = "<strong>Waktu:</strong> " . date('d-m-Y H:i:s', strtotime($histori->waktu));
            $details[] = "<strong>Dicatat:</strong> " . date('d-m-Y H:i:s', strtotime($histori->created_at));

            $result = implode('<br>', $details);
            
            return $this->response->setJSON($result);

        } catch (Exception $e) {
            log_message('error', 'Error showing histori: ' . $e->getMessage());
            return $this->response->setJSON('Terjadi kesalahan saat mengambil data');
        }
    }
}
