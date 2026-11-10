<?php

namespace Modules\Risk\Models;

use CodeIgniter\Model;

class RiskMatrixModel extends Model
{
    public function ready(): bool
    {
        return $this->db->tableExists('dt01_risk_matrix_ms');
    }

    public function cells($group, $org): array
    {
        return $this->db->table('dt01_risk_matrix_ms')
            ->select('likelihood, likelihood_label, impact_level, impact_label, score, risk_level, color_hex, requires_action')
            ->where('group_id', $group)->where('org_id', $org)
            ->orderBy('likelihood', 'DESC')->orderBy('impact_level')->get()->getResultArray();
    }
}
