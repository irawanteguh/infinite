<?php
namespace Modules\Auth\Models;
use CodeIgniter\Model;

class SignModel extends Model{

    public const PASSWORD_MIN_LENGTH = 12;
    public const PASSWORD_MAX_BYTES = 72;
    public const PASSWORD_COMPLEXITY = [
        ['pattern' => '\p{Lu}', 'label' => 'Huruf besar'],
        ['pattern' => '\p{Ll}', 'label' => 'Huruf kecil'],
        ['pattern' => '\p{Nd}', 'label' => 'Angka'],
        ['pattern' => '[\p{P}\p{S}]', 'label' => 'Simbol'],
    ];

    public static function meetsPasswordPolicy(string $password): bool
    {
        if (!mb_check_encoding($password, 'UTF-8') || mb_strlen($password, 'UTF-8') < self::PASSWORD_MIN_LENGTH
            || strlen($password) > self::PASSWORD_MAX_BYTES || strpos($password, "\0") !== false) {
            return false;
        }

        foreach (self::PASSWORD_COMPLEXITY as $rule) {
            if (!preg_match('/' . $rule['pattern'] . '/u', $password)) {
                return false;
            }
        }
        return true;
    }

    function login($username,$password){
        $accounts = $this->db->table('dt01_gen_user_data')
            ->select('user_id, active, password')->where('username', $username)->get()->getResult();
        foreach ($accounts as $account) {
            if ($this->passwordMatches($password, (string) $account->password)) {
                unset($account->password);
                return $account;
            }
        }

        return null;
    }

    public function changeOwnPassword(array $scope, string $current, string $new): bool
    {
        $scope['active'] = '1';
        $table = $this->db->table('dt01_gen_user_data');
        $account = $table->select('password')->where($scope)->get()->getRowArray();
        if (!$account || !$this->passwordMatches($current, (string) $account['password'])) {
            return false;
        }

        $hash = password_hash($new, PASSWORD_BCRYPT);
        if (!is_string($hash) || !$table->where($scope)->where('password', $account['password'])
            ->update(['password' => $hash])) {
            throw new \RuntimeException('Gagal menyimpan password.');
        }
        return $this->db->affectedRows() === 1;
    }

    private function passwordMatches(string $password, string $stored): bool
    {
        if ($password === '') {
            return false;
        }
        $algorithm = password_get_info($stored)['algoName'];
        if ($algorithm === 'bcrypt' && (strlen($password) > 72 || strpos($password, "\0") !== false)) {
            return false;
        }
        if ($algorithm !== 'unknown') {
            return password_verify($password, $stored);
        }

        return hash_equals(strtolower($stored), encodedata($password));
    }

    function datasession($userid){
        $query =
                "
                    select 
                        a.group_id,
                        a.org_id,
                        a.user_id,
                        a.name,
                        a.email,
                        a.department_id,
                        b.trial,
                        b.kode_rs,
                        b.kode_ppk,
                        b.org_name AS organizationname,
                        b.website,
                        b.address,
                        b.email AS organizationemail,
                        c.name AS pimpinan
                    from dt01_gen_user_data a
                    left join dt01_gen_organization_ms b
                        ON b.org_id = a.org_id
                    left join dt01_gen_user_data c
                        ON c.user_id = b.user_id
                    where a.active = '1'
                    and a.user_id = '".$userid."';
                ";

        $recordset = $this->db->query($query);
        $recordset = $recordset->getRow();
        return $recordset;
    }

}
