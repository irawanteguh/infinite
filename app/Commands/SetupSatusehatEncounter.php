<?php
namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Database;
use Modules\Satusehat\Database\CreateEncounterTransactions;

class SetupSatusehatEncounter extends BaseCommand
{
    protected $group = 'SATUSEHAT';
    protected $name = 'satusehat:setup-encounter';
    protected $description = 'Membuat tabel transaksi pendaftaran Encounter.';
    public function run(array $params)
    {
        $db = Database::connect();
        (new CreateEncounterTransactions(Database::forge($db)))->up();
        (new \Modules\Satusehat\Database\CreateBridgingLog(Database::forge($db)))->up();
        CLI::write(CreateEncounterTransactions::TABLE . ' dan log bridging tersedia.', 'green');
    }
}
