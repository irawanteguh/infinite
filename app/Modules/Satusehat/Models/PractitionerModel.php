<?php
namespace Modules\Satusehat\Models;

use CodeIgniter\Model;
use Modules\Satusehat\Database\CreatePractitionerMaster;

class PractitionerModel extends Model
{
    protected $masterTable = CreatePractitionerMaster::TABLE;

    public function findForOrganization(string $group, string $org, string $mode, string $identifier, bool $sandbox): ?array
    {
        if (!$this->db->tableExists($this->masterTable)) return null;
        $builder = $this->db->table($this->masterTable)
            ->where(['group_id' => $group, 'org_id' => $org, 'active' => '1'])
            ->where($mode === 'nik' ? 'nik' : 'ihs_number', $identifier);
        if (!$sandbox) $builder->where('is_dummy', '0');
        return $builder->get()->getRowArray();
    }

    public function searchForOrganization(string $group, string $org, string $query, bool $sandbox): array
    {
        if (!$this->db->tableExists($this->masterTable)) return [];
        $builder = $this->db->table($this->masterTable)->where(['group_id' => $group, 'org_id' => $org, 'active' => '1']);
        if (!$sandbox) $builder->where('is_dummy', '0');
        if ($query !== '') $builder->groupStart()->like('name', $query)->orLike('nik', $query)->orLike('ihs_number', $query)->groupEnd();
        return $builder->orderBy('name')->get()->getResultArray();
    }

    public function toResource(array $row): array
    {
        $resource = [
            'resourceType' => 'Practitioner',
            'id' => $row['ihs_number'],
            'identifier' => [
                ['system' => 'https://fhir.kemkes.go.id/id/nik', 'value' => $row['nik']],
            ],
            'name' => [['use' => 'official', 'text' => $row['name']]],
        ];
        if ($row['gender'] !== null && $row['gender'] !== '') $resource['gender'] = $row['gender'];
        if ($row['birth_date'] !== null && $row['birth_date'] !== '') $resource['birthDate'] = $row['birth_date'];
        return $resource;
    }
}
