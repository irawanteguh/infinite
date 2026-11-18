<?php

namespace Modules\Ur\Models;

use CodeIgniter\Model;

class PneumoniaModel extends Model{
    protected $DBGroup = null;

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
                        a.ptd,
                        a.los,
                        a.mrn,
                        a.nama_pasien,
                        a.sep,
                        a.nokartu,
                        a.dpjp,
                        a.diaglist,
                        a.proclist,
                        a.tarif_rs,
                        a.total_tarif,
                        a.idrg_total_tarif,
                        a.sl,
                        DATE_FORMAT(a.admission_date, '%d.%m.%Y') AS admission_date,
                        DATE_FORMAT(a.discharge_date, '%d.%m.%Y') AS discharge_date, MONTH(a.discharge_date) AS month
                    FROM dt01_bpjs_ur_dt a
                    WHERE a.ptd = '1'
                    AND a.diaglist REGEXP '(^|;)[[:space:]]*(J1[2-8]|J22)([.]|;|$)'
                    AND a.kode_rs = ? AND a.discharge_date >= ? AND a.discharge_date < ?
                    ORDER BY a.admission_date ASC;
                ";

        $recordset = $this->db->query($query, [$koders, sprintf('%04d-01-01', $periode), sprintf('%04d-01-01', $periode + 1)]);
        return $recordset->getResult();
    }
    
public function summary($koders, $year) { return $this->rawdata($koders, $year); }}