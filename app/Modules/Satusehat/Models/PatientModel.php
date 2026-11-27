<?php
namespace Modules\Satusehat\Models;

use Modules\Satusehat\Database\CreatePatientMaster;

class PatientModel extends PractitionerModel
{
    protected $masterTable = CreatePatientMaster::TABLE;

    public function motherOptions(string $group, string $org, bool $sandbox, bool $dummyOnly = false): array
    {
        $builder = $this->db->table($this->masterTable)->select('nik, name, gender, birth_date, ihs_number')
            ->where(['group_id' => $group, 'org_id' => $org, 'active' => '1'])->where('nik IS NOT NULL', null, false);
        if (!$sandbox || $dummyOnly) $builder->where('is_dummy', $sandbox ? '1' : '0');
        if ($dummyOnly) $builder->where('gender', 'female');
        return array_values(array_filter($builder->orderBy('name')->get()->getResultArray(), static fn($row) => preg_match('/^\d{16}$/D', $row['nik']) === 1));
    }

    public function storeNewborn(string $group, string $org, array $patient, array $submitted, bool $sandbox): void
    {
        $scope = ['group_id' => $group, 'org_id' => $org, 'ihs_number' => $patient['id']];
        $data = $scope + ['nik' => null, 'mother_nik' => $submitted['identifier'][0]['value'],
            'name' => $patient['name'][0]['text'] ?? $submitted['name'][0]['text'],
            'gender' => $patient['gender'] ?? $submitted['gender'], 'birth_date' => $patient['birthDate'] ?? $submitted['birthDate'],
            'is_dummy' => $sandbox ? '1' : '0', 'active' => '1'];
        $existing = $this->db->table($this->masterTable)->where($scope)->get()->getRowArray();
        if ($existing) return;
        if (!$this->db->table($this->masterTable)->insert($data)) throw new \RuntimeException('IHS telah diterima tetapi master belum tersimpan. Perbarui daftar sebelum mencoba kembali.');
    }

    public function toResource(array $row): array
    {
        $resource = parent::toResource($row);
        $resource['resourceType'] = 'Patient';
        if (empty($row['nik'])) $resource['identifier'] = [];
        if (!empty($row['mother_nik'])) $resource['identifier'][] = ['system' => 'https://fhir.kemkes.go.id/id/nik-ibu', 'value' => $row['mother_nik']];
        return $resource;
    }
}
