<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Modules\Pe\Config\GeneralQuestionnaires;
use Modules\Pe\Models\MasterqusionerModel;

class SetupSkm extends BaseCommand
{
    protected $group = 'Patient Experience';
    protected $name = 'pe:setup-skm';
    protected $description = 'Prepare draft SKM instruments for one organization: <group_id> <org_id>.';

    public function run(array $params)
    {
        if (count($params) !== 2) throw new \InvalidArgumentException('Gunakan: pe:setup-skm <group_id> <org_id>');
        [$group, $org] = $params;
        foreach ([$group, $org] as $value) {
            if (!preg_match('/\A[A-Za-z0-9][A-Za-z0-9_-]{0,63}\z/', $value)) throw new \InvalidArgumentException('Identitas organisasi tidak valid.');
        }
        $db = db_connect();
        if (!$db->table('dt01_pe_instrument_ms')->where('group_id', $group)->where('org_id', $org)->countAllResults()) {
            throw new \InvalidArgumentException('Organisasi belum memiliki master PE. Siapkan organisasi terlebih dahulu.');
        }
        $model = new MasterqusionerModel();
        if (!$model->ready()) throw new \RuntimeException('Jalankan pe:setup terlebih dahulu.');
        $db->transBegin();
        try {
            foreach (GeneralQuestionnaires::SERVICES as $service => $definition) {
                $scope = static function ($table) use ($db, $group, $org) {
                    return $db->table($table)->where('group_id', $group)->where('org_id', $org);
                };
                $instrument = $scope('dt01_pe_instrument_ms')->where('code', $definition['code'])->get()->getRowArray();
                if ($instrument) {
                    CLI::write($definition['name'] . ': sudah tersedia; isian master dipertahankan.');
                    continue;
                }
                $model->saveInstrument($group, $org, [
                    'instrument_id' => '', 'code' => $definition['code'], 'name' => $definition['name'], 'type' => 'SKM',
                    'respondent' => 'Pasien atau pendamping pasien',
                    'measurement' => 'Setelah menerima pelayanan. Draf awal; dapat disesuaikan melalui Master Kuesioner.',
                    'active' => '1',
                ], 'pe-general-setup');
                $instrument = $scope('dt01_pe_instrument_ms')->where('code', $definition['code'])->get()->getRowArray();
                $order = 0;
                foreach (GeneralQuestionnaires::questions($service) as $name => $questions) {
                    $domain = $scope('dt01_pe_domain_ms')->where('name', $name)->get()->getRowArray();
                    if (!$domain) {
                        $model->saveDomain($group, $org, ['domain_id' => '', 'name' => $name, 'active' => '1'], 'pe-general-setup');
                        $domain = $scope('dt01_pe_domain_ms')->where('name', $name)->get()->getRowArray();
                    }
                    foreach ($questions as $question) {
                        $model->saveQuestion($group, $org, [
                            'question_id' => '', 'instrument_id' => $instrument['instrument_id'], 'domain_id' => $domain['domain_id'],
                            'question' => $question, 'sort_order' => ++$order, 'active' => '1',
                            'answer_options' => json_encode(\Modules\Pe\Config\SkmQuestionnaire::QUESTIONS[$order - 1][1], JSON_UNESCAPED_UNICODE),
                        ], 'pe-general-setup');
                    }
                }
                CLI::write($definition['name'] . ': ' . $order . ' pertanyaan draf disiapkan.');
            }
            if ($db->transStatus() === false || !$db->transCommit()) throw new \RuntimeException('Gagal menyiapkan SKM.');
            CLI::write('SKM siap untuk organisasi yang dipilih.', 'green');
        } catch (\Throwable $e) {
            $db->transRollback();
            throw $e;
        }
    }
}