<?php
namespace Modules\Satusehat\Database;
use CodeIgniter\Database\Migration;
class OptimizeRegionLookup extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists(CreateRegionMaster::TABLE)) return;
        foreach ($this->db->getIndexData(CreateRegionMaster::TABLE) as $index) if ($index->name === 'region_lookup_sort') return;
        $this->db->query('CREATE INDEX region_lookup_sort ON ' . CreateRegionMaster::TABLE . ' (level, parent_code, name, code)');
    }
    public function down() {}
}
