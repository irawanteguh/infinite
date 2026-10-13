<?php

namespace Modules\Additional\Models;

use CodeIgniter\Model;

class ViewMDCModel extends Model{
    protected $DBGroup = 'default';

    function datamastermdc(){
        $query = "
                    select a.id, mdc_code, name_en, name_id, explanation, example, chapter_no, page_start, page_end
                    from dt01_ics_mdc a
                    order by id asc
                ";

        $recordset = $this->db->query($query);
        return $recordset->getResult();
    }
}