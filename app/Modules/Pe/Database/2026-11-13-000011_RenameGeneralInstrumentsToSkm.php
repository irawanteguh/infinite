<?php

namespace Modules\Pe\Database;

use CodeIgniter\Database\Migration;

class RenameGeneralInstrumentsToSkm extends Migration
{
    private const NAMES = [
        'PREM_GENERAL_IGD' => ['PREM IGD', 'SKM IGD'],
        'PREM_GENERAL_RJ' => ['PREM Rawat Jalan', 'SKM Rawat Jalan'],
        'PREM_GENERAL_RI' => ['PREM Rawat Inap', 'SKM Rawat Inap'],
    ];

    public function up()
    {
        foreach (self::NAMES as $code => [$old, $new]) {
            $this->db->table('dt01_pe_instrument_ms')->where('code', $code)->where('name', $old)->update(['name' => $new]);
        }
    }

    public function down()
    {
        foreach (self::NAMES as $code => [$old, $new]) {
            $this->db->table('dt01_pe_instrument_ms')->where('code', $code)->where('name', $new)->update(['name' => $old]);
        }
    }
}