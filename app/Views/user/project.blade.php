@extends('layouts.app')

@section('content')
<!-- Breadcrumb Navigation -->
<ol class="breadcrumb bg-transparent breadcrumb-sm pl-0 pr-0 ml-2">
    <li class="breadcrumb-item">
        <a href="{{ site_url('dashboard') }}">
            <i class="fal fa-home mr-1"></i> Home
        </a>
    </li>
    <li class="breadcrumb-item">
        <a href="{{ site_url('user') }}">User</a>
    </li>
    <li class="breadcrumb-item active">{{ $title }}</li>
</ol>

<!-- Main Panel -->
<div class="panel">
    <!-- Panel Header -->
    <div class="panel-hdr">
        <h2>{{ $title }}</h2>
        <div class="panel-toolbar">
            <button type="button" class="btn btn-success btn-sm waves-effect waves-themed mr-2" onclick="exportToExcel()">
                <i class="fal fa-file-excel mr-1"></i> Export to Excel
            </button>
            
            <!-- Add Project Button (Only for Kepala Bagian) -->
            @if (session('role') == 'KEPALA BAGIAN')
                <button type="button" class="btn btn-primary btn-sm waves-effect waves-themed" onclick="openProjectTypeSelection()">
                    <i class="fal fa-plus mr-1"></i> Tambahkan Project
                </button>
            @endif
        </div>
    </div>
    
    <!-- Panel Content -->
    <div class="panel-container">
        <div class="panel-content">
            <!-- Data Table -->
            <table id="dt-project" class="table table-bordered table-hover table-striped w-100">
                <thead class="bg-primary-500">
                    <tr>
                        <th width="5%">No</th>
                        <th>Nama Proyek</th>
                        <th>Project Owner</th>
                        <th>Catatan Disposisi</th>
                        <th width="15%">Tanggal Mulai/Selesai</th>
                        <th>PIC 1</th>
                        <th>PIC 2</th>
                        <th>PIC 3</th>
                        <th>Progress</th>
                        <th data-orderable="false" width="10%">Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Forms -->
@if (session('role') == 'KEPALA BAGIAN')
<!-- Project Type Selection Modal -->
<div class="modal fade" id="project-type-modal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header">
                <div class="modal-title">
                    <h4>Pilih Jenis Project</h4>
                </div>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i class="fal fa-times"></i></span>
                </button>
            </div>
            
            <!-- Modal Body -->
            <div class="modal-body text-center py-4">
                <p class="mb-4">Silakan pilih jenis project yang akan dibuat:</p>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="card border-success" onclick="selectProjectType('reguler')" style="cursor: pointer; transition: all 0.3s ease;">
                            <div class="card-body text-center py-4">
                                <i class="fal fa-clipboard-list fa-3x text-success mb-3"></i>
                                <h5 class="card-title text-success">Project Reguler</h5>
                                <p class="card-text small text-muted">Project dengan form lengkap dan semua field standar</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-primary" onclick="selectProjectType('khusus')" style="cursor: pointer; transition: all 0.3s ease;">
                            <div class="card-body text-center py-4">
                                <i class="fal fa-star fa-3x text-primary mb-3"></i>
                                <h5 class="card-title text-primary">Project Khusus</h5>
                                <p class="card-text small text-muted">Project dengan form yang disesuaikan kebutuhan khusus</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Form Modal for Project Management -->
