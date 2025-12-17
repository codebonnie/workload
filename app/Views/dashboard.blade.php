@extends('layouts.app')

@section('content')
    <ol class="breadcrumb page-breadcrumb">
        <li class="breadcrumb-item">
            <a href="{{ site_url('dashboard') }}">
                <i class="fal fa-home mr-1"></i> Home
            </a>
        </li>
        <li class="breadcrumb-item active">{{ $title }}</li>
        <li class="position-absolute pos-right d-none d-sm-block" id="current-date">{{ $today }}</li>
    </ol>

    <div class="subheader">
        <h1 class="subheader-title">
            <i class="subheader-icon fal fa-university"></i>
            Selamat Datang, <span class="fw-300">{{ session('name') }}</span>
            <br>
            <small id="current-time" class="text-muted"></small>
        </h1>
    </div>

    <div class="row">
        <div class="col-12 mb-3">
            <div class="card py-2">
                <div class="row text-center no-gutters">
                    <div class="col">
                        <div class="card bg-primary text-white py-2 mx-2" id="card-triwulan-I" style="cursor:pointer">
                            <h6>Triwulan I</h6>
                            <h4>{{ $rekapTriwulan['I'] ?? 0 }}</h4>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card bg-success text-white py-2 mx-2" id="card-triwulan-II" style="cursor:pointer">
                            <h6>Triwulan II</h6>
                            <h4>{{ $rekapTriwulan['II'] ?? 0 }}</h4>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card bg-warning text-white py-2 mx-2" id="card-triwulan-III" style="cursor:pointer">
                            <h6>Triwulan III</h6>
                            <h4>{{ $rekapTriwulan['III'] ?? 0 }}</h4>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card bg-danger text-white py-2 mx-2" id="card-triwulan-IV" style="cursor:pointer">
                            <h6>Triwulan IV</h6>
                            <h4>{{ $rekapTriwulan['IV'] ?? 0 }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-4 col-md-6">
            <div class="card bg-primary text-white mb-3" id="card-total-project" style="cursor:pointer">
                <div class="card-body">
                    <h5>Total Project</h5>
                    <h2 id="total-project">0</h2>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6">
            <div class="card bg-success text-white mb-3" id="card-belum-jatuh" style="cursor:pointer">
                <div class="card-body">
                    <h5>Project Belum Jatuh Tempo</h5>
                    <h2 id="project-belum-jatuh">0</h2>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6">
            <div class="card bg-danger text-white mb-3" id="card-sudah-jatuh" style="cursor:pointer">
                <div class="card-body">
                    <h5>Project Sudah Jatuh Tempo</h5>
                    <h2 id="project-sudah-jatuh">0</h2>
                </div>
            </div>
        </div>
    </div>

    {{-- Progress status dan aktivitas project --}}
    <div class="row">
        <div class="col-xl-4 col-md-6">
            <div class="card mb-3">
                <div class="card-header bg-info text-white">Progress Status</div>
                <div class="card-body" id="progress-status-container">
                    <div class="text-center">
                        <i class="fal fa-spinner fa-spin"></i>
                        <p>Memuat...</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-8 col-md-6">
            <div class="card mb-3">
                <div class="card-header bg-secondary text-white">Aktivitas Project</div>
                <div class="card-body" id="aktivitas-project-container">
                    <div class="text-center">
                        <i class="fal fa-spinner fa-spin"></i>
                        <p>Memuat...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Project List -->
    <div class="modal fade" id="modalProjectList" tabindex="-1" role="dialog" aria-labelledby="projectListModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="projectListModalLabel"><i class="fal fa-table"></i> Daftar Project</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true"><i class="fal fa-times"></i></span>
            </button>
          </div>
          <div class="modal-body p-0">
            <div class="table-responsive">
              <table class="table table-bordered table-hover mb-0" id="projectListTable">
                <thead class="thead-light">
                  <tr>
                    <th>No</th>
                    <th>Nama Proyek</th>
                    <th>Project Owner</th>
                    <th>Tanggal Mulai/Selesai</th>
                    <th>PIC</th>
                    <th>Progress</th>
                  </tr>
                </thead>
                <tbody>
                  <!-- JS inject rows here -->
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
@stop

@push('scripts')
<script>
    function updateTime() {
        const now = new Date();
        const timeString = now.toLocaleTimeString('id-ID', { hour12: false });
        document.getElementById('current-time').textContent = timeString;
    }

    setInterval(updateTime, 1000);
    updateTime();

    function loadDashboardData() {
        $.ajax({
            url: "{{ site_url('/dashboard/summary') }}",
            method: "GET",
            dataType: "json",
            success: function(res) {
                if (res.status === 200) {
                    const data = res.data;

                    // Update total project
                    $('#total-project').text(data.keseluruhan.total_project ?? 0);

                    // Update deadline status
                    $('#project-belum-jatuh').text(data.deadline.belum_jatuh_tempo ?? 0);
                    $('#project-sudah-jatuh').text(data.deadline.sudah_jatuh_tempo ?? 0);

                    // Progress Status
                    let progressHtml = '';
                    const progressLabels = {
                        'belum_terlaksana': { label: 'Belum Terlaksana', color: 'secondary' },
                        'dalam_proses': { label: 'Dalam Proses', color: 'primary' },
                        'selesai': { label: 'Selesai', color: 'success' },
                        'ditahan': { label: 'Ditahan', color: 'warning' },
                        'ditunda': { label: 'Ditunda', color: 'info' },
                        'diturunkan': { label: 'Diturunkan', color: 'danger' },
                    };

                    for (const key in progressLabels) {
                        const val = data.progress[key] ?? 0;
                        const config = progressLabels[key];
                        progressHtml += `
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span><strong>${config.label}:</strong></span>
                                <span class="badge badge-${config.color}">${val}</span>
                            </div>`;
                    }
                    $('#progress-status-container').html(progressHtml);

                    // Aktivitas Project
                    let aktivitasHtml = '';
                    const aktivitasData = [
                        { 
                            key: 'belum_sama_sekali', 
                            label: 'Belum Sama Sekali', 
                            percent: '0%',
                            color: 'light'
                        },
                        { 
                            key: 'administrasi', 
                            label: 'Administrasi', 
                            percent: '1-10%',
                            color: 'secondary'
                        },
                        { 
                            key: 'konsep_pengembangan_fitur_belum_selesai', 
                            label: 'Konsep Pengembangan Fitur Belum Selesai', 
                            percent: '11-20%',
                            color: 'info'
                        },
                        { 
                            key: 'konsep_pengembangan_fitur_telah_selesai', 
                            label: 'Konsep Pengembangan Fitur Telah Selesai', 
                            percent: '21-30%',
                            color: 'primary'
                        },
                        { 
                            key: 'proses_development', 
                            label: 'Proses Development', 
                            percent: '31-70%',
                            color: 'warning'
                        },
                        { 
                            key: 'proses_sit', 
                            label: 'Proses SIT', 
                            percent: '71-80%',
                            color: 'info'
                        },
                        { 
                            key: 'proses_uat', 
                            label: 'Proses UAT', 
                            percent: '81-90%',
                            color: 'primary'
                        },
                        { 
                            key: 'penyesuaian_catatan_dan_pengembangan_fitur_selesai', 
                            label: 'Penyesuaian Catatan dan Pengembangan Fitur Selesai', 
                            percent: '91-100%',
                            color: 'success'
                        }
                    ];

                    aktivitasData.forEach(item => {
                        const count = data.aktivitas[item.key] ?? 0;
                        aktivitasHtml += `
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div>
                                    <strong>${item.label}</strong>
                                    <br>
                                    <small class="text-muted">${item.percent}</small>
                                </div>
                                <span class="badge badge-${item.color}">${count}</span>
                            </div>`;
                    });

                    $('#aktivitas-project-container').html(aktivitasHtml);

                } else {
                    toastr.error('Gagal memuat data dashboard');
                    showErrorState();
                }
            },
            error: function(xhr, status, error) {
                console.error('Dashboard error:', error);
                toastr.error('Terjadi kesalahan saat mengambil data dashboard');
                showErrorState();
            }
        });
    }

    function showErrorState() {
        const errorHtml = `
            <div class="text-center text-muted">
                <i class="fal fa-exclamation-triangle fa-2x"></i>
                <p class="mt-2">Gagal memuat data</p>
            </div>`;

        $('#progress-status-container').html(errorHtml);
        $('#aktivitas-project-container').html(errorHtml);
    }

    // Handler interaktif semua kartu
    $('#card-total-project').on('click', function() {
        $('#modalProjectList').modal('show');
        loadProjectList('all');
    });
    $('#card-belum-jatuh').on('click', function() {
        $('#modalProjectList').modal('show');
        loadProjectList('belum-jatuh');
    });
    $('#card-sudah-jatuh').on('click', function() {
        $('#modalProjectList').modal('show');
        loadProjectList('sudah-jatuh');
    });
    $('#card-triwulan-I').on('click', function() {
        $('#modalProjectList').modal('show');
        loadProjectList('triwulan-I');
    });
    $('#card-triwulan-II').on('click', function() {
        $('#modalProjectList').modal('show');
        loadProjectList('triwulan-ii');
    });
    $('#card-triwulan-III').on('click', function() {
        $('#modalProjectList').modal('show');
        loadProjectList('triwulan-iii');
    });
    $('#card-triwulan-IV').on('click', function() {
        $('#modalProjectList').modal('show');
        loadProjectList('triwulan-iv');
    });

    function loadProjectList(type) {
        $.ajax({
            url: "{{ site_url('/dashboard/list-projects') }}",
            method: "GET",
            data: { filter: type },
            dataType: "json",
            success: function(res) {
                let rows = '';
                if(res.status === 200 && res.data.length > 0){
                    res.data.forEach(item => {
                        rows += `
                            <tr>
                                <td>${item.no}</td>
                                <td>${item.nama_proyek}</td>
                                <td>${item.project_owner}</td>
                                <td>${item.tanggal_mulai} s.d. ${item.tanggal_selesai}</td>
                                <td>${item.pic}</td>
                                <td>${item.progress}</td>
                            </tr>`;
                    });
                } else {
                    rows = `<tr><td colspan="6" class="text-center text-muted">Tidak ada data</td></tr>`;
                }
                $('#projectListTable tbody').html(rows);
            },
            error: function(){
                $('#projectListTable tbody').html(`<tr><td colspan="6" class="text-center text-danger">Gagal memuat data</td></tr>`);
            }
        });
    }

    $(document).ready(function() {
        loadDashboardData();
        setInterval(loadDashboardData, 300000);
    });
</script>
@endpush
