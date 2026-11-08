<?php

namespace Modules\Developer\Models;

use CodeIgniter\Model;

class OrganizationModel extends Model{
    protected $DBGroup = 'default';

    function masterorganization(){
        $query =
                "
                    select a.org_id, header_id, org_name, kode_rs, kode_ppk, website, email, address, holding, user_id, active, created_by, date_format(a.created_date, '%d.%m.%Y %H:%i:%s')dibuattgl,
                        (select name from dt01_gen_user_data where user_id=a.user_id)pic,
                        (select email from dt01_gen_user_data where user_id=a.user_id)emailpic,
                        (select name from dt01_gen_user_data where user_id=a.created_by)dibuatoleh
                    from dt01_gen_organization_ms a
                    order by holding asc, header_id asc, org_name asc
                ";

        $recordset = $this->db->query($query);
        return $recordset->getResult();
    }

    function headerorganizations(){
        $query =
                "
                    select a.org_id, org_name, holding
                    from dt01_gen_organization_ms a
                    where a.active = '1'
                    and   a.holding in ('A', 'B')
                    order by org_name asc
                ";

        $recordset = $this->db->query($query);
        return $recordset->getResult();
    }

    function getorganization($orgid){
        return $this->db->table('dt01_gen_organization_ms')->where('org_id', $orgid)->get()->getRow();
    }

    function checkusername($username){
        return $this->db->table('dt01_gen_user_data')
            ->where('username', $username)
            ->where('active', '1')
            ->get()
            ->getRow();
    }

    function insertorganization($data){
        return $this->db->table('dt01_gen_organization_ms')->insert($data);
    }

    function insertuser($data){
        return $this->db->table('dt01_gen_user_data')->insert($data);
    }

    public function updatemasterorganization($orgid, $data){
        return $this->db->table('dt01_gen_organization_ms')->where('org_id', $orgid)->update($data);
    }
}