<div class="modal fade" id="form-modal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header">
                <div class="modal-title">
                    <h4><span id="modal-title"></span> <span id="project-type-badge"></span></h4>
                </div>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i class="fal fa-times"></i></span>
                </button>
            </div>
            
            <!-- Modal Body -->
            <div class="modal-body pt-0">
                <!-- Hidden Fields -->
                <input type="hidden" id="action" name="action">
                <input type="hidden" id="original_id_proyek" name="original_id_proyek">
                <input type="hidden" id="selected_project_type" name="selected_project_type">

                <!-- Project ID & Quarter Row -->
                <div class="form-row my-3">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label" for="id_proyek">
                                Nomor<span class="text-danger">*</span>
                            </label>
                            <input type="text" id="id_proyek" class="form-control" name="id_proyek" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label" for="triwulan">
                                Triwulan<span class="text-danger">*</span>
                            </label>
                            <select id="triwulan" name="triwulan" class="form-control" required>
                                <option value="">Pilih Triwulan</option>
                                <option value="I">Triwulan I</option>
                                <option value="II">Triwulan II</option>
                                <option value="III">Triwulan III</option>
                                <option value="IV">Triwulan IV</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label" for="tahun">
                                Tahun <span class="text-danger">*</span>
                            </label>
                            <input type="number" id="tahun" class="form-control" name="tahun" required placeholder="Contoh:{{ date('Y') }}">
                        </div>
                    </div>
                </div>

                <!-- Project Name -->
                <div class="form-row my-3">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="form-label" for="nama_proyek">
                                Nama Proyek <span class="text-danger">*</span>
                            </label>
                            <input type="text" id="nama_proyek" class="form-control" name="nama_proyek" required>
                        </div>
                    </div>
                </div>
                
                <input type="hidden" id="kategori" name="kategori">
                
                <!-- FORM REGULER FIELDS -->
                <div id="reguler-fields" style="display: none;">
                    <!-- Notes -->
                    <div class="form-row my-3">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="form-label" for="catatan_disposisi">
                                    Catatan Disposisi
                                </label>
                                <textarea id="catatan_disposisi" class="form-control" name="catatan_disposisi" rows="3" placeholder="Isi catatan disposisi jika ada"></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Date Range -->
                    <div class="form-row my-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label" for="tanggal_mulai">
                                    Tanggal Mulai <span class="text-danger">*</span>
                                </label>
                                <input type="date" id="tanggal_mulai" class="form-control" name="tanggal_mulai" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label" for="tanggal_selesai">
                                    Tanggal Selesai <span class="text-danger">*</span>
                                </label>
                                <input type="date" id="tanggal_selesai" class="form-control" name="tanggal_selesai" required>
                            </div>
                        </div>
                    </div>

                    <!-- Project Owner & PIC 1 -->
                    <div class="form-row my-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label" for="kode_unit_kerja">
                                    Project Owner <span class="text-danger">*</span>
                                </label>
                                <select class="select2 form-control w-100" id="kode_unit_kerja" name="kode_unit_kerja" required>
                                    <option></option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label" for="pic_1">
                                    PIC 1 <span class="text-danger">*</span>
                                </label>
                                <select class="select2 form-control w-100" id="pic_1" name="pic_1" required>
                                    <option value="">Pilih PIC 1</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}">
                                            {{ $user->name }} ({{ $user->nomor_absen ?? 'N/A' }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <!-- PIC 2 & PIC 3 -->
                    <div class="form-row my-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label" for="pic_2">PIC 2</label>
                                <select class="select2 form-control w-100" id="pic_2" name="pic_2">
                                    <option value="">Pilih PIC 2</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}">
                                            {{ $user->name }} ({{ $user->nomor_absen ?? 'N/A' }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label" for="pic_3">PIC 3</label>
                                <select class="select2 form-control w-100" id="pic_3" name="pic_3">
                                    <option value="">Pilih PIC 3</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}">
                                            {{ $user->name }} ({{ $user->nomor_absen ?? 'N/A' }})
                                        </option>
                                    @endforeach
                                </select>    
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- FORM KHUSUS FIELDS -->
                <div id="khusus-fields" style="display: none;">
                    <!-- Informasi: Form khusus akan dikonfigurasi nanti -->
                    <div class="alert alert-info">
                        <i class="fal fa-info-circle mr-2"></i>
                        <strong>Project Khusus</strong><br>
                        Form untuk project khusus akan disesuaikan dengan kebutuhan spesifik nanti.
                        Untuk saat ini menggunakan field minimal:
                    </div>
                    
                    <!-- Field minimal untuk project khusus -->
                    <div class="form-row my-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label" for="tanggal_mulai_khusus">
                                    Tanggal Mulai <span class="text-danger">*</span>
                                </label>
                                <input type="date" id="tanggal_mulai_khusus" class="form-control" name="tanggal_mulai" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label" for="tanggal_selesai_khusus">
                                    Tanggal Selesai <span class="text-danger">*</span>
                                </label>
                                <input type="date" id="tanggal_selesai_khusus" class="form-control" name="tanggal_selesai" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class ="form-row my-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label" for="kode_unit_kerja_khusus">
                                    Project Owner <span class="text-danger">*</span>
                                </label>
                                <select class="select2 form-control w-100" id="kode_unit_kerja_khusus" name="kode_unit_kerja" required></select>
                            </div>
                        </div>
                    </div>

                    <div class="form-row my-3">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="form-label" for="pic_1_khusus">
                                    PIC Utama <span class="text-danger">*</span>
                                </label>
                                <select class="select2 form-control w-100" id="pic_1_khusus" name="pic_1" required>
                                    <option value="">Pilih PIC Utama</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}">
                                            {{ $user->name }} ({{ $user->nomor_absen ?? 'N/A' }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Progress -->
                <div class="form-row my-3">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="form-label" for="progress">Progress</label>
                            <select class="select2 form-control w-100" id="progress" name="progress">
                                <option></option>
                            </select>
                            <small class="text-muted">
                                <i class="fal fa-info-circle"></i> Progress otomatis diset ke "Belum Terlaksana" dan akan dikelola oleh Staff PTI/Assistant Manager
                            </small>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Modal Footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="backToProjectTypeSelection()">
                    <i class="fal fa-arrow-left mr-2"></i> Kembali
                </button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fal fa-times mr-2"></i> Tutup
                </button>
                <button type="button" class="btn btn-primary" id="btnSubmit" onclick="submit()">
                    <i class="fal fa-save mr-2"></i> Simpan
                </button>
            </div>
        </div>
    </div>
</div>
@endif

@if (strtoupper(session('role')) == 'STAFF PTI' || strtoupper(session('role')) == 'ASSISTANT MANAGER')
<!-- Detail Modal for Project Progress Management -->
<div class="modal fade" id="detail-modal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content">
            <form id="formDetailProject" enctype="multipart/form-data">
                <!-- Modal Header -->
                <div class="modal-header">
                    <h4 class="modal-title">Lengkapi Detail & Progress Project</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true"><i class="fal fa-times"></i></span>
                    </button>
                </div>
                
                <!-- Modal Body -->
                <div class="modal-body overflow-auto" style="max-height:70vh;">
                    <!-- Hidden Field -->
                    <input type="hidden" name="id_proyek" id="detail_id_proyek">

                    <!-- Quarter Selection -->
                    <div class="form-row">
                        <div class="col-md-6 mb-3">
                            <label for="detail_triwulan">Triwulan <span class="text-danger">*</span></label>
                            <select name="triwulan" id="detail_triwulan" class="form-control" required>
                                <option value="">Pilih Triwulan</option>
                                <option value="I">Triwulan I</option>
                                <option value="II">Triwulan II</option>
                                <option value="III">Triwulan III</option>
                                <option value="IV">Triwulan IV</option>
                            </select>
                        </div>
                    </div>

                    <!-- Project Description -->
                    <div class="form-group">
                        <label for="deskripsi_proyek">Deskripsi Proyek</label>
                        <textarea name="deskripsi_proyek" id="deskripsi_proyek" class="form-control" rows="3"></textarea>
                    </div>

                    <!-- Development Type -->
                    <div class="form-group">
                        <label for="jenis_pengembangan">Jenis Pengembangan</label>
                        <input type="text" name="jenis_pengembangan" id="jenis_pengembangan" class="form-control">
                    </div>

                    <!-- Project Activity -->
                    <div class="form-group">
                        <label for="aktivitas_proyek">Aktivitas Proyek <span class="text-danger">*</span></label>
                        <select name="aktivitas_proyek" id="aktivitas_proyek" class="form-control select2">
                            <option value="">Pilih Aktivitas Proyek</option>
                            <option value="Belum sama sekali" selected>Belum sama sekali</option>
                            <option value="Administrasi">Administrasi</option>
                            <option value="Konsep pengembangan fitur belum selesai">Konsep pengembangan fitur belum selesai</option>
                            <option value="Konsep pengembangan fitur telah selesai">Konsep pengembangan fitur telah selesai</option>
                            <option value="Proses Development">Proses Development</option>
                            <option value="Proses SIT">Proses SIT</option>
                            <option value="Proses UAT">Proses UAT</option>
                            <option value="Penyesuaian catatan dan pengembangan fitur selesai">Penyesuaian catatan dan pengembangan fitur selesai</option>
                        </select>
                    </div>

                    <!-- Progress Selection -->
                    <div class="form-group">
                        <label for="progress_detail">Progress <span class="text-danger">*</span></label>
                        <select name="progress_detail" id="progress_detail" class="form-control select2">
                            <option value="">Pilih Progress</option>
                        </select>
                    </div>

                    <!-- Progress Bar -->
                    <div class="form-group">
                        <label class="form-label">Persentase Progress Berdasarkan Aktivitas</label>
                        <div class="progress" style="height: 25px;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                 id="progress_percentage_bar" 
                                 role="progressbar" 
                                 style="width: 0%" 
                                 aria-valuenow="0" 
                                 aria-valuemin="0" 
                                 aria-valuemax="100">
                                <span id="progress_percentage_text" style="font-weight: bold; color: white;">0%</span>
                            </div>
                        </div>
                        <small class="text-muted" id="progress_info">Pilih aktivitas project untuk melihat estimasi progress</small>
                    </div>

                    <!-- Notes -->
                    <div class="form-group">
                        <label for="keterangan">Keterangan</label>
                        <textarea name="keterangan" id="keterangan" class="form-control" rows="2"></textarea>
                    </div>

                    <!-- Document Upload Section -->
                    <div class="form-group">
                        <label class="d-block">Upload Dokumen (Opsional)</label>
                        @foreach(['izin_pengembangan','analisa_resiko','unit_testing','lainnya','review_source_code','pentest','brd','urf','kajian_biaya_manfaat','sit','uat','to','pir'] as $dok)
                            <div class="mb-3">
                                <label id="label_dokumen_{{ $dok }}" for="dokumen_{{ $dok }}" class="small font-weight-bold d-block">
                                    Dokumen {{ strtoupper($dok) }}
                                </label>
                                <input type="file" name="dokumen_{{ $dok }}" class="form-control-file">
                            </div>
                        @endforeach
                    </div>

                    <!-- Application Details -->
                    <div class="form-group">
                        <label for="alamat_aplikasi">Alamat Aplikasi</label>
                        <input type="text" name="alamat_aplikasi" id="alamat_aplikasi" class="form-control">
                    </div>

                    <!-- Server & Status Information -->
                    <div class="form-row">
                        <div class="col-md-6 mb-3">
                            <label for="server_app">Server App</label>
                            <input type="text" name="server_app" id="server_app" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="status">Status</label>
                            <select name="status" id="status" class="form-control">
                                <option value="">Pilih Status</option>
                                <option value="Aktif">Aktif</option>
                                <option value="Nonaktif">Nonaktif</option>
                            </select>
                        </div>
                    </div>

                    <!-- Technical Specifications -->
                    <div class="form-row">
                        <div class="col-md-6 mb-3">
                            <label for="platform">Platform</label>
                            <input type="text" name="platform" id="platform" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="bahasa_pemrograman">Bahasa Pemrograman</label>
                            <input type="text" name="bahasa_pemrograman" id="bahasa_pemrograman" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="framework">Framework</label>
                            <input type="text" name="framework" id="framework" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="version">Version</label>
                            <input type="text" name="version" id="version" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="database">Database</label>
                            <input type="text" name="database" id="database" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="backup_realtime">Backup Realtime</label>
                            <input type="text" name="backup_realtime" id="backup_realtime" class="form-control">
                        </div>
                    </div>

                    <!-- Infrastructure Details -->
                    <div class="form-row">
                        <div class="col-md-6 mb-3">
                            <label for="cpu">CPU</label>
                            <input type="text" name="cpu" id="cpu" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="tipe_server">Tipe Server</label>
                            <input type="text" name="tipe_server" id="tipe_server" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="os">OS</label>
                            <input type="text" name="os" id="os" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="memory">Memory</label>
                            <input type="text" name="memory" id="memory" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="pengembang_aplikasi">Pengembang Aplikasi</label>
                            <select name="pengembang_aplikasi" id="pengembang_aplikasi" class="form-control select2">
                                <option value="Internal">Internal</option>
                                <option value="Eksternal">Eksternal</option>
                                <option value="Join Development">Join Development</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="pusat_data">Pusat Data</label>
                            <input type="text" name="pusat_data" id="pusat_data" class="form-control">
                        </div>
                    </div>

                    <!-- Data Center & Implementation Details -->
                    <div class="form-row">
                        <div class="col-md-6 mb-3">
                            <label for="penyelenggara_data">Penyelenggara Data</label>
                            <input type="text" name="penyelenggara_data" id="penyelenggara_data" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="drc">DRC</label>
                            <input type="text" name="drc" id="drc" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="penyelenggara_drc">Penyelenggara DRC</label>
                            <input type="text" name="penyelenggara_drc" id="penyelenggara_drc" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="frekuensi">Frekuensi</label>
                            <input type="text" name="frekuensi" id="frekuensi" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="tanggal_implementasi">Tanggal Implementasi</label>
                            <input type="date" name="tanggal_implementasi" id="tanggal_implementasi" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="jenis_kepemilikan">Jenis Kepemilikan</label>
                            <select name="jenis_kepemilikan" id="jenis_kepemilikan" class="form-control select2">
                                <option value="Sewa">Sewa</option>
                                <option value="Milik Sendiri">Milik Sendiri</option>
                                <option value="Beli Putus">Beli Putus</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="tingkat_kritikalitas">Tingkat Kritikalitas</label>
                            <select name="tingkat_kritikalitas" id="tingkat_kritikalitas" class="form-control select2">
                                <option value="Mayor">Mayor</option>
                                <option value="Minor">Minor</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="skala_prioritas">Skala prioritas</label>
                            <select name="skala_prioritas" id="skala_prioritas" class="form-control select2">
                                <option value="High">High</option>
                                <option value="Medium">Medium</option>
                                <option value="Low">Low</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="koneksi_dengan_pihak_luar">Koneksi dengan Pihak Luar</label>
                            <select name="koneksi_dengan_pihak_luar" id="koneksi_dengan_pihak_luar" class="form-control select2">
                                <option value="Ya">Ya</option>
                                <option value="Tidak">Tidak</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="catatan_tindak_lanjut">Catatan Tindak Lanjut</label>
                            <input type="text" name="catatan_tindak_lanjut" id="catatan_tindak_lanjut" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="github">Link Github</label>
                            <input type="text" name="github" id="github" placeholder="https://github.com/namauser/project_namtanfilm" class="form-control">
                        </div>
                    </div>
                </div>
                
                <!-- Modal Footer -->
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fal fa-times mr-2"></i> Tutup
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fal fa-save mr-2"></i> Simpan Detail & Progress
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<!-- View Detail Modal (Read-Only) - All Roles -->
<div class="modal fade" id="view-detail-modal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header">
                <h4 class="modal-title">Detail Project</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i class="fal fa-times"></i></span>
                </button>
            </div>
            
            <!-- Modal Body -->
            <div class="modal-body overflow-auto" style="max-height: 70vh;">
                <!-- Basic Project Info Card -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fal fa-info-circle mr-2"></i>Informasi Project</h5>
                        <span id="view_kategori_badge"></span>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="font-weight-bold">Nomor Project:</label>
                                <p class="border rounded px-3 py-2 bg-light mb-0" id="view_id_proyek">-</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="font-weight-bold">Nama Project:</label>
                                <p class="border rounded px-3 py-2 bg-light mb-0" id="view_nama_proyek">-</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="font-weight-bold">Project Owner:</label>
                                <p class="border rounded px-3 py-2 bg-light mb-0" id="view_project_owner">-</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="font-weight-bold">Triwulan:</label>
                                <p class="border rounded px-3 py-2 bg-light mb-0" id="view_triwulan">-</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="font-weight-bold">Tahun:</label>
                                <p class="border rounded px-3 py-2 bg-light mb-0" id="view_tahun">-</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="font-weight-bold">Progress:</label>
                                <p class="border rounded px-3 py-2 bg-light mb-0" id="view_progress">-</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="font-weight-bold">Tanggal Mulai:</label>
                                <p class="border rounded px-3 py-2 bg-light mb-0" id="view_tanggal_mulai">-</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="font-weight-bold">Tanggal Selesai:</label>
                                <p class="border rounded px-3 py-2 bg-light mb-0" id="view_tanggal_selesai">-</p>
                            </div>
                            <div class="col-12 mb-3">
                                <label class="font-weight-bold">Catatan Disposisi:</label>
                                <p class="border rounded px-3 py-2 bg-light mb-0" id="view_catatan_disposisi">-</p>
                            </div>
                            <div class="col-12 mb-3">
                                <label class="font-weight-bold">Catatan Tindak Lanjut:</label>
                                <p class="border rounded px-3 py-2 bg-light mb-0" id="view_catatan_tindak_lanjut"></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- PIC Information Card -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fal fa-users mr-2"></i>Person In Charge (PIC)</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="font-weight-bold">PIC 1:</label>
                                <p class="border rounded px-3 py-2 bg-light mb-0" id="view_pic_1">-</p>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="font-weight-bold">PIC 2:</label>
                                <p class="border rounded px-3 py-2 bg-light mb-0" id="view_pic_2">-</p>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="font-weight-bold">PIC 3:</label>
                                <p class="border rounded px-3 py-2 bg-light mb-0" id="view_pic_3">-</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Detail Information Card -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fal fa-clipboard-list mr-2"></i>Detail Project</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label class="font-weight-bold">Deskripsi Project:</label>
                            <p class="border rounded px-3 py-2 bg-light" id="view_deskripsi_proyek">-</p>
                        </div>

                        <div class="form-group">
                            <label class="font-weight-bold">Jenis Pengembangan:</label>
                            <p class="border rounded px-3 py-2 bg-light" id="view_jenis_pengembangan">-</p>
                        </div>

                        <div class="form-group">
                            <label class="font-weight-bold">Aktivitas Project:</label>
                            <p class="border rounded px-3 py-2 bg-light" id="view_aktivitas_proyek">-</p>
                        </div>

                        <div class="form-group">
                            <label class="font-weight-bold">Keterangan:</label>
                            <p class="border rounded px-3 py-2 bg-light" id="view_keterangan">-</p>
                        </div>

                        <div class="form-group">
                            <label class="font-weight-bold">Alamat Aplikasi:</label>
                            <p class="border rounded px-3 py-2 bg-light" id="view_alamat_aplikasi">-</p>
                        </div>
                        
                        <!-- Technical Specifications -->
                        <div class="form-row">
                            <div class="col-md-6 mb-3">
                                <label class="font-weight-bold">Server App:</label>
                                <p class="border rounded px-3 py-2 bg-light mb-0" id="view_server_app">-</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="font-weight-bold">Status:</label>
                                <p class="border rounded px-3 py-2 bg-light mb-0" id="view_status">-</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="font-weight-bold">Platform:</label>
                                <p class="border rounded px-3 py-2 bg-light mb-0" id="view_platform">-</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="font-weight-bold">Bahasa Pemrograman:</label>
                                <p class="border rounded px-3 py-2 bg-light mb-0" id="view_bahasa_pemrograman">-</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="font-weight-bold">Framework:</label>
                                <p class="border rounded px-3 py-2 bg-light mb-0" id="view_framework">-</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="font-weight-bold">Version:</label>
                                <p class="border rounded px-3 py-2 bg-light mb-0" id="view_version">-</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="font-weight-bold">Database:</label>
                                <p class="border rounded px-3 py-2 bg-light mb-0" id="view_database">-</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="font-weight-bold">Backup Realtime:</label>
                                <p class="border rounded px-3 py-2 bg-light mb-0" id="view_backup_realtime">-</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="font-weight-bold">CPU:</label>
                                <p class="border rounded px-3 py-2 bg-light mb-0" id="view_cpu">-</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="font-weight-bold">Tipe Server:</label>
                                <p class="border rounded px-3 py-2 bg-light mb-0" id="view_tipe_server">-</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="font-weight-bold">OS:</label>
                                <p class="border rounded px-3 py-2 bg-light mb-0" id="view_os">-</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="font-weight-bold">Memory:</label>
                                <p class="border rounded px-3 py-2 bg-light mb-0" id="view_memory">-</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="font-weight-bold">Pengembang Aplikasi:</label>
                                <p class="border rounded px-3 py-2 bg-light mb-0" id="view_pengembang_aplikasi">-</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="font-weight-bold">Pusat Data:</label>
                                <p class="border rounded px-3 py-2 bg-light mb-0" id="view_pusat_data">-</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="font-weight-bold">Penyelenggara Data:</label>
                                <p class="border rounded px-3 py-2 bg-light mb-0" id="view_penyelenggara_data">-</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="font-weight-bold">DRC:</label>
                                <p class="border rounded px-3 py-2 bg-light mb-0" id="view_drc">-</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="font-weight-bold">Penyelenggara DRC:</label>
                                <p class="border rounded px-3 py-2 bg-light mb-0" id="view_penyelenggara_drc">-</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="font-weight-bold">Frekuensi:</label>
                                <p class="border rounded px-3 py-2 bg-light mb-0" id="view_frekuensi">-</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="font-weight-bold">Tanggal Implementasi:</label>
                                <p class="border rounded px-3 py-2 bg-light mb-0" id="view_tanggal_implementasi">-</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="font-weight-bold">Jenis Kepemilikan:</label>
                                <p class="border rounded px-3 py-2 bg-light mb-0" id="view_jenis_kepemilikan">-</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="font-weight-bold">Tingkat Kritikalitas:</label>
                                <p class="border rounded px-3 py-2 bg-light mb-0" id="view_tingkat_kritikalitas">-</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="font-weight-bold">Skala Prioritas</label>
                                <p class="border rounded px-3 py-2 bg-light mb-0" id="view_skala_prioritas"></p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="font-weight-bold">Koneksi dengan Pihak Luar:</label>
                                <p class="border rounded px-3 py-2 bg-light mb-0" id="view_koneksi_dengan_pihak_luar">-</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Document Section Card -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fal fa-file-alt mr-2"></i>Dokumen Project</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach(['izin_pengembangan','analisa_resiko','unit_testing','lainnya','review_source_code','pentest','brd','urf','kajian_biaya_manfaat','sit','uat','to','pir'] as $dok)
                                <div class="col-md-6 mb-3">
                                    <label class="font-weight-bold">Dokumen {{ strtoupper($dok) }}:</label>
                                    <div class="border rounded px-3 py-2 bg-light d-flex align-items-center" id="view_dokumen_{{ $dok }}">
                                        <i class="fal fa-spinner fa-spin"></i> Memuat...
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="card mb-3">
                <div class="card-header bg-light text-dark">
                    <h5 class="mb-0">
                        <i class="fal fa-user mr-2"></i>Link Github
                    </h5>
                </div>
                <div class="card-body">
                    <div id="view_github"></div>
                </div>
            </div>
            </div>
        
            <!-- Modal Footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fal fa-times mr-2"></i>Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<!-- History Modal -->
<div class="modal fade" id="histori-modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header">
                <h5 class="modal-title">Histori Disposisi</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            
            <!-- Modal Body -->
            <div class="modal-body">
                <table id="dt-histori" class="table table-bordered table-hover table-striped w-100">
                    <thead>
                        <tr>
                            <th>Waktu</th>
                            <th>Kegiatan</th>
                            <th>Oleh</th>
                            <th>Role</th>
                            <th>Catatan Disposisi</th>
                            <th>Catatan Tindak Lanjut</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
            
            <!-- Modal Footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
var csrfName = $('#txt_csrfname').attr('name');
var csrfHash = $('#txt_csrfname').val();
var historiTable;
let role = "{{ strtoupper(session('role')) }}";
var selectedProjectType = null; 

const progressMapping = {
    'Belum sama sekali': { min: 0, max: 0, display: '0%', color: 'bg-danger' },
    'Administrasi': { min: 1, max: 10, display: '1-10%', color: 'bg-danger' },
    'Konsep pengembangan fitur belum selesai': { min: 11, max: 20, display: '11-20%', color: 'bg-warning' },
    'Konsep pengembangan fitur telah selesai': { min: 21, max: 30, display: '21-30%', color: 'bg-warning' },
    'Proses Development': { min: 31, max: 70, display: '31-70%', color: 'bg-info' },
    'Proses SIT': { min: 71, max: 80, display: '71-80%', color: 'bg-info' },
    'Proses UAT': { min: 81, max: 90, display: '81-90%', color: 'bg-success' },
    'Penyesuaian catatan dan pengembangan fitur selesai': { min: 91, max: 100, display: '91-100%', color: 'bg-success' }
};

function exportToExcel() {
    toastr["info"]("Sedang memproses export Excel...");
    
    const exportForm = document.createElement('form');
    exportForm.method = 'POST';
    exportForm.action = "{{ site_url('export/Project') }}";
    exportForm.style.display = 'none';

    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = csrfName;
    csrfInput.value = csrfHash;
    exportForm.appendChild(csrfInput);
    document.body.appendChild(exportForm);
    exportForm.submit();
    
    setTimeout(() => {
        document.body.removeChild(exportForm);
        toastr["success"]("File Excel berhasil didownload!");
    }, 1000);
}

function generateNextProjectId() {
    return $.ajax({
        url: "{{ site_url('/getNextProjectId') }}",
        type: "GET",
        dataType: "json",
        success: function(result) {
            if (result.status === 200) {
                $('#id_proyek').val(result.next_id);
            } else {
                $('#id_proyek').val('1');
            }
        },
        error: function() {
            $('#id_proyek').val('1');
        }
    });
}

function updateProgressBar(aktivitas) {
    const mapping = progressMapping[aktivitas];
    if (mapping) {
        const avgProgress = Math.round((mapping.min + mapping.max) / 2);
        const progressBar = $('#progress_percentage_bar');
        
        progressBar.css('width', avgProgress + '%').attr('aria-valuenow', avgProgress);
        $('#progress_percentage_text').text(mapping.display);
        $('#progress_info').text(`Aktivitas: ${aktivitas} - Estimasi Progress: ${mapping.display}`);
        progressBar.removeClass('bg-danger bg-warning bg-info bg-success').addClass(mapping.color);
    } else {
        $('#progress_percentage_bar').css('width', '0%').attr('aria-valuenow', 0);
        $('#progress_percentage_text').text('0%');
        $('#progress_info').text('Pilih aktivitas project untuk melihat estimasi progress');
        $('#progress_percentage_bar').removeClass('bg-danger bg-warning bg-info bg-success').addClass('bg-secondary');
    }
}

function handleAjaxError(xhr, status, error) {
    let errorMessage = error;
    if (xhr.responseJSON && xhr.responseJSON.messages) {
        errorMessage = xhr.responseJSON.messages;
    } else if (xhr.responseText) {
        errorMessage = xhr.responseText;
    }
    let fullErrorMsg = `Error: ${xhr.status} ${xhr.statusText} - ${errorMessage}`;
    toastr["error"](fullErrorMsg);
    console.error("AJAX Error:", xhr);
}

function updateCsrfToken(newToken) {
    csrfHash = newToken;
    $('#txt_csrfname').val(newToken);
}

function initTable() {
    let columns = [
        { data: 'id_proyek', className: "text-center" },
        { data: 'nama_proyek' },
        { data: 'project_owner', className: "text-center" },
        { data: 'catatan_disposisi' },
        { data: 'tanggal_mulai_selesai', className: "text-center" },
        { data: 'pic_1_username', defaultContent: '-', className: "text-center" },
        { data: 'pic_2_username', defaultContent: '-', className: "text-center" },
        { data: 'pic_3_username', defaultContent: '-', className: "text-center" },
        { data: 'progress', className: "text-center" },
        {
            data: null,
            className: "text-center",
            orderable: false,
            render: function(data, type, row, meta) {
                const index = meta.row;
                const role = "{{ session('role') }}";
                const progress = (row.progress || '').toLowerCase();
                
                return `
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-toggle="dropdown">
                            <i class="fal fa-ellipsis-h"></i>
                        </button>
                        <div class="dropdown-menu">
                            <a class="dropdown-item" href="javascript:void(0)" onclick="viewDetailModal(${index})">
                                <i class="fal fa-eye mr-2"></i> Lihat Detail
                            </a>
                            <a class="dropdown-item" href="javascript:void(0)" onclick="showHistory(${row.id_proyek})">
                                <i class="fal fa-history mr-2"></i> Histori Disposisi 
                            </a>
                            ${role === 'KEPALA BAGIAN' ? `
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="javascript:void(0)" onclick="editModal(${index})">
                                    <i class="fal fa-edit mr-2"></i> Edit Project
                                </a>
                                <a class="dropdown-item text-danger" href="javascript:void(0)" onclick="deleteData(${index})">
                                    <i class="fal fa-trash mr-2"></i> Hapus Project
                                </a>
                            ` : ''}
                            ${(role === 'STAFF PTI' || role === 'ASSISTANT MANAGER') ? `
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="javascript:void(0)" onclick="detailModal(${index})">
                                    <i class="fal fa-cogs mr-2"></i> Edit Detail & Progress
                                </a>
                            ` : ''}
                        </div>
                    </div>
                `;
            }
        }
    ];
    return $('#dt-project').DataTable({
        responsive: true,
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ site_url('/dataTables/Project') }}",
            type: "GET",
            dataType: "json",
            dataSrc: 'data',
            error: handleAjaxError
        },
        columns: columns
    });
}

