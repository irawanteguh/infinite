<?php
namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Database;
use Modules\Satusehat\Database\CreatePatientMaster;

class SetupSatusehatPatient extends BaseCommand
{
    protected $group = 'SATUSEHAT';
    protected $name = 'satusehat:setup-patient';
    protected $description = 'Membuat master pasien dan memasukkan 10 data dummy Sandbox untuk organisasi yang ditentukan.';
    protected $usage = 'satusehat:setup-patient <group_id> <org_id>';

    public function run(array $params)
    {
        if (count($params) !== 2) throw new \InvalidArgumentException('Isi group_id dan org_id.');
        [$group, $org] = $params;
        $db = Database::connect();
        if (!$db->table('dt01_gen_user_data')->where(['group_id' => $group, 'org_id' => $org])->countAllResults()) throw new \InvalidArgumentException('Scope organisasi tidak ditemukan.');
        (new CreatePatientMaster(Database::forge($db)))->up();
        $rows = [
            ['9271060312000001', 'Ardianto Putra', 'male', '1992-01-09', 'P02478375538'],
            ['9204014804000002', 'Claudia Sintia', 'female', '1989-11-03', 'P03647103112'],
            ['9104224509000003', 'Elizabeth Dior', 'female', '1976-07-07', 'P00805884304'],
            ['9104223107000004', 'Dr. Alan Bagus Prasetya', 'male', '1977-09-03', 'P00912894463'],
            ['9104224606000005', 'Ghina Assyifa', 'female', '2004-08-21', 'P01654557057'],
            ['9104025209000006', 'Salsabilla Anjani Rizki', 'female', '2001-04-16', 'P02280547535'],
            ['9201076001000007', 'Theodore Elisjah', 'female', '1985-09-18', 'P01836748436'],
            ['9201394901000008', 'Sonia Herdianti', 'female', '1996-06-08', 'P00883356749'],
            ['9201076407000009', 'Nancy Wang', 'female', '1955-10-10', 'P01058967035'],
            ['9210060207000010', 'Syarif Muhammad', 'male', '1988-11-02', 'P02428473601'],
        ];
        $db->transBegin();
        $inserted = 0;
        try {
            foreach ($rows as [$nik, $name, $gender, $birthDate, $ihs]) {
                $data = ['group_id' => $group, 'org_id' => $org, 'nik' => $nik, 'name' => $name, 'gender' => $gender, 'birth_date' => $birthDate, 'ihs_number' => $ihs, 'is_dummy' => '1', 'active' => '1'];
                $builder = $db->table(CreatePatientMaster::TABLE);
                $existing = $builder->where(['group_id' => $group, 'org_id' => $org])
                    ->groupStart()->where('nik', $nik)->orWhere('ihs_number', $ihs)->groupEnd()->get()->getResultArray();
                if ($existing) {
                    if (count($existing) !== 1 || array_diff_assoc($data, $existing[0])) throw new \RuntimeException('Data master sudah ada dengan nilai berbeda untuk IHS ' . $ihs);
                    continue;
                }
                if (!$db->table(CreatePatientMaster::TABLE)->insert($data)) throw new \RuntimeException('Insert pasien gagal.');
                $inserted++;
            }
            if ($db->transStatus() === false || !$db->transCommit()) throw new \RuntimeException('Transaksi database gagal.');
        } catch (\Throwable $e) {
            $db->transRollback();
            throw $e;
        }
        CLI::write(CreatePatientMaster::TABLE . ': ' . $inserted . ' data ditambahkan, 10 data sumber terverifikasi.', 'green');
    }
}
