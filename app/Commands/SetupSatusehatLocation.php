<?php
namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Modules\Satusehat\Database\CreateLocationMaster;

class SetupSatusehatLocation extends BaseCommand
{
    protected $group = 'SATUSEHAT';
    protected $name = 'satusehat:setup-location';
    protected $description = 'Membuat tabel master Location SATUSEHAT.';

    public function run(array $params)
    {
        (new CreateLocationMaster(\Config\Database::forge()))->up();
        CLI::write('Tabel Location siap.', 'green');
    }
}
