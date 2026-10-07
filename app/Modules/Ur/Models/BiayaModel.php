<?php

namespace Modules\Ur\Models;

use CodeIgniter\Model;

class BiayaModel extends Model{
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

    function rawdata($koders,$periode){
        $query = "
                    SELECT
                        x.*,
                        ROUND((x.tarifklaimrawatinap + x.tarifklaimrawatjalan) - (x.tarifrsrawatinap + x.tarifrsrawatjalan), 0) AS selisihtotal,
                        (x.jmlpasienrawatinap + x.jmlpasienrawatjalan) AS jmlpasientotal,
                        ROUND(x.tarifrsrawatinap + x.tarifrsrawatjalan, 0) AS tarifrstotal,
                        ROUND(x.tarifklaimrawatinap + x.tarifklaimrawatjalan, 0) AS tarifklaimtotal,
                        ROUND(((x.tarifklaimrawatinap + x.tarifklaimrawatjalan) - (x.tarifrsrawatinap + x.tarifrsrawatjalan)) / NULLIF(x.tarifrsrawatinap + x.tarifrsrawatjalan, 0) * 100, 2) AS efisiensi_persen,
                        ROUND((x.tarifrsrawatinap + x.tarifrsrawatjalan) / NULLIF(x.jmlpasienrawatinap + x.jmlpasienrawatjalan, 0), 0) AS avg_tarif_rs,
                        ROUND((x.tarifklaimrawatinap + x.tarifklaimrawatjalan) / NULLIF(x.jmlpasienrawatinap + x.jmlpasienrawatjalan, 0), 0) AS avg_tarif_klaim
                    FROM (
                        SELECT
                            upper(a.dpjp) as dpjp,
                            SUM(CASE WHEN a.ptd = '1' THEN 1 ELSE 0 END) AS jmlpasienrawatinap,
                            SUM(CASE WHEN a.ptd = '2' THEN 1 ELSE 0 END) AS jmlpasienrawatjalan,
                            SUM(CASE WHEN a.ptd = '1' THEN a.tarif_rs ELSE 0 END) AS tarifrsrawatinap,
                            SUM(CASE WHEN a.ptd = '2' THEN a.tarif_rs ELSE 0 END) AS tarifrsrawatjalan,
                            SUM(CASE WHEN a.ptd = '1' THEN a.tarif_inacbg ELSE 0 END) AS tarifklaimrawatinap,
                            SUM(CASE WHEN a.ptd = '2' THEN a.tarif_inacbg ELSE 0 END) AS tarifklaimrawatjalan
                        FROM dt01_bpjs_ur_dt a
                        where a.kode_rs='".$koders."'
                        and   year(a.admission_date) = ".$periode."
                        GROUP BY upper(a.dpjp)
                    ) x
                    ORDER BY selisihtotal desc, efisiensi_persen desc;
                ";

        $recordset = $this->db->query($query);
        return $recordset->getResult();
    }
    
}