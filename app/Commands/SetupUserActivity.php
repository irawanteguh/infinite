<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Modules\Developer\Database\AddUserActivityDetails;
use Modules\Developer\Database\CreateUserActivity;

class SetupUserActivity extends BaseCommand
{
    protected $group = 'Developer';
    protected $name = 'developer:setup-userlog';
    protected $description = 'Siapkan tabel timeline aktivitas pengguna.';

    public function run(array $params)
    {
        require_once APPPATH . 'Modules/Developer/Database/2026-11-24-000001_CreateUserActivity.php';
        require_once APPPATH . 'Modules/Developer/Database/2026-11-24-000002_AddUserActivityDetails.php';
        (new CreateUserActivity())->up();
        (new AddUserActivityDetails())->up();
        CLI::write('Tabel aktivitas pengguna siap.', 'green');
        return EXIT_SUCCESS;
    }
}