// Function used to dropdown projectowner rek
function initProjectOwner(selectedValue = null, setDefault = false, type = 'reguler') {
    const targetId = type === 'khusus' ? '#kode_unit_kerja_khusus' : '#kode_unit_kerja'
    return $.ajax({
        url: "{{ site_url('/optionsDivOnly/unitKerjaAPI') }}",
        type: "POST",
        dataType: "json",
        data: {[csrfName]: csrfHash},
        success: function(result) {
            updateCsrfToken(result.token);
           if (result.status === 200){
            dataDivisiFiltered = result.data.filter(function(item){
                return item.text.toLowerCase().includes('divisi');
            });
           } else {
            toastr["warning"](result.messages || "Data divisi gagal dimuat");
           }

           $.ajax({
            url: "{{ site_url('/optionsDirOnly/unitKerjaAPI') }}",
            type: "POST",
            dataType: "json",
            data: {[csrfName]: csrfHash},
            success: function(resultDirektur){
                updateCsrfToken(resultDirektur.token);
                let dataDirektur = [];
                if (resultDirektur.status === 200){
                    dataDirektur = resultDirektur.data;
                } else {
                    toastr["warning"](resultDirektur.messages || "Data direktur gagal dimuat");
                }

                const combinedData = dataDivisiFiltered.concat(dataDirektur);

                $(targetId).select2({
                    dropdownParent: $("#form-modal"),
                    placeholder: "Pilih Project Owner",
                    data: combinedData
                });

                if (selectedValue){
                    $(targetId).val(selectedValue).trigger('change');
                }
            } 
           })
        },
        error: function(xhr, status, error) { handleAjaxError(xhr, status, error); }
    });
}

