<?php
namespace Modules\Pe\Database;

use CodeIgniter\Database\Migration;
use Modules\Pe\Config\GeneralQuestionnaires;

class UseSkmInstrumentType extends Migration
{
    public function up()
    {
        $this->db->table('dt01_pe_instrument_ms')->whereIn('code', array_column(GeneralQuestionnaires::SERVICES, 'code'))
            ->where('type', 'PREM')->update(['type' => 'SKM']);
    }

    public function down()
    {
        $this->db->table('dt01_pe_instrument_ms')->whereIn('code', array_column(GeneralQuestionnaires::SERVICES, 'code'))
            ->where('type', 'SKM')->update(['type' => 'PREM']);
    }
}
