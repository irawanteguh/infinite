<?php
namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Database;
use Modules\Satusehat\Database\CreatePractitionerMaster;

class SetupSatusehatPractitioner extends BaseCommand
{
    protected $group = 'SATUSEHAT';
    protected $name = 'satusehat:setup-practitioner';
    protected $description = 'Membuat master Practitioner dan memasukkan 10 data dummy Sandbox untuk organisasi yang ditentukan.';
    protected $usage = 'satusehat:setup-practitioner <group_id> <org_id>';

    public function run(array $params)
    {
        if (count($params) !== 2) throw new \InvalidArgumentException('Isi group_id dan org_id.');
        [$group, $org] = $params;
        $db = Database::connect();
        if (!$db->table('dt01_gen_user_data')->where(['group_id' => $group, 'org_id' => $org])->countAllResults()) throw new \InvalidArgumentException('Scope organisasi tidak ditemukan.');
        (new CreatePractitionerMaster(Database::forge($db)))->up();
        $rows = [
            ['7209061211900001', 'dr. Alexander', 'male', '1994-01-01', '10009880728'],
            ['3322071302900002', 'dr. Yoga Yandika, Sp.A', 'male', '1995-02-02', '10006926841'],
            ['3171071609900003', 'dr. Syarifuddin, Sp.Pd.', 'male', '1988-03-03', '10001354453'],
            ['3207192310600004', 'dr. Nicholas Evan, Sp.B.', 'male', '1986-04-04', '10010910332'],
            ['6408130207800005', 'dr. Dito Arifin, Sp.M.', 'male', '1985-05-05', '10018180913'],
            ['3217040109800006', 'dr. Olivia Kirana, Sp.OG', 'female', '1984-06-06', '10002074224'],
            ['3519111703800007', 'dr. Alicia Chrissy, Sp.N.', 'female', '1982-07-07', '10012572188'],
            ['5271002009700008', 'dr. Nathalie Tan, Sp.PK.', 'female', '1981-08-08', '10018452434'],
            ['3313096403900009', 'Sheila Annisa S.Kep', 'female', '1980-09-09', '10014058550'],
            ['3578083008700010', 'apt. Aditya Pradhana, S.Farm.', 'female', '1980-10-10', '10001915884'],
        ];
        $db->transBegin();
        $inserted = 0;
        try {
            foreach ($rows as [$nik, $name, $gender, $birthDate, $ihs]) {
                $data = ['group_id' => $group, 'org_id' => $org, 'nik' => $nik, 'name' => $name, 'gender' => $gender, 'birth_date' => $birthDate, 'ihs_number' => $ihs, 'is_dummy' => '1', 'active' => '1'];
                $builder = $db->table(CreatePractitionerMaster::TABLE);
                $existing = $builder->where(['group_id' => $group, 'org_id' => $org])
                    ->groupStart()->where('nik', $nik)->orWhere('ihs_number', $ihs)->groupEnd()->get()->getResultArray();
                if ($existing) {
                    if (count($existing) !== 1 || array_diff_assoc($data, $existing[0])) throw new \RuntimeException('Data master sudah ada dengan nilai berbeda untuk IHS ' . $ihs);
                    continue;
                }
                if (!$db->table(CreatePractitionerMaster::TABLE)->insert($data)) throw new \RuntimeException('Insert Practitioner gagal.');
                $inserted++;
            }
            if ($db->transStatus() === false || !$db->transCommit()) throw new \RuntimeException('Transaksi database gagal.');
        } catch (\Throwable $e) {
            $db->transRollback();
            throw $e;
        }
        CLI::write(CreatePractitionerMaster::TABLE . ': ' . $inserted . ' data ditambahkan, 10 data sumber terverifikasi.', 'green');
    }
}