// (this notes from: ara)This function used to progress for kabag, and staff/Assistant
function initProgress(selectedValue = null, setDefault = false, readOnly = false) {
    return $.ajax({
        url: "{{ site_url('/progress/Project') }}",
        type: "POST",
        dataType: "json",
        data: { [csrfName]: csrfHash }, 
        success: function(result) {
            updateCsrfToken(result.token); 
            if (result.status === 200) {
                $('#progress').select2({
                    dropdownParent: $("#form-modal"),
                    placeholder: "Pilih Progress",
                    data: result.data,
                    disabled: readOnly
                });
                
                if (setDefault) {
                    let defaultValue = null;
                    result.data.forEach(function(item) {
                        if (item.text && item.text.toLowerCase().includes('belum terlaksana')) {
                            defaultValue = item.id;
                        }
                    });
                    if (defaultValue) {
                        $('#progress').val(defaultValue).trigger('change');
                    }
                } else if (selectedValue) {
                    $('#progress').val(selectedValue).trigger('change');
                }
                
                if (readOnly) {
                    setTimeout(function() {
                        $('#progress').prop('disabled', true);
                        $('#progress').trigger('change.select2');
                    }, 100);
                }
            } else {
                toastr["warning"](result.messages);
            }
        },
        error: function(xhr, status, error) { handleAjaxError(xhr, status, error); }
    });
}

