<?php
namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class SetupUserAccess extends BaseCommand
{
    protected $group = 'Human Resource';
    protected $name = 'hr:setup-access';
    protected $description = 'Prepare module access storage and appoint an explicitly named access manager.';
    protected $usage = 'hr:setup-access <username>';

    public function run(array $params)
    {
        $username = $params[0] ?? '';
        if ($username === '') { CLI::error('Sebutkan username pengelola akses.'); return EXIT_ERROR; }
        $db = \Config\Database::connect();
        $rows = $db->table('dt01_gen_user_data')->select('user_id,group_id,org_id,suspend')->where('username',$username)->where('active','1')->get()->getResultArray();
        if (count($rows)!==1 || in_array(strtoupper((string)$rows[0]['suspend']),['Y','1'],true)) {
            CLI::error('Username harus merujuk tepat satu pengguna aktif dan tidak disuspend.'); return EXIT_ERROR;
        }
        $user = $rows[0];
        require_once APPPATH.'Modules/Hr/Database/2026-11-13-000001_CreateUserModuleAccess.php';
        (new \Modules\Hr\Database\CreateUserModuleAccess())->up();
        $existing = $db->table('dt01_gen_access_manager')->where('group_id',$user['group_id'])->where('org_id',$user['org_id'])->where('user_id',$user['user_id'])->countAllResults();
        if (!$existing && !$db->table('dt01_gen_access_manager')->insert([
            'group_id'=>$user['group_id'],'org_id'=>$user['org_id'],'user_id'=>$user['user_id'],'created_by'=>$user['user_id'],'created_at'=>date('Y-m-d H:i:s'),
        ])) { CLI::error('Gagal menyimpan pengelola akses.'); return EXIT_ERROR; }
        CLI::write('Penyimpanan akses siap. Pengelola: '.$username.'. Pengguna tanpa izin modul hanya dapat membuka welcome.','green');
        return EXIT_SUCCESS;
    }
}
