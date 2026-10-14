<?php

namespace App\Models;

use CodeIgniter\Model;

class ModelBahv extends Model{
    protected $DBGroup = 'default';

    function insertpendingbahv($data){
        return $this->db->table('dt01_bpjs_pending_klaim')->insert($data);
    }
    
}