function initProgressForDetail() {
    return $.ajax({
        url: "{{ site_url('/progress/Project') }}",
        type: "POST",
        dataType: "json",
        data: { [csrfName]: csrfHash }, 
        success: function(result) {
            updateCsrfToken(result.token); 
            if (result.status === 200) {
                $('#progress_detail').select2({
                    dropdownParent: $("#detail-modal"),
                    placeholder: "Pilih Progress",
                    data: result.data,
                    width: '100%'
                });
            } else {
                toastr["warning"](result.messages);
            }
        },
        error: function(xhr, status, error) { 
            handleAjaxError(xhr, status, error); 
        }
    });
}

function initProjectOwnerForEdit(selectedValue = null) {
    const targetId = '#kode_unit_kerja';
    return $.ajax({
        url: "{{ site_url('/optionsDivOnly/unitKerjaAPI') }}",
        type: "POST",
        dataType: "json",
        data: {[csrfName]: csrfHash},
        success: function(result) {
            updateCsrfToken(result.token);
            if (result.status === 200) {
                dataDivisiFiltered = result.data.filter(function(item){
                    return item.text.toLowerCase().includes('divisi');
                });
            } else {
                toastr["warning"](result.messages || 'Data divisi gagal dimuat');
            }

            $.ajax({
                url: "{{ site_url('/optionsDirOnly/unitKerjaAPI') }}",
                type: "POST",
                dataType: "json",
                data: {[csrfName]: csrfHash},
                success: function(resultDirektur){
                    updateCsrfToken(resultDirektur.token);
                    let dataDirektur = [];
                    if (resultDirektur.status === 200) {
                        dataDirektur = resultDirektur.data;
                    } else {
                        toastr["warning"](resultDirektur.messages || "Data direktur gagal dimuat");
                    }

                    const combinedData = dataDivisiFiltered.concat(dataDirektur);

                    $(targetId).empty();

                    $(targetId).select2({
                        dropdownParent: $("#form-modal"),
                        placeholder: "Pilih Project Owner",
                        data: combinedData
                    });

                    if (selectedValue) {
                        $(targetId).val(selectedValue).trigger('change');
                    }
                }
            })
        },
        error: function(xhr, status, error) {handleAjaxError(xhr, status, error); }
    });
}

function initProgressForEdit(selectedValue = null) {
    return $.ajax({
        url: "{{ site_url('/progress/Project') }}",
        type: "POST",
        dataType: "json",
        data: { [csrfName]: csrfHash }, 
        success: function(result) {
            updateCsrfToken(result.token); 
            if (result.status === 200) {
                $('#progress').select2({
                    dropdownParent: $("#form-modal"),
                    placeholder: "Pilih Progress",
                    data: result.data,
                    disabled: true
                });
                
                if (selectedValue) {
                    $('#progress').val(selectedValue).trigger('change');
                } else {
                    let defaultValue = null;
                    result.data.forEach(function(item) {
                        if (item.text && item.text.toLowerCase().includes('belum terlaksana')) {
                            defaultValue = item.id;
                        }
                    });
                    if (defaultValue) {
                        $('#progress').val(defaultValue).trigger('change');
                    }
                }
                
                setTimeout(function() {
                    $('#progress').prop('disabled', true);
                    $('#progress').trigger('change.select2');
                }, 100);
            } else {
                toastr["warning"](result.messages);
            }
        },
        error: function(xhr, status, error) { 
            handleAjaxError(xhr, status, error); 
        }
    });
}

function initPIC(pic1Value = null, pic2Value = null, pic3Value = null) {
    $('#pic_1, #pic_2, #pic_3').each(function() {
        if ($(this).hasClass('select2-hidden-accessible')) {
            $(this).select2('destroy');
        }
        
        $(this).select2({
            dropdownParent: $("#form-modal"),
            placeholder: $(this).attr('id') === 'pic_1' ? "Pilih PIC 1" : 
                        $(this).attr('id') === 'pic_2' ? "Pilih PIC 2" : "Pilih PIC 3",
            width: '100%'
        });
    });
    
    if (pic1Value) {
        $('#pic_1').val(pic1Value).trigger('change');
    }
    if (pic2Value) {
        $('#pic_2').val(pic2Value).trigger('change');
    }
    if (pic3Value) {
        $('#pic_3').val(pic3Value).trigger('change');
    }
    
    return Promise.resolve();
}

var table = initTable();

function generateProjectNumber() {
    return $.ajax({
        url: "{{ site_url('generateNumber/Project') }}",
        type: "POST",
        dataType: "json",
        data: {[csrfName]: csrfHash},
        success: function(result) {
            updateCsrfToken(result.token);
            if(result.status === 200) {
                $('#id_proyek').val(result.data.id_proyek);
            }
        },
        error: function(xhr, status, error) {
            console.log('Error generating project number:', error);
            toastr["error"]("Gagal generate nomor proyek");
        }
    });
}

@if (session('role') == 'KEPALA BAGIAN')
$('#form-modal').on('shown.bs.modal', function () {
    var projectType = $('#selected_project_type').val();
    
    if (projectType === 'khusus') {
       $.when(initProjectOwner(null, false, 'khusus')).done(function () {
        $('#pic_1_khusus').select2({
            dropdownParent: $("#form-modal"),
            placeholder: "Pilih PIC Utama",
            width: '100%'
        });
       });
    }
    
    if ($('#action').val() === 'add') {
        if (projectType === 'reguler') {
            $.when(
                generateProjectNumber(),
                initProjectOwner(),
                initProgress(null, true, true),
                initPIC()
            ).done(function() {
            });
        } else if (projectType === 'khusus') {
            $.when(
                generateProjectNumber(),
                initProjectOwner(null, false, 'khusus'),
                initProgress(null, true, true)
            ).done(function() {
            });
        }
    }
});
$('#form-modal').on('hidden.bs.modal', function () {
    resetFormFields();
});

function resetFormFields() {
    $('#id_proyek').val('').prop('readonly', false);
    $('#nama_proyek').val('');
    $('#triwulan').val('');
    $('#tahun').val('');
    $('#catatan_disposisi').val('');
    $('#tanggal_mulai').val('');
    $('#tanggal_selesai').val('');
    $('#tanggal_mulai_khusus').val('');
    $('#tanggal_selesai_khusus').val('');
    $('#original_id_proyek').val('');
    $('#kode_unit_kerja').val(null).trigger('change');
    $('#pic_1').val(null).trigger('change');
    $('#pic_2').val(null).trigger('change');
    $('#pic_3').val(null).trigger('change');
    $('#pic_1_khusus').val(null).trigger('change');
    $('#progress').val(null).trigger('change');
    $('#kode_unit_kerja').prop('disabled', false);
    $('#progress').prop('disabled', false);
}

function openProjectTypeSelection() {
    $('#project-type-modal').modal('show');
}

function selectProjectType(projectType) {
    selectedProjectType = projectType;
    $('#project-type-modal').modal('hide');
    openProjectForm(projectType);
}

function openProjectForm(projectType) {
    $('#modal-title').html('Form Tambah Project');
    $('#action').val('add');
    $('#selected_project_type').val(projectType);
    $('#kategori').val(projectType);
    
    resetFormFields();

    const currentYear = new Date().getFullYear();
    $('#tahun').val(currentYear);
    
    if (projectType === 'reguler') {
        $('#project-type-badge').html('<span class="badge badge-success ml-2">Reguler</span>');
        $('#reguler-fields').show();
        $('#khusus-fields').hide();
        setRequiredFields('reguler');
        
    } else if (projectType === 'khusus') {
        $('#project-type-badge').html('<span class="badge badge-primary ml-2">Khusus</span>');
        $('#reguler-fields').hide();
        $('#khusus-fields').show();
        setRequiredFields('khusus');
    }
    
    $('#form-modal').modal('show');
}

