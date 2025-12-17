<?php
namespace App\Database\Seeds;
use Carbon\Carbon;
use App\Libraries\StatusUserEnum;

class UserSeeder extends \CodeIgniter\Database\Seeder
{
	public function run()
	{
		$data = [
			'nomor_absen' => '01299',
			'username' => 'Bonnie',
			'email' => 'bonniegmmtv@gmail.com',
			'name' => 'Pattraphus Borratasuwan',
			'kode_unit_kerja' => '1300',
			'role' => 'SUPER ADMIN',
			'password' => password_hash('Admintsi1*', PASSWORD_BCRYPT),
			'status' => StatusUserEnum::ACTIVE,
			'password_expired' => Carbon::now()->addYear(),
			'created_by' => 'SYSTEM',
		];
		
		$this->db->table('users')->insert($data);
	}
}
