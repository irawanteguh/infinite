<?php

namespace Modules\RestAPI\Models;

use CodeIgniter\Model;

class ClaimGuardModel extends Model{
    protected $DBGroup = 'default';

    function datamastermdc(){
        $query = "
                    SELECT a.mdc_code, name_en, name_id
                    FROM dt01_ics_mdc a
                ";

        $recordset = $this->db->query($query);
        return $recordset->getResult();
    }
}