function backToProjectTypeSelection() {
    $('#form-modal').modal('hide');
    setTimeout(function() {
        $('#project-type-modal').modal('show');
    }, 300);
}

function setRequiredFields(projectType) {
    $('.form-control').removeAttr('required');
    
    $('#id_proyek, #nama_proyek, #triwulan').attr('required', true);
    
    if (projectType === 'reguler') {
        $('#tanggal_mulai, #tanggal_selesai, #kode_unit_kerja, #pic_1').attr('required', true);
    } else if (projectType === 'khusus') {
        $('#tanggal_mulai_khusus, #tanggal_selesai_khusus, #kode_unit_kerja_khusus #pic_1_khusus').attr('required', true);
    }
}

function editModal(index) {
    var data = table.row(index).data();
    var projectCategory = data.kategori || 'reguler'; 
    
    $('#modal-title').html('Form Edit Project');
    $('#action').val('edit');
    $('#selected_project_type').val(projectCategory);
    $('#kategori').val(projectCategory);
    
    if (projectCategory === 'reguler') {
        $('#project-type-badge').html('<span class="badge badge-success ml-2">Reguler</span>');
        $('#reguler-fields').show();
        $('#khusus-fields').hide();
        setRequiredFields('reguler');
    } else {
        $('#project-type-badge').html('<span class="badge badge-primary ml-2">Khusus</span>');
        $('#reguler-fields').hide();
        $('#khusus-fields').show();
        setRequiredFields('khusus');
    }
    
    $('#original_id_proyek').val(data.id_proyek);
    $('#id_proyek').val(data.id_proyek).prop('readonly', true);

    $.ajax({
        url: "{{ site_url('getDetail/Project') }}",
        type: "GET",
        data: { id_proyek: data.id_proyek },
        dataType: "json",
        success: function (res) {
            if (res.status === 200 && res.data) {
                const d = res.data;
                
                $('#nama_proyek').val(d.nama_proyek || '');
                $('#triwulan').val(d.triwulan || '').trigger('change');
                $('#tahun').val(d.tahun || '');
                
                if (projectCategory === 'reguler') {
                    $('#catatan_disposisi').val(d.catatan_disposisi || '');
                    
                    if (d.tanggal_mulai_raw) {
                        $('#tanggal_mulai').val(d.tanggal_mulai_raw);
                    }
                    if (d.tanggal_selesai_raw) {
                        $('#tanggal_selesai').val(d.tanggal_selesai_raw);
                    }
                    
                    $.when(
                        initProjectOwnerForEdit(d.project_owner || d.kode_unit_kerja),
                        initProgressForEdit(d.progress_id || d.progress),
                        initPIC(d.pic_1, d.pic_2 || '', d.pic_3 || '')
                    ).done(function() {
                        $('#form-modal').modal('show');
                    });
                    
                } else {
                    if (d.tanggal_mulai_raw) {
                        $('#tanggal_mulai_khusus').val(d.tanggal_mulai_raw);
                    }
                    if (d.tanggal_selesai_raw) {
                        $('#tanggal_selesai_khusus').val(d.tanggal_selesai_raw);
                    }
                    
                    $.when(
                        initProjectOwner(d.project_owner || d.kode_unit_kerja, false, 'khusus'),
                        initProgressForEdit(d.progress_id || d.progress)
                    ).done(function() {
                        $('#pic_1_khusus').select2({
                            dropdownParent:$("#form-modal"),
                            placeholder: "Pilih PIC Utama",
                            width: '100%'
                        });
                        $('#pic_1_khusus').val(d.pic_1).trigger('change');

                        $('#form-modal').modal('show');
                    });
                }
                
            } else {
                toastr["error"]("Gagal memuat data project");
            }
        },
        error: function(xhr, status, error) {
            toastr["error"]("Error loading project data: " + error);
        }
    });
}
@endif

@if (session('role') == 'STAFF PTI' || session('role') == 'ASSISTANT MANAGER')
function detailModal(index) {
    const data = table.row(index).data();
    const id_proyek = data.id_proyek;

    $('#formDetailProject')[0].reset();
    $('#formDetailProject').find('input[type="file"]').val('');
    $('#detail_id_proyek').val(id_proyek);

    const docTypesClear = ['izin_pengembangan', 'analisa_resiko', 'unit_testing', 'lainnya', 'review_source_code','pentest', 'brd', 'urf', 'kajian_biaya_manfaat','sit', 'uat', 'to', 'pir']
    docTypesClear.forEach(function(docType) {
        const labelElement = $('#label_dokumen_' + docType);
        const originalText = 'Dokumen ' + docType.toUpperCase().replace(/_/g, '');
        labelElement.html(originalText).removeClass('text-primary font-weight-bold');
    });
    $('#formDetailProject input[type="text"], #formDetailProject input[type="date"], #formDetailProject textarea, #formDetailProject select').val('');

    $('#detail-modal .modal-body').prepend('<div id="loading-detail" class="text-center p-3"><i class="fal fa-spinner fa-spin fa-2x"></i><br><small>Memuat data...</small></div>');

    $('#aktivitas_proyek').select2({
        dropdownParent: $('#detail-modal'),
        placeholder: "Pilih Aktivitas Proyek",
        width: '100%'
    });
    
    initProgressForDetail();

    $('#aktivitas_proyek').off('change.progressUpdate').on('change.progressUpdate', function() {
        const selectedActivity = $(this).val();
        updateProgressBar(selectedActivity);
    });

    $.ajax({
        url: "{{ site_url('getDetail/Project') }}",
        type: "GET",
        data: { id_proyek: id_proyek },
        dataType: "json",
        success: function (res) {
            $('#loading-detail').remove();
            
            if (res.status === 200 && res.data) {
                const d = res.data;
                
                const docTypes = ['izin_pengembangan', 'analisa_resiko', 'unit_testing','lainnya', 'review_source_code', 'pentest', 'brd', 'urf', 'kajian_biaya_manfaat', 'sit', 'uat', 'to', 'pir'];

                docTypes.forEach(function(docType) {
                    const fieldName = 'dokumen_' + docType;
                    const fullFileNameWithPath = d[fieldName] || '';
                    const labelElement = $('#label_dokumen_' + docType);
                    const labelText = 'Dokumen ' + docType.toUpperCase().replace(/_/g, ' ');

                    if(fullFileNameWithPath) {
                        const downloadUrl = `{{ site_url('downloadDocument/Project/') }}/${id_proyek}/${docType}`;
                        const linkHtml = `
                        <a href="${downloadUrl}" target="_blank" title="pratinjau dokumen">${labelText} <i class="fal fa-external-link-alt fa-xs"></i></a>`;
                        
                        labelElement.html(linkHtml).addClass('text-primary font-weight-bold');
                    } else {
                        labelElement.html(labelText).removeClass('text-primary font-weight-bold');
                    }
                });
                
                $('#detail_triwulan').val(d.triwulan || '').trigger('change');

                $('#deskripsi_proyek').val(d.deskripsi_proyek || '');
                $('#jenis_pengembangan').val(d.jenis_pengembangan || '');
                
                const aktivitasValue = d.aktivitas_proyek || 'Belum sama sekali';
                $('#aktivitas_proyek').val(aktivitasValue).trigger('change');

                $('#progress_detail').val(d.progress_id || d.progress || '').trigger('change');
                
                updateProgressBar(aktivitasValue);
                
                $('#keterangan').val(d.keterangan || '');
                $('#catatan_tindak_lanjut').val(d.catatan_tindak_lanjut || '');
                $('#alamat_aplikasi').val(d.alamat_aplikasi || '');
                $('#server_app').val(d.server_app || '');
                $('#status').val(d.status || '');
                $('#platform').val(d.platform || '');
                $('#bahasa_pemrograman').val(d.bahasa_pemrograman || '');
                $('#framework').val(d.framework || '');
                $('#version').val(d.version || '');
                $('#database').val(d.database || '');
                $('#backup_realtime').val(d.backup_realtime || '');
                $('#cpu').val(d.cpu || '');
                $('#tipe_server').val(d.tipe_server || '');
                $('#os').val(d.os || '');
                $('#memory').val(d.memory || '');
                $('#pengembang_aplikasi').val(d.pengembang_aplikasi || '');
                $('#pusat_data').val(d.pusat_data || '');
                $('#penyelenggara_data').val(d.penyelenggara_data || '');
                $('#drc').val(d.drc || '');
                $('#penyelenggara_drc').val(d.penyelenggara_drc || '');
                $('#frekuensi').val(d.frekuensi || '');
                
                if (d.tanggal_implementasi) {
                    let dateValue = d.tanggal_implementasi;
                    if (dateValue.includes('/')) {
                        const parts = dateValue.split('/');
                        if (parts.length === 3) {
                            dateValue = parts[2] + '-' + parts[1].padStart(2, '0') + '-' + parts[0].padStart(2, '0');
                        }
                    }
                    $('#tanggal_implementasi').val(dateValue);
                }
                
                $('#jenis_kepemilikan').val(d.jenis_kepemilikan || '');
                $('#tingkat_kritikalitas').val(d.tingkat_kritikalitas || '');
                $('#skala_prioritas').val(d.skala_prioritas || '');
                $('#koneksi_dengan_pihak_luar').val(d.koneksi_dengan_pihak_luar || '');
                $('#github').val(d.github || '');
                
                toastr["success"]("Data berhasil dimuat");
            } else {
                $('#aktivitas_proyek').val('Belum sama sekali').trigger('change');
                $('#detail_triwulan').val('');
                updateProgressBar('Belum sama sekali');
                toastr["warning"](res.messages || 'Data tidak ditemukan atau kosong');
            }
        },
        error: function(xhr, status, error) {
            $('#loading-detail').remove();
            $('#aktivitas_proyek').val('Belum sama sekali').trigger('change');
            $('#detail_triwulan').val('');
            updateProgressBar('Belum sama sekali');
            console.error('Error loading detail:', xhr.responseText);
            handleAjaxError(xhr, status, error);
        },
        complete: function() {
            $('#detail-modal').modal('show');
        }
    });
}

