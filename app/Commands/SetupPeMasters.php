<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class SetupPeMasters extends BaseCommand
{
    protected $group = 'Patient Experience';
    protected $name = 'pe:setup';
    protected $description = 'Prepare PE masters, question domains, and PREM/PROM response tables.';

    public function run(array $params)
    {
        require_once APPPATH . 'Modules/Pe/Database/2026-11-11-000001_CreateQuestionnaireMasters.php';
        $migration = new \Modules\Pe\Database\CreateQuestionnaireMasters();
        $migration->up();
        require_once APPPATH . 'Modules/Pe/Database/2026-11-11-000002_CreateDomainMaster.php';
        (new \Modules\Pe\Database\CreateDomainMaster())->up();
        require_once APPPATH . 'Modules/Pe/Database/2026-11-11-000003_CreatePremResponses.php';
        (new \Modules\Pe\Database\CreatePremResponses())->up();
        require_once APPPATH . 'Modules/Pe/Database/2026-11-11-000004_CreatePromResponses.php';
        (new \Modules\Pe\Database\CreatePromResponses())->up();
        CLI::write('Master PE dan tabel jawaban PREM/PROM siap digunakan.', 'green');
    }
}
