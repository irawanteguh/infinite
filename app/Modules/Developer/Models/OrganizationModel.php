<?php

namespace Modules\Developer\Models;

use CodeIgniter\Model;

class OrganizationModel extends Model{
    protected $DBGroup = 'default';

    function masterorganization(){
        $query =
                "
                    select a.org_id, header_id, org_name, website, email, address, holding, user_id, active, created_by, date_format(a.created_date, '%d.%m.%Y %H:%i:%s')dibuattgl,
                        (select name from dt01_gen_user_data where user_id=a.user_id)pic,
                        (select email from dt01_gen_user_data where user_id=a.user_id)emailpic,
                        (select name from dt01_gen_user_data where user_id=a.created_by)dibuatoleh
                    from dt01_gen_organization_ms a
                    order by header_id asc, holding desc, org_name asc
                ";

        $recordset = $this->db->query($query);
        return $recordset->getResult();
    }

    public function updatemasterorganization($orgid, $data){
        return $this->db->table('dt01_gen_organization_ms')->where('org_id', $orgid)->update($data);
    }
}