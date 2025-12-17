@extends('layouts.app')

@section('content')
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

<div class="panel">

    <div class="panel-hdr">
        <h2>{{ $title }}</h2>
        <div class="panel-toolbar">
            <button type="button" class="btn btn-success btn-sm waves-effect waves-themed mr-2" onclick="exportToExcel()">
                <i class="fal fa-file-excel mr-1">Ekspor to excel</i>
            </button>

            @if (in_array(strtoupper(session('role')), ['STAFF PTI', 'ASSISTANT MANAGER']))
            <button type="button" class="btn btn-primary btn-sm waves-effect waves-themed" onclick="$('#form-modal').modal('show'); $('#action').val('add');">
                <i class="fal fa-plus mr-1"></i> Tambahkan Project
            </button>
            @endif
        </div>
    </div>

    <div class="panel-container">
        <div class="panel-content">
            <table id="dt-onhands" class="table table-bordered table-hover table-striped w-100">
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

@if (in_array(strtoupper(session('role')), ['STAFF PTI', 'ASSISTANT MANAGER']))
<div class="modal fade" id="form-modal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-lg" style="overflow-y: auto;"> 
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title">
                    <h4><span id="modal-title">Form Project</span></h4>
                </div>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i class="fal fa-times"></i></span>
                </button>
            </div>
            <div class="modal-body pt-0" style="overflow-y: visible;">
                <input type="hidden" id="action" name="action">
                <input type="hidden" id="original_id_proyek" name="original_id_proyek">
                
                <div class="form-row my-3">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label" for="id_proyek">
                                Nomor <span class="text-danger">*</span>
                            </label>
                            <input type="text" id="id_proyek" class="form-control" name="id_proyek" readonly>
                            <small class="form-text text-muted mt-1">
                                <i class="fal fa-info-circle mr-1"></i>Nomor akan otomatis tersedia
                            </small>
                        </div>
                    </div>
                </div>

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
                                <option value="">PILIH PIC 1</option>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="form-row my-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label" for="pic_2">
                                PIC 2
                            </label>
                            <select class="select2 form-control w-100" id="pic_2" name="pic_2">
                                <option value="">PILIH PIC 2</option>
                                @foreach ($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label" for="pic_3">
                                PIC 3
                            </label>
                            <select class="select2 form-control w-100" id="pic_3" name="pic_3">
                                <option value="">PILIH PIC 3</option>
                                @foreach ($users as $user )
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                
                 <div class="form-row my-3">
                    <div class="col-md-12">
                         <div class="form-group">
                            <label class="form-label" for="progress">Progress</label>
                            <select class="select2 form-control w-100" id="progress" name="progress">
                                <option></option>
                            </select>
                         </div>
                    </div>
                </div>

                <!-- Document Upload Section -->
                <div class="form-row my-3">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="form-label" for="dokumen">
                                Upload Dokumen Lainnya (Opsional)
                            </label>
                            <input type="file" id="dokumen" name="dokumen[]" class="form-control-file" multiple accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.zip,.rar">
                            <small class="form-text text-muted mt-2">
                                <i class="fal fa-info-circle mr-1"></i>
                                Anda dapat memilih multiple file sekaligus. Format yang didukung: PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, JPG, PNG, ZIP, RAR
                            </small>
                            <div id="dokumen-preview" class="mt-3"></div>
                        </div>
                    </div>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fal fa-times mr-2"></i>Tutup
                </button>
                <button type="button" class="btn btn-primary" id="btnSubmit" onclick="hitEndPoint()">
                    <i class="fal fa-save mr-2"></i>Simpan
                </button>
            </div>
        </div>
    </div>
</div>
@endif

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
            <div class="modal-body overflow-auto" style="max-height: 70vh">
                <!-- INFORMATION CARD -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fal fa-info-circle mr-2"></i>INFORMASI PROJECT</h5>
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
                                <label class="font-weight-bold">Catatan Disposisi:</label>
                                <p class="border rounded px-3 py-2 bg-light mb-0" id="view_catatan_disposisi">-</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="font-weight-bold">Tanggal Mulai:</label>
                                <p class="border rounded px-3 py-2 bg-light mb-0" id="view_tanggal_mulai">-</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="font-weight-bold">Tanggal Selesai:</label>
                                <p class="border rounded px-3 py-2 bg-light mb-0" id="view_tanggal_selesai">-</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="font-weight-bold">Progress:</label>
                                <p class="border rounded px-3 py-2 bg-light mb-0" id="view_progress">-</p>
                            </div>
                        </div>
                    </div>
                </div>

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

                <!-- DOCUMENT CARD -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fal fa-file mr-2"></i>Dokumen</h5>
                    </div>
                    <div class="card-body">
                        <div id="view-dokumen-container">
                            <p class="text-muted text-center">Tidak ada dokumen</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fal fa-times mr-2"></i>Tutup
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    var csrfName = $('#txt_csrfname').attr('name');
    var csrfHash = $('#txt_csrfname').val();
    let role = "{{ strtoupper(session('role')) }}";

    $(document).ready(function() {
        initTable();
    });

    function exportToExcel() {
        window.location.href = "{{ site_url('/exportToExcel/Onhands') }}";
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
            {data: 'id_proyek', className: "text-center"},
            {data: 'nama_proyek'},
            {data: 'project_owner', className: "text-center"},
            {data: 'catatan_disposisi'},
            {data: 'tanggal_mulai_selesai', className: "text-center"},
            {data: 'pic_1_username', defaultContent: '-', className: "text-center"},
            {data: 'pic_2_username', defaultContent: '-', className: "text-center"},
            {data: "pic_3_username", defaultContent: '-', className: "text-center"},
            {data: 'progress', className: "text-center"},
            {
                data: null,
                className: "text-center",
                orderable: false,
                render: function(data,type,row,meta){
                    const index = meta.row;
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
                        ${['STAFF PTI', 'ASSISTANT MANAGER'].includes(role) ? `
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="javascript:void(0)" onclick="editModal(${index})">
                                <i class="fal fa-edit mr-2"></i> Edit Project
                            </a>
                            <a class="dropdown-item text-danger" href="javascript:void(0)" onclick="deleteData(${index})">
                                <i class="fal fa-trash-alt mr-2"></i> Hapus Project
                        ` : ''} 
                        </div>
                    </div> 
                    `;
                }
            }
        ];

        window.table = $('#dt-onhands').DataTable({
            responsive: true, 
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ site_url('/dataTables/Onhands') }}",
                type: "GET",
                dataType: "json",
                dataSrc: 'data',
                error: handleAjaxError
            },
            columns: columns 
        });
        return window.table;
    }

    function populateEditModal(data) {
        $('#modal-title').text('Form Edit Project');

        $('#action').val('edit');
        $('#original_id_proyek').val(data.id_proyek);
        $('#id_proyek').val(data.id_proyek).prop('readonly', true);
        $('#nama_proyek').val(data.nama_proyek || '');
        $('#catatan_disposisi').val(data.catatan_disposisi || '');
        $('#tanggal_mulai').val(data.tanggal_mulai || '');
        $('#tanggal_selesai').val(data.tanggal_selesai || '');

        // Initialize selects and values
        $.when(
            initProjectOwner(data.project_owner),
            initProgress(data.progress),
            initPIC(data.pic_1, data.pic_2, data.pic_3)
        ).done(function() {
            // After selects initialized, set values
            if (data.project_owner) {
                $('#kode_unit_kerja').val(data.project_owner).trigger('change');
            }
            if (data.pic_1) $('#pic_1').val(data.pic_1).trigger('change');
            if (data.pic_2) $('#pic_2').val(data.pic_2).trigger('change');
            if (data.pic_3) $('#pic_3').val(data.pic_3).trigger('change');
            if (data.progress) $('#progress').val(data.progress).trigger('change');

            $('#form-modal').modal('show');
        });

        $('#dokumen-preview').empty();
        $.ajax({
            url: "{{ site_url('/getDocuments/Onhands') }}",
            type: "POST",
            dataType: "json",
            data: {
                [csrfName]: csrfHash,
                id: data.id_proyek
            },
            success: function(result) {
                updateCsrfToken(result.token);
                if (result.status === 200 && result.data && result.data.length) {
                    var container = $('<div class="list-group"></div>');
                    result.data.forEach(function(file) {
                        var item = $('<div class="list-group-item d-flex justify-content-between align-items-center"></div>');
                        var left = $('<div></div>');
                        left.append('<strong>' + file.name + '</strong><br><small class="text-muted">' + Math.round(file.size/1024) + ' KB</small>');
                        item.append(left);

                        var right = $('<div></div>');
                        right.append('<a class="btn btn-sm btn-info mr-2" href="' + file.url + '" target="_blank"><i class="fal fa-download mr-1"></i>Download</a>');
                        right.append('<label class="mb-0"><input type="checkbox" class="remove-doc" data-path="' + file.path + '"> Hapus</label>');
                        item.append(right);

                        container.append(item);
                    });
                    $('#dokumen-preview').append(container);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading existing documents', error);
            }
        });
    }

    function initProjectOwner(selectedValue = null) {
        let targetId = '#kode_unit_kerja'; 
        
        return $.ajax({
            url: "{{ site_url('/optionsDivOnly/unitKerjaAPI') }}",
            type: "POST", 
            dataType: "json",
            data: {[csrfName]: csrfHash},
            success: function(result){
                updateCsrfToken(result.token);
                let dataDivisiFiltered = [];
                
                if (result.status === 200) {
                    dataDivisiFiltered = result.data.filter(function(item){
                        return (item.text || '').toLowerCase().includes('divisi');
                    });
                } else {
                    toastr["warning"](result.messages || "Data divisi gagal dimuat");
                }

                $.ajax({
                    url: "{{ site_url('/optionsDirOnly/unitKerjaAPI') }}",
                    type: "POST",
                    data:{[csrfName]: csrfHash},
                    success: function(resultDirektur){
                        updateCsrfToken(resultDirektur.token);
                        let dataDirektur= [];
                        if (resultDirektur.status === 200) {
                            dataDirektur = resultDirektur.data;
                        } 

                        const combinedData = dataDivisiFiltered.concat(dataDirektur);

                        // Destroy existing select2 if any
                        if ($(targetId).hasClass('select2-hidden-accessible')) {
                            $(targetId).select2('destroy');
                        }
                        
                        $(targetId).empty(); 
                        $(targetId).select2({
                            dropdownParent: $('#form-modal'),
                            placeholder: "Pilih Project Owner",
                            data: combinedData,
                            width: '100%',
                            matcher: function(params, data) {
                                if (!params.term) return data;
                                if ((data.text || '').toUpperCase().indexOf(params.term.toUpperCase()) > -1) {
                                    return data;
                                }
                                return null;
                            }
                        });

                        if (selectedValue) {
                            $(targetId).val(selectedValue).trigger('change');
                        }
                    }
                })
            },
            error:function(xhr, status, error) {
                console.log(error);
                 handleAjaxError(xhr, status, error);
            }
        });
    }

    function initProgress(){
        return $.ajax({
            url: "{{ site_url('progress/Onhands') }}",
            type: "POST",
            dataType: "json",
            data: {[csrfName]: csrfHash},
            success: function(result) {
                updateCsrfToken(result.token);
                if(result.status === 200) {
                    // Destroy existing select2 if any
                    if ($('#progress').hasClass('select2-hidden-accessible')) {
                        $('#progress').select2('destroy');
                    }
                    
                    $('#progress').empty();
                    $('#progress').select2({
                        dropdownParent: $('#form-modal'),
                        placeholder: "Pilih Progress Saat Ini!",
                        data: result.data,
                        width: '100%'
                    });
                }
            },
            error: function(xhr, status, error) {
                console.log(error);
            }
        })
    }

    function initPIC (pic1Value = null, pic2Value = null, pic3Value= null){
        $('#pic_1, #pic_2, #pic_3').each(function() {
            if ($(this).hasClass('select2-hidden-accessible')) {
                $(this).select2('destroy');
            }
            $(this).select2({
                dropdownParent: $('#form-modal'),
                placeholder: $(this).attr('id') === 'pic_1' ? "Pilih PIC 1" :
                            $(this).attr('id') === 'pic_2' ? "Pilih PIC 2" : "Pilih PIC 3",
                width: '100%'
            });
        });

        if(pic1Value) {
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

    function generateProjectNumber() {
        return $.ajax({
            url: "{{ site_url('generateNumber/Onhands') }}",
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

    @if (in_array(strtoupper(session('role')), ['STAFF PTI', 'ASSISTANT MANAGER']))
    $('#form-modal').on('shown.bs.modal', function() {
        if ($('#action').val() === 'add') {
            $.when(
                generateProjectNumber(),
                initProjectOwner(),
                initProgress(null, true, true), 
                initPIC()                
            ).done(function() {
                // Done
            });
        }
    });

    $('#form-modal').on('hidden.bs.modal', function() {
        resetFormFields();
    });

    function resetFormFields() {
        $('#id_proyek').val('').prop('readonly', false);
        $('#nama_proyek').val('');
        $('#kode_unit_kerja').val(null).trigger('change');
        $('#catatan_disposisi').val('');
        $('#tanggal_mulai').val('change');
        $('#tanggal_selesai').val('change');
        $('#pic_1').val(null).trigger('change');
        $('#pic_2').val(null).trigger('change');
        $('#pic_3').val(null).trigger('change');
        $('#progress').val(null).trigger('change');
        $('#dokumen').val('');
        $('#dokumen-preview').empty();
        $('#kode_unit_kerja').prop('disabled', false);
    }
    @endif

    function viewDetailModal(index) {
        var data = table.row(index).data();

        $('#view_id_proyek').text(data.id_proyek || '-');
        $('#view_nama_proyek').text(data.nama_proyek || '-');
        $('#view_project_owner').text(data.project_owner || '-');
        $('#view_catatan_disposisi').text(data.catatan_disposisi || '-');
        $('#view_tanggal_mulai').text(data.tanggal_mulai || '-');
        $('#view_tanggal_selesai').text(data.tanggal_selesai || '-');
        $('#view_progress').text(data.progress || '-');
        $('#view_pic_1').text(data.pic_1_username || '-');
        $('#view_pic_2').text(data.pic_2_username || '-');
        $('#view_pic_3').text(data.pic_3_username || '-');

        $('#view-detail-modal').modal('show');

        // Load documents
        $.ajax({
            url: "{{ site_url('/getDocuments/Onhands') }}",
            type: "POST",
            dataType: "json",
            data: {
                [csrfName]: csrfHash,
                id: data.id_proyek
            },
            success: function(result) {
                updateCsrfToken(result.token);
                $('#view-dokumen-container').empty();
                
                if (result.status === 200 && result.data && result.data.length) {
                    var container = $('<div class="list-group"></div>');
                    result.data.forEach(function(file) {
                        var item = $('<div class="list-group-item d-flex justify-content-between align-items-center"></div>');
                        var left = $('<div></div>');
                        left.append('<strong>' + file.name + '</strong><br><small class="text-muted">' + Math.round(file.size/1024) + ' KB</small>');
                        item.append(left);

                        var right = $('<div></div>');
                        right.append('<a class="btn btn-sm btn-info" href="' + file.url + '"><i class="fal fa-download mr-1"></i>Download</a>');
                        item.append(right);

                        container.append(item);
                    });
                    $('#view-dokumen-container').append(container);
                } else {
                    $('#view-dokumen-container').html('<p class="text-muted text-center">Tidak ada dokumen</p>');
                }
            },
            error: function(xhr, status, error) {
                $('#view-dokumen-container').html('<p class="text-muted text-center">Gagal memuat dokumen</p>');
                console.error('Error loading documents', error);
            }
        });

        $.ajax({
            url: "{{ site_url('/get/Onhands') }}",
            type: "POST",
            dataType: "json",
            data: {
                [csrfName]: csrfHash,
                id_proyek: data.id_proyek
            },
            success: function(result) {
                updateCsrfToken(result.token);
                if(result.status === 200) {
                    data = result.data;
                    // Additional details can be populated here if needed
                } else {
                    toastr["warning"](result.messages || "Gagal memuat data proyek");
                }
            },
            error: function(xhr, status, error) {
                console.log('Error fetching project details:', error);
                handleAjaxError(xhr, status, error);
            }
        });
    }

    function validateForm() {
        var id_proyek = $('#id_proyek').val();
        var nama_proyek = $('#nama_proyek').val();
        var project_owner = $('#kode_unit_kerja').val();
        var tanggal_mulai = $('#tanggal_mulai').val();
        var tanggal_selesai = $('#tanggal_selesai').val();
        var catatan_disposisi = $('#catatan_disposisi').val();
        var pic_1 = $('#pic_1').val();
        var pic_2 = $('#pic_2').val();
        var pic_3 = $('#pic_3').val();
        var progress = $('#progress').val();

        if (!id_proyek) {toastr["warning"]("Nomor Proyek Tidak Boleh Kosong"); return false; }
        if (!nama_proyek) {toastr["warning"]("Nama Proyek Tidak Boleh Kosong"); return false; }
        if (!project_owner) {toastr["warning"]("Project Owner Tidak Boleh Kosong"); return false; }
        if (!tanggal_mulai) {toastr["warning"]("Tanggal Mulai Tidak Boleh Kosong"); return false; }
        if (!tanggal_selesai) {toastr["warning"]("Tanggal Selesai Tidak Boleh Kosong"); return false; }
        if (!pic_1) {toastr["warning"]("PIC 1 Harus Diisi"); return false; }

        if (new Date(tanggal_mulai) > new Date(tanggal_selesai)) {
            toastr["warning"]("Tanggal Mulai tidak boleh melebihi Tanggal Selesai!")
        }

        return true;
    }

    function submit() {
        if(!validateForm()) return;
        hitEndPoint();
    }

    // Handle document file preview
    $('#dokumen').on('change', function() {
        var previewDiv = $('#dokumen-preview');
        previewDiv.empty();

        var files = this.files;
        if (files.length > 0) {
            var fileList = $('<div class="alert alert-info"></div>');
            fileList.append('<strong><i class="fal fa-file mr-2"></i>File yang dipilih:</strong><ul class="mt-2 mb-0">');

            for (var i = 0; i < files.length; i++) {
                var fileSize = (files[i].size / 1024).toFixed(2);
                var fileName = files[i].name;
                fileList.find('ul').append('<li>' + fileName + ' (' + fileSize + ' KB)</li>');
            }
            fileList.append('</ul>');
            previewDiv.append(fileList);
        }
    });
    
    function editModal(index) {
        var data = table.row(index).data();

        $('#modal-title').text('Form Edit Project');

        $('#action').val('edit');
        $('#original_id_proyek').val(data.id_proyek);
        $('#id_proyek').val(data.id_proyek).prop('readonly', true);
        $('#nama_proyek').val(data.nama_proyek);
        $('#catatan_disposisi').val(data.catatan_disposisi);
        $('#tanggal_mulai').val(data.tanggal_mulai);
        $('#tanggal_selesai').val(data.tanggal_selesai);

        $.ajax({
            url: "{{ site_url('/get/Onhands') }}",
            type: "POST",
            dataType: "json",
            data: {
                [csrfName]: csrfHash,
                id_proyek: data.id_proyek
            },
            success: function(result) {
                updateCsrfToken(result.token);
                if(result.status === 200) {
                    data = result.data;
                    populateEditModal(data);
                } else {
                    toastr["warning"](result.messages || "Gagal memuat data proyek");
                }
            },
            error: function(xhr, status, error) {
                console.log('Error fetching project details:', error);
                handleAjaxError(xhr, status, error);
            }
        });
    }

    function hitEndPoint(){
        var endpoint;
        var formData = new FormData();
        
        // Add text fields
        formData.append(csrfName, csrfHash);
        formData.append('id_proyek', $('#id_proyek').val());
        formData.append('nama_proyek', $('#nama_proyek').val());
        formData.append('project_owner', $('#kode_unit_kerja').val());
        formData.append('tanggal_mulai', $('#tanggal_mulai').val());
        formData.append('tanggal_selesai', $('#tanggal_selesai').val());
        formData.append('catatan_disposisi', $('#catatan_disposisi').val());
        formData.append('pic_1', $('#pic_1').val());
        formData.append('pic_2', $('#pic_2').val());
        formData.append('pic_3', $('#pic_3').val());
        formData.append('progress', $('#progress').val());

        // Add files
        var fileInput = document.getElementById('dokumen');
        if (fileInput.files.length > 0) {
            for (var i = 0; i < fileInput.files.length; i++) {
                formData.append('dokumen[]', fileInput.files[i]);
            }
        }

        // Add removed existing files if any (from edit modal)
        if ($('#action').val() === 'edit') {
            $('.remove-doc:checked').each(function() {
                formData.append('remove_files[]', $(this).data('path'));
            });
        }

        if($('#action').val() == 'add') {
            endpoint = "{{ site_url('/post/Onhands') }}"; 
        } else if ($('#action').val() == 'edit') {
            endpoint = "{{ site_url('/edit/Onhands') }}";
            formData.append('original_id_proyek', $('#original_id_proyek').val());
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
            data: formData,
            processData: false,
            contentType: false,
            success: function(result) {
                updateCsrfToken(result.token);
                
                $('#form-modal').modal('hide'); 
                $('#btnSubmit').attr('disabled', false);
                $('#btnSubmit').html('<i class="fal fa-save mr-2"></i>Simpan');

                if(result.status === 200){
                    toastr["success"](result.messages || result.message);
                    $('#dt-onhands').DataTable().ajax.reload(); 
                } else {
                    toastr["warning"](result.messages || result.message);
                }
            },

            error: function(xhr, status, error) {
                $('#form-modal').modal('hide');
                $('#btnSubmit').attr('disabled', false);
                $('#btnSubmit').html('<i class="fal fa-save mr-2"></i>Simpan');
                handleAjaxError(xhr, status,error);
            }
        });
    }

    function deleteData(index) {
        var data = table.row(index).data();
        toastr.warning(
            "<button type='button' id='confirmationButtonYes' class='btn btn-light mt-2 ml-2'>Hapus Data</button",
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

    function hitEndPointDelete(id) {
        $.ajax({
            url: "{{ site_url('/delete/Onhands') }}",
            type: "POST",
            dataType: "json",
            data: {
                [csrfName]: csrfHash,
                id: id,
            },
            success: function(result){
                reloadTable();
                if(result.status === 200){
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
        $('#dt-onhands').DataTable().ajax.reload();
    }
</script>
@endpush