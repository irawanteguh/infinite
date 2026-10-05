<?php

namespace App\Models;

use CodeIgniter\Model;

class Modelur extends Model{
    protected $DBGroup = 'default';

    function cekdatastatusur($nomorsep){
        $query =
                "
                    select a.sep
                    from dt01_bpjs_ur_dt a
                    where a.sep='".$nomorsep."'
                ";

        $recordset = $this->db->query($query);
        return $recordset->getResult();
    }

    function insertstatusur($data){
        return $this->db->table('dt01_bpjs_ur_dt')->insert($data);
    }

    function updatestatusur($nomorsep, $data){
        return $this->db->table('dt01_bpjs_ur_dt')->where('sep', $nomorsep)->update($data);
    }
    
}