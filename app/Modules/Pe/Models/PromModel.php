<?php

namespace Modules\Pe\Models;

class PromModel extends PremModel
{
    protected $instrumentType = 'PROM';
    protected $responseHeader = 'dt01_pe_prom_response_hd';
    protected $responseDetail = 'dt01_pe_prom_response_dt';

    public function availableInstruments($group = null, $org = null): array
    {
        $builder = $this->db->table('dt01_pe_instrument_ms i')
            ->select('i.instrument_id, i.name, i.respondent, i.measurement, COUNT(q.question_id) AS question_count')
            ->join('dt01_pe_question_ms q', 'q.instrument_id = i.instrument_id AND q.active = \'1\'')
            ->join('dt01_pe_domain_ms d', 'd.domain_id = q.domain_id AND d.group_id = i.group_id AND d.org_id = i.org_id AND d.active = \'1\'')
            ->where('i.type', 'PROM')->where('i.active', '1');
        if ($group && $org) $builder->where('i.group_id', $group)->where('i.org_id', $org);
        return $builder->groupBy(['i.instrument_id', 'i.name', 'i.respondent', 'i.measurement'])->orderBy('i.name')->get()->getResultArray();
    }

    protected function responseMetadata(array $context): array
    {
        $phase = $context['measurement_phase'] ?? '';
        if (!in_array($phase, ['T0', 'T1'], true)) {
            throw new \InvalidArgumentException('Pilih tahap pengukuran T0 atau T1.');
        }
        return parent::responseMetadata($context) + ['measurement_phase' => $phase];
    }
}
