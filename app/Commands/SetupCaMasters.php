<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Database;
use Modules\Ca\Database\AddPatientDomicile;
use Modules\Ca\Database\AddScreeningScopes;
use Modules\Ca\Database\AddInstrumentScoring;
use Modules\Ca\Database\AddScreeningAnswerType;
use Modules\Ca\Database\CreateScreeningMasters;
use Modules\Ca\Database\CreateScreeningTransactions;

class SetupCaMasters extends BaseCommand
{
    protected $group = 'Skrining Kanker';
    protected $name = 'ca:setup';
    protected $description = 'Membuat tabel master dan transaksi skrining kanker pada database.default.';

    public function run(array $params)
    {
        $db = Database::connect('default');
        (new CreateScreeningMasters(Database::forge($db)))->up();
        (new CreateScreeningTransactions(Database::forge($db)))->up();

        (new AddPatientDomicile(Database::forge($db)))->up();

        (new AddInstrumentScoring(Database::forge($db)))->up();

        (new AddScreeningScopes(Database::forge($db)))->up();

        (new \Modules\Ca\Database\AddPatientContact(Database::forge($db)))->up();
        (new AddScreeningAnswerType(Database::forge($db)))->up();

        $db->resetDataCache();
        foreach (['dt01_ca_instrument_ms', 'dt01_ca_question_ms', CreateScreeningTransactions::HEADER, CreateScreeningTransactions::DETAIL] as $table) {
            if (!$db->tableExists($table)) {
                throw new \RuntimeException('Tabel CA belum tersedia: ' . $table);
            }
            CLI::write($table, 'green');
        }
    }
}
