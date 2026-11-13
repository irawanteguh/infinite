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
        require_once APPPATH . 'Modules/Pe/Database/2026-11-12-000005_AddVisitDetailsToResponses.php';
        (new \Modules\Pe\Database\AddVisitDetailsToResponses())->up();
        require_once APPPATH . 'Modules/Pe/Database/2026-11-12-000006_NormalizeResponseDetails.php';
        (new \Modules\Pe\Database\NormalizeResponseDetails())->up();
        require_once APPPATH . 'Modules/Pe/Database/2026-11-12-000007_NormalizeResponseHeaders.php';
        (new \Modules\Pe\Database\NormalizeResponseHeaders())->up();
        require_once APPPATH . 'Modules/Pe/Database/2026-11-13-000008_AddTenantScopeToPeTables.php';
        (new \Modules\Pe\Database\AddTenantScopeToPeTables())->up();
        require_once APPPATH . 'Modules/Pe/Database/2026-11-13-000009_AddInpatientRespondentFields.php';
        (new \Modules\Pe\Database\AddInpatientRespondentFields())->up();
        require_once APPPATH . 'Modules/Pe/Database/2026-11-13-000010_AddSurveyOptionsAndFeedback.php';
        (new \Modules\Pe\Database\AddSurveyOptionsAndFeedback())->up();
        require_once APPPATH . 'Modules/Pe/Database/2026-11-13-000011_RenameGeneralInstrumentsToSkm.php';
        (new \Modules\Pe\Database\RenameGeneralInstrumentsToSkm())->up();
        require_once APPPATH . 'Modules/Pe/Database/2026-11-13-000012_SeparateSkmTransactions.php';
        (new \Modules\Pe\Database\SeparateSkmTransactions())->up();
        require_once APPPATH . 'Modules/Pe/Database/2026-11-13-000013_LinkPremToSkm.php';
        (new \Modules\Pe\Database\LinkPremToSkm())->up();
        require_once APPPATH . 'Modules/Pe/Database/2026-11-13-000014_AddRespondentClinic.php';
        (new \Modules\Pe\Database\AddRespondentClinic())->up();
        require_once APPPATH . 'Modules/Pe/Database/2026-11-13-000015_UseSkmInstrumentType.php';
        (new \Modules\Pe\Database\UseSkmInstrumentType())->up();
        CLI::write('Master PE dan tabel jawaban PREM/PROM siap digunakan.', 'green');
    }
}
