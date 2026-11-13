<?php

namespace Modules\Pe\Models;

use CodeIgniter\Model;

class MasterqusionerModel extends Model
{
    protected $DBGroup = 'default';
    private const INSTRUMENTS = 'dt01_pe_instrument_ms';
    private const QUESTIONS = 'dt01_pe_question_ms';
    private const DOMAINS = 'dt01_pe_domain_ms';

    public function ready(): bool
    {
        return $this->db->tableExists(self::INSTRUMENTS) && $this->db->tableExists(self::QUESTIONS)
            && $this->db->tableExists(self::DOMAINS) && $this->db->fieldExists('domain_id', self::QUESTIONS);
    }

    private function scoped($group, $org)
    {
        return $this->db->table(self::INSTRUMENTS)->where('group_id', $group)->where('org_id', $org);
    }

    public function masters($group, $org): array
    {
        return [
            'instruments' => $this->scoped($group, $org)->orderBy('type')->orderBy('name')->get()->getResultArray(),
            'domains' => $this->db->table(self::DOMAINS)->where('group_id', $group)->where('org_id', $org)->orderBy('name')->get()->getResultArray(),
            'questions' => $this->db->table(self::QUESTIONS . ' q')->select('q.question_id, q.instrument_id, q.domain_id, d.name AS domain, q.question, q.sort_order, q.active, q.answer_options')
                ->join(self::INSTRUMENTS . ' i', 'i.instrument_id = q.instrument_id AND i.group_id = q.group_id AND i.org_id = q.org_id')
                ->join(self::DOMAINS . ' d', 'd.domain_id = q.domain_id AND d.group_id = i.group_id AND d.org_id = i.org_id', 'left')
                ->where('i.group_id', $group)->where('i.org_id', $org)
                ->orderBy('q.sort_order')->get()->getResultArray(),
        ];
    }

    private function instrument($id, $group, $org): array
    {
        $row = $this->scoped($group, $org)->where('instrument_id', $id)->get()->getRowArray();
        if (!$row) {
            throw new \InvalidArgumentException('Instrumen tidak ditemukan pada organisasi ini.');
        }
        return $row;
    }

    public function saveInstrument($group, $org, array $data, $user): void
    {
        if (!in_array($data['type'], ['SKM', 'PREM', 'PROM'], true)) throw new \InvalidArgumentException('Jenis instrumen tidak valid.');
        if (\Modules\Pe\Config\GeneralQuestionnaires::service($data['code']) !== null && $data['type'] !== 'SKM') {
            throw new \InvalidArgumentException('Instrumen SKM harus menggunakan jenis SKM.');
        }
        $id = $data['instrument_id'];
        unset($data['instrument_id']);
        if ($id !== '') {
            $existing = $this->instrument($id, $group, $org);
            if ($existing['type'] !== $data['type']) {
                foreach (['prem', 'prom', 'skm'] as $prefix) {
                    if ($this->db->table('dt01_pe_' . $prefix . '_response_hd')->where('instrument_id', $id)->where('group_id', $group)->where('org_id', $org)->countAllResults()) {
                        throw new \InvalidArgumentException('Jenis instrumen yang sudah memiliki jawaban tidak dapat diubah.');
                    }
                }
            }
        }
        if ($this->scoped($group, $org)->where('code', $data['code'])->where('instrument_id !=', $id)->countAllResults()) {
            throw new \InvalidArgumentException('Kode instrumen sudah digunakan.');
        }
        $data['updated_at'] = date('Y-m-d H:i:s');
        if ($id !== '') {
            $ok = $this->scoped($group, $org)->where('instrument_id', $id)->update($data);
        } else {
            $data += ['instrument_id' => $this->uuid(), 'group_id' => $group, 'org_id' => $org, 'created_by' => $user, 'created_at' => $data['updated_at']];
            $ok = $this->db->table(self::INSTRUMENTS)->insert($data);
        }
        if (!$ok) {
            throw new \RuntimeException('Failed to save instrument.');
        }
    }

