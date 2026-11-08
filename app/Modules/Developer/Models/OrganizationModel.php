<?php

namespace Modules\Developer\Models;

use CodeIgniter\Model;

class OrganizationModel extends Model{
    protected $DBGroup = 'default';

    function masterorganization(){
        $query =
                "
                    select a.org_id, a.header_id, a.org_name, a.kode_rs, a.kode_ppk, a.website, a.email, a.address, a.holding, a.user_id, a.active, a.created_by,
                        parent.org_name parent_name, parent.holding parent_holding,
                        case when parent.holding = 'B' and root.org_name is not null then concat(root.org_name, ' → ', parent.org_name) else parent.org_name end hierarchy_parent_name,
                        case when a.holding in ('H', 'A') then 0
                             when a.holding = 'C' and parent.holding = 'H' then 1
                             when a.holding = 'C' and parent.holding = 'B' then 2
                             when a.holding = 'B' then 1 else 1 end hierarchy_level,
                        coalesce(root.org_id, parent.org_id, a.org_id) hierarchy_root_id,
                        case
                            when a.holding in ('H', 'A') then concat(a.org_name, '|0')
                            when a.holding = 'B' then concat(parent.org_name, '|1|', a.org_name, '|0')
                            when a.holding = 'C' and parent.holding = 'B' then concat(root.org_name, '|1|', parent.org_name, '|1|', a.org_name, '|0')
                            when a.holding = 'C' and parent.holding = 'H' then concat(parent.org_name, '|1|', a.org_name, '|0')
                            else concat(coalesce(root.org_name, parent.org_name, a.org_name), '|', a.org_name)
                        end hierarchy_sort_path,
                        date_format(a.created_date, '%d.%m.%Y %H:%i:%s') dibuattgl,
                        (select name from dt01_gen_user_data where user_id=a.user_id)pic,
                        (select email from dt01_gen_user_data where user_id=a.user_id)emailpic,
                        (select name from dt01_gen_user_data where user_id=a.created_by)dibuatoleh
                    from dt01_gen_organization_ms a
                    left join dt01_gen_organization_ms parent on parent.org_id = a.header_id
                    left join dt01_gen_organization_ms root on root.org_id = parent.header_id
                    order by hierarchy_sort_path asc, hierarchy_root_id asc
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
                    and   a.holding in ('H', 'A', 'B')
                    order by org_name asc
                ";

        $recordset = $this->db->query($query);
        return $recordset->getResult();
    }

    function getorganization($orgid){
        return $this->db->table('dt01_gen_organization_ms')->where('org_id', $orgid)->get()->getRow();
    }

    public function parentOrganizationAllowed($orgid, array $holdings)
    {
        if (!$holdings) return false;
        return $this->db->table('dt01_gen_organization_ms')->where('org_id', $orgid)
            ->where('active', '1')->whereIn('holding', $holdings)->countAllResults() > 0;
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
