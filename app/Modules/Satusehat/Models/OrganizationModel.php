<?php
namespace Modules\Satusehat\Models;

use CodeIgniter\Model;
use Modules\Satusehat\Database\CreateOrganizationMaster;

class OrganizationModel extends Model
{
    private function scope(string $group, string $org, string $target): array
    {
        if (!$this->db->tableExists(CreateOrganizationMaster::TABLE)) {
            throw new \DomainException('Jalankan php spark satusehat:setup-organization terlebih dahulu.');
        }
        return ['group_id' => $group, 'org_id' => $org, 'target_hash' => $target];
    }

    public function records(string $group, string $org, string $target): array
    {
        $rows = $this->db->table(CreateOrganizationMaster::TABLE)
            ->where($this->scope($group, $org, $target))->orderBy('organization_id')->get()->getResultArray();
        return array_map(static fn($row) => json_decode($row['resource_json'], true, 512, JSON_THROW_ON_ERROR), $rows);
    }

    public function store(string $group, string $org, string $target, array $resources): void
    {
        $scope = $this->scope($group, $org, $target);
        $this->db->transBegin();
        try {
            foreach ($resources as $resource) {
                if (($resource['resourceType'] ?? '') !== 'Organization' || !preg_match('/^[A-Za-z0-9.-]{1,64}$/D', $resource['id'] ?? '')) {
                    throw new \DomainException('Respons Organization tidak valid.');
                }
                $data = $scope + ['ihs_number' => $resource['id'], 'resource_json' => json_encode($resource, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE), 'synced_at' => gmdate('Y-m-d H:i:s')];
                $sql = $this->db->table(CreateOrganizationMaster::TABLE)->set($data)->getCompiledInsert();
                if ($this->db->DBDriver === 'MySQLi') {
                    $sql .= ' ON DUPLICATE KEY UPDATE resource_json = VALUES(resource_json), synced_at = VALUES(synced_at)';
                } else {
                    $sql .= ' ON CONFLICT(group_id, org_id, target_hash, ihs_number) DO UPDATE SET resource_json = excluded.resource_json, synced_at = excluded.synced_at';
                }
                if (!$this->db->query($sql)) throw new \RuntimeException('Organization gagal disimpan.');
            }
            if (!$this->db->transStatus() || !$this->db->transCommit()) throw new \RuntimeException('Penyimpanan Organization gagal.');
        } catch (\Throwable $e) {
            $this->db->transRollback();
            throw $e;
        }
    }
}
