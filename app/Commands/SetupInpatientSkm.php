<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Modules\Pe\Config\SkmQuestionnaire;
use Modules\Pe\Models\MasterqusionerModel;

class SetupInpatientSkm extends BaseCommand
{
    protected $group = 'Patient Experience';
    protected $name = 'pe:setup-inpatient-skm';
    protected $description = 'Set up the nine supplied inpatient SKM questions for <group_id> <org_id>.';

    public function run(array $params)
    {
        if (count($params) !== 2) throw new \InvalidArgumentException('Gunakan: pe:setup-inpatient-skm <group_id> <org_id>');
        [$group, $org] = $params;
        $db = db_connect();
        $scope = static function ($table) use ($db, $group, $org) { return $db->table($table)->where('group_id', $group)->where('org_id', $org); };
        $instrument = $scope('dt01_pe_instrument_ms')->where('code', 'PREM_GENERAL_RI')->get()->getRowArray();
        if (!$instrument) throw new \InvalidArgumentException('Instrumen PREM Rawat Inap belum tersedia untuk organisasi ini.');
        if ($scope('dt01_pe_question_ms')->where('instrument_id', $instrument['instrument_id'])->where('answer_options !=', null)->countAllResults()) {
            CLI::write('SKM sudah tersedia; perubahan master dipertahankan.');
            return;
        }
        $model = new MasterqusionerModel();
        $db->transBegin();
        try {
            // Retain prior questions and IDs for historical response JOINs.
            $db->query('UPDATE dt01_pe_question_ms SET active = ?, sort_order = sort_order + 1000
                WHERE instrument_id = ? AND group_id = ? AND org_id = ? ORDER BY sort_order DESC',
                ['0', $instrument['instrument_id'], $group, $org]);
            $name = 'Survei Kepuasan Masyarakat';
            $domain = $scope('dt01_pe_domain_ms')->where('name', $name)->get()->getRowArray();
            if (!$domain) {
                $model->saveDomain($group, $org, ['domain_id' => '', 'name' => $name, 'active' => '1'], 'skm-setup');
                $domain = $scope('dt01_pe_domain_ms')->where('name', $name)->get()->getRowArray();
            }
            foreach (SkmQuestionnaire::QUESTIONS as $index => [$question, $options]) {
                $id = $db->query('SELECT UUID() AS id')->getRow()->id;
                $now = date('Y-m-d H:i:s');
                if (!$db->table('dt01_pe_question_ms')->insert([
                    'question_id' => $id, 'instrument_id' => $instrument['instrument_id'], 'group_id' => $group, 'org_id' => $org,
                    'domain_id' => $domain['domain_id'], 'domain' => $name, 'question' => $question, 'sort_order' => $index + 1,
                    'answer_options' => json_encode($options, JSON_UNESCAPED_UNICODE), 'active' => '1',
                    'created_by' => 'skm-setup', 'updated_by' => 'skm-setup', 'created_at' => $now, 'updated_at' => $now,
                ])) throw new \RuntimeException('Gagal membuat pertanyaan SKM.');
            }
            if ($db->transStatus() === false || !$db->transCommit()) throw new \RuntimeException('Gagal menyimpan SKM.');
            CLI::write('PREM Rawat Inap: 9 pertanyaan SKM, empat pilihan per pertanyaan. Pertanyaan lama dipertahankan sebagai nonaktif.', 'green');
        } catch (\Throwable $e) {
            $db->transRollback();
            throw $e;
        }
    }
}