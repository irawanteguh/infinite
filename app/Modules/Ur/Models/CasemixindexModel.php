<?php

namespace Modules\Ur\Models;

use CodeIgniter\Model;

class CasemixindexModel extends Model{
    protected $DBGroup = 'default';

    function periode(){
        $query = "
                    with recursive periode as (
                        select 2014 as periode
                        union all
                        select periode + 1
                        from periode
                        where periode < year(curdate())
                    )
                    select periode
                    from periode
                    order by periode desc

                ";

        $recordset = $this->db->query($query);
        return $recordset->getResult();
    }

    function datakelompokkasus($koders,$periode){
        $query = "
                    select 
                        a.id_cg,
                        a.name_case_group,
                        a.notes,

                        sum(case when month(b.discharge_date) = 1  then 1 else 0 end) as jan,
                        sum(case when month(b.discharge_date) = 2  then 1 else 0 end) as feb,
                        sum(case when month(b.discharge_date) = 3  then 1 else 0 end) as mar,
                        sum(case when month(b.discharge_date) = 4  then 1 else 0 end) as apr,
                        sum(case when month(b.discharge_date) = 5  then 1 else 0 end) as mei,
                        sum(case when month(b.discharge_date) = 6  then 1 else 0 end) as jun,
                        sum(case when month(b.discharge_date) = 7  then 1 else 0 end) as jul,
                        sum(case when month(b.discharge_date) = 8  then 1 else 0 end) as agu,
                        sum(case when month(b.discharge_date) = 9  then 1 else 0 end) as sep,
                        sum(case when month(b.discharge_date) = 10 then 1 else 0 end) as okt,
                        sum(case when month(b.discharge_date) = 11 then 1 else 0 end) as nov,
                        sum(case when month(b.discharge_date) = 12 then 1 else 0 end) as des

                    from dt01_inacbg_casegroup_ms a

                    left join dt01_bpjs_ur_dt b 
                        on b.cg = a.id_cg
                        and   b.kode_rs='".$koders."'
                        and   year(b.discharge_date) = " . $periode."

                    group by 
                        a.id_cg,
                        a.name_case_group,
                        a.notes

                    order by 
                        cast(a.id_cg as unsigned) asc;
                ";

        $recordset = $this->db->query($query);
        return $recordset->getResult();
    }

    function datacmg($koders,$periode){
        $query = "
                    select 
                        a.group_code,
                        a.group_name,
                        a.description,

                        sum(case when month(b.discharge_date) = 1  then 1 else 0 end) as jan,
                        sum(case when month(b.discharge_date) = 2  then 1 else 0 end) as feb,
                        sum(case when month(b.discharge_date) = 3  then 1 else 0 end) as mar,
                        sum(case when month(b.discharge_date) = 4  then 1 else 0 end) as apr,
                        sum(case when month(b.discharge_date) = 5  then 1 else 0 end) as mei,
                        sum(case when month(b.discharge_date) = 6  then 1 else 0 end) as jun,
                        sum(case when month(b.discharge_date) = 7  then 1 else 0 end) as jul,
                        sum(case when month(b.discharge_date) = 8  then 1 else 0 end) as agu,
                        sum(case when month(b.discharge_date) = 9  then 1 else 0 end) as sep,
                        sum(case when month(b.discharge_date) = 10 then 1 else 0 end) as okt,
                        sum(case when month(b.discharge_date) = 11 then 1 else 0 end) as nov,
                        sum(case when month(b.discharge_date) = 12 then 1 else 0 end) as des

                    from dt01_inacbg_cmg_ms a

                    left join dt01_bpjs_ur_dt b 
                        on b.cmg = a.group_code
                        and   b.kode_rs='".$koders."'
                        and   year(b.discharge_date) = " . $periode."

                    group by 
                        a.group_Code,
                        a.group_name,
                        a.description

                    order by group_code asc;
                ";

        $recordset = $this->db->query($query);
        return $recordset->getResult();
    }

    function dataseveritylevel($koders,$periode){
        $query = "
                    SELECT a.sl,
                        SUM(CASE WHEN MONTH(a.DISCHARGE_DATE) = 1  THEN 1 ELSE 0 END) AS jan,
                        SUM(CASE WHEN MONTH(a.DISCHARGE_DATE) = 2  THEN 1 ELSE 0 END) AS feb,
                        SUM(CASE WHEN MONTH(a.DISCHARGE_DATE) = 3  THEN 1 ELSE 0 END) AS mar,
                        SUM(CASE WHEN MONTH(a.DISCHARGE_DATE) = 4  THEN 1 ELSE 0 END) AS apr,
                        SUM(CASE WHEN MONTH(a.DISCHARGE_DATE) = 5  THEN 1 ELSE 0 END) AS mei,
                        SUM(CASE WHEN MONTH(a.DISCHARGE_DATE) = 6  THEN 1 ELSE 0 END) AS jun,
                        SUM(CASE WHEN MONTH(a.DISCHARGE_DATE) = 7  THEN 1 ELSE 0 END) AS jul,
                        SUM(CASE WHEN MONTH(a.DISCHARGE_DATE) = 8  THEN 1 ELSE 0 END) AS agu,
                        SUM(CASE WHEN MONTH(a.DISCHARGE_DATE) = 9  THEN 1 ELSE 0 END) AS sep,
                        SUM(CASE WHEN MONTH(a.DISCHARGE_DATE) = 10 THEN 1 ELSE 0 END) AS okt,
                        SUM(CASE WHEN MONTH(a.DISCHARGE_DATE) = 11 THEN 1 ELSE 0 END) AS nov,
                        SUM(CASE WHEN MONTH(a.DISCHARGE_DATE) = 12 THEN 1 ELSE 0 END) AS des
                    FROM dt01_bpjs_ur_dt a
                    where a.kode_rs='".$koders."'
                    and   year(a.admission_date) = ".$periode."
                    GROUP BY  a.sl

                ";

        $recordset = $this->db->query($query);
        return $recordset->getResult();
    }
    
}