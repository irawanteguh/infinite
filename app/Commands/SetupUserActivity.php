<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Modules\Developer\Libraries\UserActivityMaintenance;

class SetupUserActivity extends BaseCommand
{
    protected $group = 'Developer';
    protected $name = 'developer:setup-userlog';
    protected $description = 'Siapkan tabel timeline aktivitas pengguna.';

    public function run(array $params)
    {
        UserActivityMaintenance::setup();
        CLI::write('Tabel aktivitas pengguna siap.', 'green');
        return EXIT_SUCCESS;
    }
}
