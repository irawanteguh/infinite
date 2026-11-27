<?php
namespace Modules\Satusehat\Models;

use CodeIgniter\Model;
use Modules\Satusehat\Database\CreateEncounterTransactions;

class EncounterModel extends Model
{
    private function scoped(string $group, string $org)
    {
        return $this->db->table(CreateEncounterTransactions::TABLE)->where(['group_id' => $group, 'org_id' => $org]);
    }

    public function ready(): bool
    {
        return $this->db->tableExists(CreateEncounterTransactions::TABLE)
            && $this->db->tableExists(\Modules\Satusehat\Database\CreateBridgingLog::TABLE);
    }

    public function records(string $group, string $org): array
    {
        return $this->scoped($group, $org)->select('encounter_tx_id, environment_id, registration_no, patient_name, visit_class, state, satusehat_id, updated_at')
            ->orderBy('updated_at', 'DESC')->get()->getResultArray();
    }

    public function record(string $group, string $org, string $id): array
    {
        if (!preg_match('/^[a-f0-9]{32}$/D', $id)) throw new \InvalidArgumentException('Pilih transaksi yang valid.');
        $row = $this->scoped($group, $org)->where('encounter_tx_id', $id)->get()->getRowArray();
        if (!$row) throw new \InvalidArgumentException('Transaksi tidak ditemukan pada organisasi ini.');
        $row['form'] = json_decode($row['registration_data'], true, 512, JSON_THROW_ON_ERROR);
        $row['result'] = $row['response_json'] ? json_decode($row['response_json'], true, 512, JSON_THROW_ON_ERROR) : null;
        unset($row['registration_data'], $row['response_json']);
        return $row;
    }

    public function saveDraft(string $group, string $org, string $user, array $form, ?string $id, int $revision): array
    {
        $now = gmdate('Y-m-d H:i:s');
        if (!$id) {
            $id = bin2hex(random_bytes(16));
            $form['registration_no'] = 'ENC-' . (new \DateTimeImmutable('now', new \DateTimeZone('Asia/Jakarta')))->format('Ymd') . '-' . strtoupper(substr($id, 0, 12));
            $isNew = true;
        } else {
            $existing = $this->record($group, $org, $id);
            $form['registration_no'] = $existing['registration_no'];
            $isNew = false;
        }
        $fields = [
            'environment_id' => $form['environment_id'], 'registration_no' => $form['registration_no'],
            'patient_name' => $form['patient']['name'], 'visit_class' => $form['visit_class'],
            'registration_data' => json_encode($form, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
            'updated_at' => $now, 'updated_by' => $user,
        ];
        if ($isNew) {
            $fields += ['encounter_tx_id' => $id, 'group_id' => $group, 'org_id' => $org, 'state' => 'draft', 'revision' => 1, 'created_by' => $user, 'created_at' => $now];
            if (!$this->db->table(CreateEncounterTransactions::TABLE)->insert($fields)) throw new \RuntimeException('Draft belum tersimpan.');
        } else {
            $this->record($group, $org, $id);
            $ok = $this->scoped($group, $org)->where('encounter_tx_id', $id)->where('revision', $revision)
                ->whereIn('state', ['draft', 'failed'])->set($fields)->set('revision', 'revision + 1', false)->update();
            if (!$ok || $this->db->affectedRows() !== 1) throw new \DomainException('Transaksi telah berubah atau sedang dikirim. Buka ulang transaksi.');
        }
        return $this->record($group, $org, $id);
    }

    public function claim(string $group, string $org, string $id, int $revision, string $hash): array
    {
        $record = $this->record($group, $org, $id);
        if ($record['target_hash'] && $record['target_hash'] !== $hash) throw new \DomainException('Tujuan environment telah berubah. Periksa konfigurasi organisasi.');
        $ok = $this->scoped($group, $org)->where('encounter_tx_id', $id)->where('revision', $revision)->whereIn('state', ['draft', 'failed'])
            ->set(['state' => 'sending', 'target_hash' => $hash, 'updated_at' => gmdate('Y-m-d H:i:s')])
            ->set('revision', 'revision + 1', false)->update();
        if (!$ok || $this->db->affectedRows() !== 1) throw new \DomainException('Transaksi sudah berubah atau dikirim. Muat ulang daftar.');
        return $this->record($group, $org, $id);
    }

    public function claimRoom(string $group, string $org, string $id, int $revision, string $hash, array $pending, string $user): array
    {
        $record = $this->record($group, $org, $id);
        if (!$record['satusehat_id'] || $record['target_hash'] !== $hash) throw new \DomainException('Encounter atau tujuan environment tidak sesuai.');
        if ($record['state'] === 'unknown' && empty($record['form']['pending_room'])) throw new \DomainException('Periksa status pendaftaran terlebih dahulu.');
        $form = $record['form'];
        $form['pending_room'] = $pending;
        $form['room_state'] = 'sending';
        unset($form['room_error']);
        $ok = $this->scoped($group, $org)->where('encounter_tx_id', $id)->where('revision', $revision)->whereIn('state', ['sent', 'unknown'])
            ->set(['state' => 'sending', 'registration_data' => json_encode($form, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE), 'updated_at' => gmdate('Y-m-d H:i:s'), 'updated_by' => $user])
            ->set('revision', 'revision + 1', false)->update();
        if (!$ok || $this->db->affectedRows() !== 1) throw new \DomainException('Transaksi berubah atau sedang diperbarui. Buka ulang transaksi.');
        return $this->record($group, $org, $id);
    }

    public function finish(string $group, string $org, string $id, int $revision, string $state, ?int $http, array $body, ?string $resourceId, ?array $form = null): array
    {
        $fields = [];
        if ($form !== null) $fields['registration_data'] = json_encode($form, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
        $ok = $this->scoped($group, $org)->where('encounter_tx_id', $id)->where('revision', $revision)->whereIn('state', ['sending', 'unknown', 'failed'])
            ->set($fields + ['state' => $state, 'http_status' => $http, 'response_json' => json_encode($body, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
                'satusehat_id' => $resourceId, 'updated_at' => gmdate('Y-m-d H:i:s')])
            ->set('revision', 'revision + 1', false)->update();
        if (!$ok || $this->db->affectedRows() !== 1) throw new \DomainException('Hasil transaksi telah diperbarui. Buka ulang transaksi.');
        return $this->record($group, $org, $id);
    }
}
