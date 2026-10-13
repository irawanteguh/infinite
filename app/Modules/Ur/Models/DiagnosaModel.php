<?php

namespace Modules\Ur\Models;

use CodeIgniter\Model;

class DiagnosaModel extends Model{
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
                        DATE_FORMAT(a.discharge_date, '%d.%m.%Y') AS discharge_date
                    FROM dt01_bpjs_ur_dt a
                    WHERE a.ptd = '1'
                    AND   a.diaglist REGEXP '(^|;)[[:space:]]*(R56[.]0|A35|B24|G44[.]2|G43[.]9|G51[.]0|R42|F45[.]9|G47[.]0|T15[.]1|H10[.]9|H11[.]3|H04[.]1|H01[.]0|H00[.]0|H02[.]0|H15[.]1|H52[.]0|H52[.]1|H52[.]2|H52[.]4|H53[.]6|H60[.]9|H66[.]0|H61[.]2|T75[.]3|J34[.]0|J00|J30[.]0|J30[.]4|T17[.]1|R04[.]0|J11[.]1|A37[.]9|J02[.]9|J03[.]9|J04[.]0|J45[.]9|J20[.]9|J18([.][0-9]+)?|A16[.]2|I10|B37[.]0|K12[.]0|B00[.]1|K11[.]2|P38|K29[.]7|A09|K21[.]9|A01[.]0|K90[.]4|T78[.]1|A05[.]9|B76[.]9|B78[.]9|B77[.]9|B65[.]9|B68[.]9|B15[.]9|A03[.]9|A06[.]0|K64[.]0|K64[.]1|N39[.]0|A54[.]9|N10|N47|N34[.]1|A54[.]0|N76[.]2|N76[.]0|N70[.]9|Z34[.]9|O03[.]9|O99[.]0|D50[.]9|O70[.]0|O70[.]1|L02[.]9|N61|O92[.]1|N64[.]5|E10[.]9|E11[.]9|E16[.]2|E46|E56[.]9|E61[.]9|E78[.]5|E79[.]0|E66[.]9|I88[.]9|A90|A91|A27[.]9|T78[.]2|L97|D17[.]9|B07[.]9|B08[.]1|B02[.]9|B05[.]9|B01[.]9|B00[.]9|L01[.]0|L08[.]0|L73[.]9|L08[.]1|A46|A18[.]4|A30[.]9|A51[.]0|A51[.]3|B35[.]0|B35[.]4|B35[.]2|B35[.]1|B35[.]6|B35[.]3|B36[.]0|B37[.]2|B74[.]9|B85[.]0|B85[.]3|B86|T63[.]4|L24[.]9|L20[.]9|L30[.]0|L22|L21[.]9|L42|L70[.]0|L73[.]2|L71[.]0|L74[.]3|L50[.]9|L27[.]0|L27[.]1)'
                    and   a.kode_rs='".$koders."'
                    and   year(a.discharge_date) = ".$periode."
                    ORDER BY a.admission_date ASC;
                ";

        $recordset = $this->db->query($query);
        return $recordset->getResult();
    }
    
}