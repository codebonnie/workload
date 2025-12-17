<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Histori extends Model
{
    use SoftDeletes;

    protected $table = 'histori';

    protected $primaryKey = 'id_histori';

    protected $fillable = [
       'id_proyek',
       'catatan_disposisi',
       'catatan_tindak_lanjut',
       'name',
       'role',
       'kegiatan',
       'kode_unit_kerja',
       'waktu',
    ];

}
