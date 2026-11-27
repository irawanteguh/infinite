<?php
namespace App\Commands;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
class OptimizeSatusehatRegions extends BaseCommand
{
    protected $group = 'SATUSEHAT';
    protected $name = 'satusehat:optimize-regions';
    protected $description = 'Menambahkan indeks pencarian dan urutan master wilayah.';
    public function run(array $params)
    {
        (new \Modules\Satusehat\Database\OptimizeRegionLookup(\Config\Database::forge()))->up();
        \Modules\Satusehat\Models\RegionModel::clearOptionsCache();
        CLI::write('Indeks dan cache master wilayah diperbarui.', 'green');
    }
}
