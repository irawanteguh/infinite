<?php

namespace Modules\Ics\Models;

use CodeIgniter\Model;

class MastermdcModel extends Model{
    protected $DBGroup = 'default';

    function datamastersourcedocument(){
        $query = "
                    select a.id, title
                    from dt01_source_document a
                ";

        $recordset = $this->db->query($query);
        return $recordset->getResult();
    }

    function datamastermdc(){
        $query = "
                    SELECT 
                        a.*,
                        CONCAT(b.title, ', Version : ', b.version) AS titlereferensi,
                        b.publisher AS publisherreferensi
                    FROM dt01_mdc a
                    LEFT JOIN dt01_source_document b 
                        ON b.id = a.document_id;
                ";

        $recordset = $this->db->query($query);
        return $recordset->getResult();
    }

    function insertmastermdc($data){
        return $this->db->table('dt01_mdc')->insert($data);
    }
    
}