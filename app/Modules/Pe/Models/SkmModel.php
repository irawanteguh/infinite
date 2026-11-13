<?php

namespace Modules\Pe\Models;

use Modules\Pe\Config\GeneralQuestionnaires;

class SkmModel extends PremModel
{
    protected $responseHeader = 'dt01_pe_skm_response_hd';
    protected $responseDetail = 'dt01_pe_skm_response_dt';
    protected $skmTransactions = true;
    protected $instrumentType = 'SKM';
    public function availableInstruments(string $group, string $org): array
    {
        $result = [];
        foreach (GeneralQuestionnaires::SERVICES as $service => $definition) {
            $instrument = $this->db->table('dt01_pe_instrument_ms')->where('group_id', $group)->where('org_id', $org)
                ->where('type', 'SKM')->where('code', $definition['code'])->where('active', '1')->get()->getRowArray();
            if (!$instrument) continue;
            $count = count($this->questions($instrument['instrument_id']));
            if (!$count) continue;
            $result[$service] = $instrument + $definition + ['service' => $service, 'question_count' => $count];
        }
        return $result;
    }

    public function resolveInstrument($id = null, $group = null, $org = null): ?array
    {
        if (!$group || !$org || !$id) return null;
        $instrument = parent::resolveInstrument($id, $group, $org);
        return $instrument && GeneralQuestionnaires::service($instrument['code']) !== null ? $instrument : null;
    }
}