$('#formDetailProject').off('submit.customized').on('submit.customized', function (e) {
    e.preventDefault();
    
    const aktivitasValue = $('#aktivitas_proyek').val();
    const progressValue = $('#progress_detail').val();
    const triwulanValue = $('#detail_triwulan').val();

    if(!triwulanValue) {
        toastr["warning"]("Triwulan harus dipilih");
        return;
    }
    
    if (!aktivitasValue) {
        toastr["warning"]("Aktivitas Proyek harus dipilih");
        return;
    }
    
    if (!progressValue) {
        toastr["warning"]("Progress harus dipilih");
        return;
    }
    
    const formData = new FormData(this);
    formData.append(csrfName, csrfHash);
    formData.append('progress', progressValue);

    formData.append('triwulan', triwulanValue);
    formData.append('catatan_proyek', $('#keterangan').val());
    
    const $submitBtn = $('#formDetailProject button[type="submit"]');
    const originalText = $submitBtn.html();
    $submitBtn.prop('disabled', true).html('<i class="fal fa-spinner fa-spin"></i> Menyimpan...');

    $.ajax({
        url: "{{ site_url('updateDetail/Project') }}",
        type: "POST",
        data: formData,
        processData: false,
        contentType: false,
        dataType: "json",
        success: function (res) {
            updateCsrfToken(res.token);
            if (res.status === 200) {
                toastr["success"]("Detail project dan progress berhasil disimpan");
                $('#detail-modal').modal('hide');
                reloadTable();
            } else {
                toastr["warning"](res.messages || res.message || 'Gagal menyimpan detail');
                console.error('Save error:', res);
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX error:', xhr.responseText);
            handleAjaxError(xhr, status, error);
        },
        complete: function() {
            $submitBtn.prop('disabled', false).html(originalText);
        }
    });
});
@endif

function viewDetailModal(index) {
    const data = table.row(index).data();
    const id_proyek = data.id_proyek;

    let badgeHtml ='';
    if (data.kategori === 'khusus') {
        badgeHtml = '<span class="badge badge-primary">Khusus</span>';
    } else {
        badgeHtml = '<span class="badge badge-success">Reguler</span>';
    }
    $('#view_kategori_badge').html(badgeHtml);
    $('#view_id_proyek').text(data.id_proyek || '-');
    $('#view_nama_proyek').text(data.nama_proyek || '-');
    $('#view_project_owner').text(data.project_owner || '-');
    $('#view_triwulan').text(data.triwulan ? `triwulan ${data.triwulan}` : '-');
    $('#view_tahun').text(data.tahun || '-');
    $('#view_progress').text(data.progress || '-');
    $('#view_tanggal_mulai').text(data.tanggal_mulai || '-');
    $('#view_tanggal_selesai').text(data.tanggal_selesai || '-');
    $('#view_catatan_disposisi').text(data.catatan_disposisi || '-');
    $('#view_pic_1').text(data.pic_1_username || '-');
    $('#view_pic_2').text(data.pic_2_username || '-');
    $('#view_pic_3').text(data.pic_3_username || '-');

    ['izin_pengembangan', 'analisa_resiko','unit_testing','review_source_code','pentest','brd','urf','kajian_biaya_manfaat','sit','uat','to','pir'].forEach(function(docType) {
        $('#view_dokumen_' + docType).html('<i class="fal fa-spinner fa-spin"></i> Memuat...');
    });
    $('#view_github').html('<p class="text-muted"><i class="fal fa-spinner fa-spin"></i> Memuat... </p>');

    $('#view-detail-modal').modal('show');

    $.ajax({
        url: "{{ site_url('getDetail/Project') }}",
        type: "GET",
        data: { id_proyek: id_proyek },
        dataType: "json",
        success: function (res) {
            if (res.status === 200 && res.data) {
                const d = res.data;
                $('#view_triwulan').text(d.triwulan ? `Triwulan ${d.triwulan}` : '-');

                ['izin_pengembangan','analisa_resiko','unit_testing','lainnya','review_source_code','pentest','brd','urf','kajian_biaya_manfaat','sit','uat','to','pir'].forEach(function(docType) {
                    const fieldName = 'dokumen_' + docType;
                    const fullFileNameWithPath = d[fieldName] || '';

                    const el = $('#view_dokumen_' + docType);
                    
                    if (fullFileNameWithPath) {
                        const displayName = fullFileNameWithPath.split('/').pop();
                        el.html(
                            `<a href="{{ site_url('downloadDocument/Project/') }}/${id_proyek}/${docType}" class="btn btn-sm btn-success" target="_blank">
                                <i class="fal fa-download mr-1"></i> ${displayName}
                            </a>`
                        );
                    } else {
                        el.html(
                            '<span class="text-muted"><i class="fal fa-times-circle mr-1"></i> Tidak tersedia</span>'
                        );
                    }
                });

                $('#view_deskripsi_proyek').text(d.deskripsi_proyek || '-');
                $('#view_jenis_pengembangan').text(d.jenis_pengembangan || '-');
                $('#view_aktivitas_proyek').text(d.aktivitas_proyek || '-');
                $('#view_keterangan').text(d.keterangan || '-');
                $('#view_catatan_tindak_lanjut').text(d.catatan_tindak_lanjut || '-');
                $('#view_alamat_aplikasi').text(d.alamat_aplikasi || '-');
                $('#view_server_app').text(d.server_app || '-');
                $('#view_status').text(d.status || '-');
                $('#view_platform').text(d.platform || '-');
                $('#view_bahasa_pemrograman').text(d.bahasa_pemrograman || '-');
                $('#view_framework').text(d.framework || '-');
                $('#view_version').text(d.version || '-');
                $('#view_database').text(d.database || '-');
                $('#view_backup_realtime').text(d.backup_realtime || '-');
                $('#view_cpu').text(d.cpu || '-');
                $('#view_tipe_server').text(d.tipe_server || '-');
                $('#view_os').text(d.os || '-');
                $('#view_memory').text(d.memory || '-');
                $('#view_pengembang_aplikasi').text(d.pengembang_aplikasi || '-');
                $('#view_pusat_data').text(d.pusat_data || '-');
                $('#view_penyelenggara_data').text(d.penyelenggara_data || '-');
                $('#view_drc').text(d.drc || '-');
                $('#view_penyelenggara_drc').text(d.penyelenggara_drc || '-');
                $('#view_frekuensi').text(d.frekuensi || '-');
                $('#view_tanggal_implementasi').text(d.tanggal_implementasi || '-');
                $('#view_jenis_kepemilikan').text(d.jenis_kepemilikan || '-');
                $('#view_tingkat_kritikalitas').text(d.tingkat_kritikalitas || '-');
                $('#view_skala_prioritas').text(d.skala_prioritas || '-');
                $('#view_koneksi_dengan_pihak_luar').text(d.koneksi_dengan_pihak_luar || '-');

                const githubContent = $('#view_github');
                const githubUrl = d.github;

                if (githubUrl && githubUrl.trim() !== ''){
                    const linkHTML = `
                    <p class="border rounded px-3 py-2 bg-light mb-0">
                    <a href="${githubUrl}" target="_blank" rel="noopener noreferrer">
                    ${githubUrl}
                    </a>
                    </p>`;
                    githubContent.html(linkHTML);
                } else {
                    const noLinkHTML = `
                    <p class="border rounded px-3 py-2 bg-light mb-0 text-muted">
                    <i>Tidak tersedia</i>
                    </p>`;
                    githubContent.html(noLinkHTML);
                }
            } else {
                ['izin_pengembangan','analisa_resiko','unit_testing','lainnya','review_source_code','pentest','brd','urf','kajian_biaya_manfaat','sit','uat','to','pir'].forEach(function(docType) {
                    $('#view_dokumen_' + docType).html('<span class="text-muted"><i class="fal fa-times-circle mr-1"></i> Tidak tersedia</span>');
                });
            }
        },
        error: function(xhr, status, error) {
            ['izin_pengembangan','analisa_resiko','unit_testing','lainnya','review_source_code','pentest','brd','urf','kajian_biaya_manfaat','sit','uat','to','pir'].forEach(function(docType) {
                $('#view_dokumen_' + docType).html('<span class="text-muted"><i class="fal fa-exclamation-triangle mr-1"></i>Error memuat</span>');
            });
        }
    });
}

function validateForm() {
    var projectType = $('#selected_project_type').val();
    var id_proyek = $('#id_proyek').val();
    var nama_proyek = $('#nama_proyek').val();
    var triwulan = $('#triwulan').val();
    var tahun = $('#tahun').val();
    
    if (!id_proyek) { toastr["warning"]("Nomor/ID Proyek tidak boleh kosong"); return false; }
    if (!nama_proyek) { toastr["warning"]("Nama Proyek tidak boleh kosong"); return false; }
    if (!triwulan) { toastr["warning"]("Triwulan wajib diisi"); return false; }
    if (!tahun) { toasrt["warning"]("Tahun wajib diisi"); return false;}
    
    if (projectType === 'reguler') {
        var tanggal_mulai = $('#tanggal_mulai').val();
        var tanggal_selesai = $('#tanggal_selesai').val();
        var project_owner = $('#kode_unit_kerja').val();
        var pic_1 = $('#pic_1').val();
        
        if (!tanggal_mulai) { toastr["warning"]("Tanggal Mulai tidak boleh kosong"); return false; }
        if (!tanggal_selesai) { toastr["warning"]("Tanggal Selesai tidak boleh kosong"); return false; }
        if (!project_owner) { toastr["warning"]("Project Owner tidak boleh kosong"); return false; }
        if (!pic_1) { toastr["warning"]("PIC 1 tidak boleh kosong"); return false; }
        
        if (new Date(tanggal_mulai) > new Date(tanggal_selesai)) {
            toastr["warning"]("Tanggal Mulai tidak boleh setelah Tanggal Selesai.");
            return false;
        }
        
    } else if (projectType === 'khusus') {
        var tanggal_mulai_khusus = $('#tanggal_mulai_khusus').val();
        var tanggal_selesai_khusus = $('#tanggal_selesai_khusus').val();
        var project_owner_khusus = $('#kode_unit_kerja_khusus').val();
        var pic_1_khusus = $('#pic_1_khusus').val();
        
        if (!tanggal_mulai_khusus) { toastr["warning"]("Tanggal Mulai tidak boleh kosong"); return false; }
        if (!tanggal_selesai_khusus) { toastr["warning"]("Tanggal Selesai tidak boleh kosong"); return false; }
        if (!project_owner_khusus) { toastr["warning"]("Project Owner tidak boleh kosong"); return false;}
        if (!pic_1_khusus) { toastr["warning"]("PIC Utama tidak boleh kosong"); return false; }
        
        if (new Date(tanggal_mulai_khusus) > new Date(tanggal_selesai_khusus)) {
            toastr["warning"]("Tanggal Mulai tidak boleh setelah Tanggal Selesai.");
            return false;
        }
    }
    
    return true;
}

function submit() {
    if (!validateForm()) return;
    hitEndPoint();
}

function hitEndPoint() {
    var endpoint;
    var projectType = $('#selected_project_type').val();
    var dataToSend = {
        [csrfName]: csrfHash,
        id_proyek: $('#id_proyek').val(),
        nama_proyek: $('#nama_proyek').val(),
        kategori: projectType,
        triwulan: $('#triwulan').val(),
        tahun: $('#tahun').val(),
        progress: $('#progress').val()
    };
    
    if (projectType === 'reguler') {
        dataToSend.catatan_disposisi = $('#catatan_disposisi').val();
        dataToSend.tanggal_mulai = $('#tanggal_mulai').val();
        dataToSend.tanggal_selesai = $('#tanggal_selesai').val();
        dataToSend.project_owner = $('#kode_unit_kerja').val();
        dataToSend.pic_1 = $('#pic_1').val();
        dataToSend.pic_2 = $('#pic_2').val();
        dataToSend.pic_3 = $('#pic_3').val();
        
    } else if (projectType === 'khusus') {
        dataToSend.tanggal_mulai = $('#tanggal_mulai_khusus').val();
        dataToSend.tanggal_selesai = $('#tanggal_selesai_khusus').val();
        dataToSend.project_owner = $('#kode_unit_kerja_khusus').val();
        dataToSend.pic_1 = $('#pic_1_khusus').val();
        dataToSend.catatan_disposisi = '';
        dataToSend.pic_2 = '';
        dataToSend.pic_3 = '';
    }
    
    if ($('#action').val() == 'add') {
        endpoint = "{{ site_url('/post/Project') }}";
    } else if ($('#action').val() == 'edit') {
        endpoint = "{{ site_url('/edit/Project') }}";
        dataToSend.original_id_proyek = $('#original_id_proyek').val();
    } else {
        toastr["error"]("Aksi tidak valid.");
        return;
    }
    
    $('#btnSubmit').attr('disabled', true);
    $('#btnSubmit').html('<i class="fal fa-circle-notch fa-spin"></i> Simpan');
    
    $.ajax({
        url: endpoint,
        type: "POST",
        dataType: "json",
        data: dataToSend,
        success: function(result) {
            updateCsrfToken(result.token);
            reloadTable();
            $('#form-modal').modal('hide');
            $('#btnSubmit').attr('disabled', false);
            $('#btnSubmit').html('<i class="fal fa-save mr-2"></i>Simpan');
            
            if (result.status === 200) {
                toastr["success"](result.messages || result.message);
            } else {
                toastr["warning"](result.messages || result.message);
            }
        },
        error: function(xhr, status, error) {
            $('#form-modal').modal('hide');
            $('#btnSubmit').attr('disabled', false);
            $('#btnSubmit').html('<i class="fal fa-save mr-2"></i>Simpan');
            handleAjaxError(xhr, status, error);
        }
    });
}

function deleteData(index) {
    var data = table.row(index).data();
    toastr.warning(
        "<button type='button' id='confirmationButtonYes' class='btn btn-light mt-2 ml-2'>Hapus Data</button>",
        `Apakah anda yakin ingin menghapus data ${data.nama_proyek}?`, {
            allowHtml: true,
            onShown: function(toast) {
                $("#confirmationButtonYes").click(function() {
                    hitEndPointDelete(data.id_proyek);
                });
            }
        }
    );
}

function showHistory(projectId) {
    $('#histori-modal').modal('show');

    const loadingHtml = '<tr><td colspan ="5" class="text-center"><i class="fal fa-spinner fa-spin"></i> Loading Data...</td></tr>';
    $('#dt-histori tbody').html(loadingHtml);

    return $.ajax ({
        url: `{{ site_url('getHistori/Project') }}${projectId}`,
        type: "GET",
        dataType: "json",
        success: function(response) {
            $('#dt-histori tbody').empty();

            if (response.status === 200 && response.data && response.data.length > 0) {
                response.data.forEach(function(item) {
                    const row = `
                    <tr>
                    <td>${item.waktu}</td>
                    <td>${item.kegiatan}</td>
                    <td>${item.name}</td>
                    <td>${item.role}</td>
                    <td>${item.catatan_disposisi || '-'}</td>
                    <td>${item.catatan_tindak_lanjut || '-'}</td>
                    </tr>
                    `;
                    $('#dt-histori tbody').append(row);
                });
            } else {
                const noDataHtml = '<tr><td colspan ="5" class="text-center">Histori proyek ini belum ada.</td></tr>';
                $('#dt-histori tbody').html(noDataHtml);
            }
        },
        error: function(xhr, status, error) {
            handleAjaxError(xhr, status, error);
            const errorHtml = '<tr><td colspan ="5" class="text-center text-danger">Data Histori Gagal Dimuat.</td></tr>';
            $('#dt-histori tbody').html(errorHtml);
        }
    });
}

function hitEndPointDelete(id) {
    $.ajax({
        url: "{{ site_url('/delete/Project') }}",
        type: "POST",
        dataType: "json",
        data: {
            [csrfName]: csrfHash,
            id: id,
        },
        success: function(result) {
            updateCsrfToken(result.token);
            reloadTable();
            if (result.status === 200) {
                toastr["success"](result.messages);
            } else {
                toastr["warning"](result.messages);
            }
        },
        error: function(xhr, status, error) {
            handleAjaxError(xhr, status, error);
        }
    });
}

function reloadTable() {
    table.ajax.reload();
}
</script>
@endpush
