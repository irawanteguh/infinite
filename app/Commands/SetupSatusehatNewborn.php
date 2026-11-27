<?php
namespace App\Commands;
use CodeIgniter\CLI\BaseCommand;
class SetupSatusehatNewborn extends BaseCommand
{
    protected $group = 'SATUSEHAT';
    protected $name = 'satusehat:setup-newborn';
    protected $description = 'Menyiapkan master pasien untuk bayi dengan NIK ibu.';
    public function run(array $params)
    {
        $forge = \Config\Database::forge();
        (new \Modules\Satusehat\Database\CreatePatientMaster($forge))->up();
        (new \Modules\Satusehat\Database\AddNewbornPatient($forge))->up();
        (new \Modules\Satusehat\Database\CreateBridgingLog($forge))->up();
        \CodeIgniter\CLI\CLI::write('Master pasien newborn tersedia.', 'green');
    }
}
