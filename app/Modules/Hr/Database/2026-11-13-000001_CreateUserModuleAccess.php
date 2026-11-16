<?php
namespace Modules\Hr\Database;

use CodeIgniter\Database\Migration;

class CreateUserModuleAccess extends Migration
{
    public function up()
    {
        $scope = [
            'group_id' => ['type'=>'VARCHAR','constraint'=>64],
            'org_id' => ['type'=>'VARCHAR','constraint'=>64],
            'user_id' => ['type'=>'VARCHAR','constraint'=>64],
        ];
        $this->forge->addField($scope + [
            'created_at'=>['type'=>'DATETIME'], 'created_by'=>['type'=>'VARCHAR','constraint'=>64],
        ]);
        $this->forge->addKey(['group_id','org_id','user_id'], true);
        $this->forge->createTable('dt01_gen_access_manager', true);

        $this->forge->addField($scope + [
            'modules_id'=>['type'=>'VARCHAR','constraint'=>64],
            'created_at'=>['type'=>'DATETIME'], 'created_by'=>['type'=>'VARCHAR','constraint'=>64],
        ]);
        $this->forge->addKey(['group_id','org_id','user_id','modules_id'], true);
        $this->forge->createTable('dt01_gen_user_module_access', true);

        $this->forge->addField($scope + [
            'audit_id'=>['type'=>'VARCHAR','constraint'=>32],
            'before_modules'=>['type'=>'TEXT'], 'after_modules'=>['type'=>'TEXT'],
            'created_at'=>['type'=>'DATETIME'], 'created_by'=>['type'=>'VARCHAR','constraint'=>64],
        ]);
        $this->forge->addKey('audit_id', true);
        $this->forge->addKey(['group_id','org_id','user_id']);
        $this->forge->createTable('dt01_gen_user_module_access_log', true);
    }

    public function down()
    {
        $this->forge->dropTable('dt01_gen_user_module_access_log', true);
        $this->forge->dropTable('dt01_gen_user_module_access', true);
        $this->forge->dropTable('dt01_gen_access_manager', true);
    }
}
