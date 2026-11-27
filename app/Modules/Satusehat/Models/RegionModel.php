<?php
namespace Modules\Satusehat\Models;
use CodeIgniter\Model;
use Modules\Satusehat\Database\CreateRegionMaster;
use Modules\Satusehat\Libraries\PatientRegions;
class RegionModel extends Model
{
    // National reference codes shared by organizations, containing no patient data.
    public function options(string $level, string $parent): array
    {
        (new PatientRegions())->query($level, $parent, 1, '');
        $key = 'satusehat-regions-v2-' . hash('sha256', implode('|', [$this->db->DBDriver, $this->db->hostname, $this->db->database, $this->db->DBPrefix, $level, $parent]));
        $cached = cache()->get($key);
        if (is_array($cached)) return $cached;
        $builder = $this->db->table(CreateRegionMaster::TABLE)->select('code, name')->where('level', $level);
        if ($level === 'provinces') $builder->where('parent_code', null);
        else $builder->where('parent_code', $parent);
        $rows = $builder->orderBy('name')->orderBy('code')->get()->getResultArray();
        $result = ['results' => array_map(static fn($row) => ['id' => $row['code'], 'text' => $row['name'] ?? $row['code']], $rows), 'more' => false, 'next' => ''];
        cache()->save($key, $result, 86400);
        return $result;
    }
    public static function clearOptionsCache(): void
    {
        cache()->deleteMatching('satusehat-regions-v2-*');
    }
    public function validateAddress(array $input): array
    {
        $fields = ['province' => 'provinces', 'city_code' => 'cities', 'district' => 'districts', 'village' => 'sub-districts'];
        $codes = [];
        foreach ($fields as $field => $level) {
            $code = $input[$field] ?? null;
            if (!is_string($code) || !preg_match('/^\d{' . PatientRegions::LEVELS[$level][2] . '}$/D', $code)) throw new \InvalidArgumentException('Pilih wilayah alamat lengkap.');
            $codes[] = $code;
        }
        $rows = array_column($this->db->table(CreateRegionMaster::TABLE)->select('code, level, parent_code, name')->whereIn('code', $codes)->get()->getResultArray(), null, 'code');
        $parent = ''; $city = '';
        foreach ($fields as $field => $level) {
            $code = $input[$field] ?? null;
            if (!is_string($code)) throw new \InvalidArgumentException('Pilih wilayah alamat lengkap.');
            $row = $rows[$code] ?? null;
            if (!$row || $row['level'] !== $level || $row['parent_code'] !== ($parent === '' ? null : $parent)) throw new \InvalidArgumentException('Wilayah alamat tidak sesuai master atau wilayah induknya. Pilih ulang alamat.');
            if ($field === 'city_code') $city = $row['name'];
            $parent = $code;
        }
        // Older callers can omit city. Preserve an explicitly supplied address city.
        if (!array_key_exists('city', $input)) $input['city'] = $city ?? '';
        return $input;
    }
}
