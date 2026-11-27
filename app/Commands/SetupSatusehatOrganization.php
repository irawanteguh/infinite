<?php
namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Modules\Satusehat\Database\CreateOrganizationMaster;

class SetupSatusehatOrganization extends BaseCommand
{
    protected $group = 'SATUSEHAT';
    protected $name = 'satusehat:setup-organization';
    protected $description = 'Membuat tabel master Organization SATUSEHAT.';

    public function run(array $params)
    {
        (new CreateOrganizationMaster(\Config\Database::forge()))->up();
        CLI::write('Tabel Organization siap.', 'green');
    }
}
