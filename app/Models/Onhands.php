<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Onhands extends Model
{
   use SoftDeletes;
    protected $table            = 'onhands';
    protected $primaryKey       = 'id_proyek';

    protected $fillable = [
        'nama_proyek',
        'project_owner',
        'catatan_disposisi',
        'tanggal_mulai',
        'tanggal_selesai',
        'pic_1',
        'pic_2',
        'pic_3',
        'progress',
        'dokumen_lainnya'
    ];

    public function pic1()
    {
        return $this->belongsTo(\App\Models\User::class, 'pic_1');
    }
    public function pic2()
    {
        return $this->belongsTo(\App\Models\User::class, 'pic_2');
    }

    public function pic3()
    {
        return $this->belongsTo(\App\Models\User::class, 'pic_3');
    }

    public function unitKerja()
    {
        return $this->belongsTo(UnitKerja::class, 'project_owner', 'kode');
    }
}
