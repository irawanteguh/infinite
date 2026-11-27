<?php
namespace Modules\Satusehat\Models;

use CodeIgniter\Model;
use Modules\Satusehat\Database\CreateBridgingLog;

class BridgingLogModel extends Model
{
    public function begin(string $group, string $org, string $user, string $environment, string $method, string $url, ?array $body, ?string $transaction): int
    {
        if (!$this->db->table(CreateBridgingLog::TABLE)->insert([
            'group_id' => $group, 'org_id' => $org, 'created_by' => $user, 'environment_id' => $environment,
            'encounter_tx_id' => $transaction, 'method' => $method, 'endpoint' => $url,
            'request_json' => $body === null ? null : json_encode($body, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
            'state' => 'started', 'created_at' => gmdate('Y-m-d H:i:s'),
        ])) throw new \RuntimeException('Log bridging belum dapat disimpan.');
        return (int)$this->db->insertID();
    }

    public function complete(int $id, string $group, string $org, ?array $result, int $duration, ?string $error): void
    {
        $http = $result['status'] ?? null;
        $state = $error !== null ? 'network_error' : (($http >= 200 && $http < 300) ? 'success' : 'http_error');
        $ok = $this->db->table(CreateBridgingLog::TABLE)->where(['bridging_log_id' => $id, 'group_id' => $group, 'org_id' => $org, 'state' => 'started'])
            ->update(['http_status' => $http, 'response_json' => $result === null ? null : json_encode($result['body'], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
                'duration_ms' => $duration, 'state' => $state, 'error_message' => $error, 'completed_at' => gmdate('Y-m-d H:i:s')]);
        if (!$ok || $this->db->affectedRows() !== 1) throw new \RuntimeException('Hasil log bridging belum tersimpan. Periksa status transaksi.');
    }
}
