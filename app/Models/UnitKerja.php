<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UnitKerja extends Model
{
	use SoftDeletes;

	protected $fillable =
		[
			'kode',
			'kode_dept',
			'kode_t24',
			'level',
			'type',
			'name',
			'synonym',
			'address',
			'telp'
		];

	public function scopeCabang($query)
	{
		return $query->whereType('cabang');
	}

	public function scopeDivisi($query)
	{
		return $query->whereType('divisi');
	}

	public function scopeDirektur($query)
	{
		return $query->whereType('direktur');
	}
	public function user()
	{
		return $this->hasMany(User::class, 'kode_unit_kerja', 'kode_t24');
	}
}