    public function saveQuestion($group, $org, array $data, $user): void
    {
        $instrument = $this->instrument($data['instrument_id'], $group, $org);
        $id = $data['question_id'];
        unset($data['question_id']);
        if ($id !== '' && !$this->db->table(self::QUESTIONS)->where('question_id', $id)->where('instrument_id', $instrument['instrument_id'])->where('group_id', $group)->where('org_id', $org)->countAllResults()) {
            throw new \InvalidArgumentException('Pertanyaan tidak ditemukan pada instrumen ini.');
        }
        $domain = $this->db->table(self::DOMAINS)->where('domain_id', $data['domain_id'])
            ->where('group_id', $group)->where('org_id', $org)->get()->getRowArray();
        if (!$domain) {
            throw new \InvalidArgumentException('Domain tidak ditemukan pada organisasi ini.');
        }
        $existing = $id === '' ? null : $this->db->table(self::QUESTIONS)->where('question_id', $id)->where('group_id', $group)->where('org_id', $org)->get()->getRowArray();
        if ($domain['active'] !== '1' && (!$existing || $existing['domain_id'] !== $domain['domain_id'])) {
            throw new \InvalidArgumentException('Pilih domain yang aktif.');
        }
        if ($id === '' && $instrument['type'] === 'SKM') {
            $options = \Modules\Pe\Config\SkmQuestionnaire::QUESTIONS[0][1];
            foreach (\Modules\Pe\Config\SkmQuestionnaire::QUESTIONS as [$question, $choices]) {
                if (mb_strtolower(trim($question)) === mb_strtolower(trim($data['question']))) { $options = $choices; break; }
            }
            $data['answer_options'] = json_encode($options, JSON_UNESCAPED_UNICODE);
        }
        $data['domain'] = $domain['name'];
        $data['group_id'] = $group;
        $data['org_id'] = $org;
        if ($this->db->table(self::QUESTIONS)->where('instrument_id', $instrument['instrument_id'])->where('group_id', $group)->where('org_id', $org)->where('sort_order', $data['sort_order'])->where('question_id !=', $id)->countAllResults()) {
            throw new \InvalidArgumentException('Nomor urut sudah digunakan. Pilih nomor urut lain.');
        }
        $data['updated_by'] = $user;
        $data['updated_at'] = date('Y-m-d H:i:s');
        if ($id !== '') {
            $ok = $this->db->table(self::QUESTIONS)->where('question_id', $id)->where('instrument_id', $instrument['instrument_id'])->where('group_id', $group)->where('org_id', $org)->update($data);
        } else {
            $data += ['question_id' => $this->uuid(), 'created_by' => $user, 'created_at' => $data['updated_at']];
            $ok = $this->db->table(self::QUESTIONS)->insert($data);
        }
        if (!$ok) {
            throw new \RuntimeException('Failed to save question.');
        }
    }

    public function saveDomain($group, $org, array $data, $user): void
    {
        $id = $data['domain_id'];
        unset($data['domain_id']);
        $scope = function () use ($group, $org) {
            return $this->db->table(self::DOMAINS)->where('group_id', $group)->where('org_id', $org);
        };
        if ($id !== '' && !$scope()->where('domain_id', $id)->countAllResults()) {
            throw new \InvalidArgumentException('Domain tidak ditemukan pada organisasi ini.');
        }
        if ($scope()->where('name', $data['name'])->where('domain_id !=', $id)->countAllResults()) {
            throw new \InvalidArgumentException('Nama domain sudah digunakan.');
        }
        $data['updated_at'] = date('Y-m-d H:i:s');
        $data['updated_by'] = $user;
        if ($id !== '') {
            $ok = $scope()->where('domain_id', $id)->update($data);
        } else {
            $data += ['domain_id' => $this->uuid(), 'group_id' => $group, 'org_id' => $org, 'created_by' => $user, 'created_at' => $data['updated_at']];
            $ok = $this->db->table(self::DOMAINS)->insert($data);
        }
        if (!$ok) throw new \RuntimeException('Failed to save domain.');
    }

    private function uuid(): string
    {
        $bytes = random_bytes(16);
        $bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40);
        $bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($bytes), 4));
    }
}
