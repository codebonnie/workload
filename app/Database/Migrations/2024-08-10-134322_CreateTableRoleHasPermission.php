<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTableRoleHasPermission extends Migration
{
	public function up()
	{
		$this->forge->addField([
			'role_id' => [
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
			],
			'permission_id' => [
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
			],
		]);

		$this->forge->addForeignKey('role_id', 'roles', 'id', '', 'CASCADE');
		$this->forge->addForeignKey('permission_id', 'permissions', 'id', '', 'CASCADE');
		$this->forge->createTable('role_has_permissions');
	}

	public function down()
	{
		$this->forge->dropTable('role_has_permissions');
	}
}
