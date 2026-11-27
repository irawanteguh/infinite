<?php
namespace App\Commands;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Database;
use Modules\Satusehat\Database\CreateRegionMaster;
use Modules\Satusehat\Libraries\PatientRegions;
class ImportSatusehatRegions extends BaseCommand
{
    protected $group = 'SATUSEHAT';
    protected $name = 'satusehat:import-regions';
    protected $description = 'Impor CSV kode wilayah tervalidasi dari workbook Kemendagri.';
    protected $usage = 'satusehat:import-regions <csv> <source.xlsx>';
    public function run(array $params)
    {
        if (count($params) !== 2 || !is_readable($params[0]) || !is_readable($params[1])) throw new \InvalidArgumentException('Isi CSV dan workbook sumber yang dapat dibaca.');
        $file = fopen($params[0], 'r');
        if (fgetcsv($file) !== ['code', 'parent_code', 'level', 'name', 'code_system']) throw new \InvalidArgumentException('Header CSV tidak valid.');
        $rows = []; $counts = [];
        $source = basename($params[1]); $sha = hash_file('sha256', $params[1]);
        while (($row = fgetcsv($file)) !== false) {
            if (count($row) !== 5) throw new \InvalidArgumentException('Baris CSV tidak valid.');
            [$code, $parent, $level, $name, $system] = $row;
            $definition = PatientRegions::LEVELS[$level] ?? null;
            if (!$definition || !preg_match('/^\d{' . $definition[2] . '}$/D', $code) || strlen($name) > 255 || strlen($system) > 255) throw new \InvalidArgumentException('Kode atau nama wilayah tidak valid: ' . $code);
            if ($definition[1] === 0 ? $parent !== '' : (!preg_match('/^\d{' . $definition[1] . '}$/D', $parent) || strpos($code, $parent) !== 0)) throw new \InvalidArgumentException('Induk wilayah tidak valid: ' . $code);
            if (isset($rows[$code])) throw new \InvalidArgumentException('Kode wilayah duplikat: ' . $code);
            $rows[$code] = ['code' => $code, 'parent_code' => $parent === '' ? null : $parent, 'level' => $level, 'name' => $name === '' ? null : $name, 'code_system' => $system, 'source_name' => $source, 'source_sha256' => $sha];
            $counts[$level] = ($counts[$level] ?? 0) + 1;
        }
        fclose($file);
        if (!$rows || count($counts) !== 4) throw new \InvalidArgumentException('Sumber wilayah belum lengkap.');
        foreach ($rows as $row) if ($row['parent_code'] !== null && !isset($rows[$row['parent_code']])) throw new \InvalidArgumentException('Induk tidak ada dalam sumber: ' . $row['code']);
        $db = Database::connect();
        (new CreateRegionMaster(Database::forge($db)))->up();
        (new \Modules\Satusehat\Database\OptimizeRegionLookup(Database::forge($db)))->up();
        $db->transBegin(); $inserted = 0; $updated = 0;
        try {
            foreach (array_chunk($rows, 500) as $batch) {
                $existing = [];
                foreach ($db->table(CreateRegionMaster::TABLE)->whereIn('code', array_column($batch, 'code'))->get()->getResultArray() as $row) $existing[$row['code']] = $row;
                $new = []; $changed = [];
                foreach ($batch as $row) {
                    if (!isset($existing[$row['code']])) $new[] = $row;
                    elseif (array_diff_assoc($row, $existing[$row['code']])) $changed[] = $row;
                }
                if ($new && $db->table(CreateRegionMaster::TABLE)->insertBatch($new, null, 500) === false) throw new \RuntimeException('Insert master wilayah gagal.');
                if ($changed && $db->table(CreateRegionMaster::TABLE)->updateBatch($changed, 'code', 500) === false) throw new \RuntimeException('Update master wilayah gagal.');
                $inserted += count($new); $updated += count($changed);
            }
            $stored = [];
            foreach ($db->table(CreateRegionMaster::TABLE)->where('source_sha256', $sha)->get()->getResultArray() as $row) $stored[$row['code']] = $row;
            foreach ($rows as $row) if (!isset($stored[$row['code']]) || array_diff_assoc($row, $stored[$row['code']])) throw new \RuntimeException('Rekonsiliasi master wilayah gagal.');
            if ($db->transStatus() === false || !$db->transCommit()) throw new \RuntimeException('Transaksi impor gagal.');
        } catch (\Throwable $e) { $db->transRollback(); throw $e; }
        \Modules\Satusehat\Models\RegionModel::clearOptionsCache();
        CLI::write(json_encode(['table' => CreateRegionMaster::TABLE, 'inserted' => $inserted, 'updated' => $updated, 'verified' => count($rows), 'counts' => $counts], JSON_UNESCAPED_UNICODE), 'green');
    }
}
