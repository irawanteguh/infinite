<?php

namespace Modules\Ics\Models;

use CodeIgniter\Model;

class MastermdcModel extends Model{
    protected $DBGroup = 'default';

    function datamastersourcedocument(){
        $query = "
                    select a.id, title
                    from dt01_ics_source_document a
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
                    FROM dt01_ics_mdc a
                    LEFT JOIN dt01_ics_source_document b 
                        ON b.id = a.document_id;
                ";

        $recordset = $this->db->query($query);
        return $recordset->getResult();
    }

    function datamastersection(){
        $query = "
                    SELECT
                        a.ID AS SECTION_ID,
                        a.SECTION_CODE,
                        a.TITLE_IND,
                        a.TITLE_ENG,
                        a.ICD_RANGE_TEXT,
                        GROUP_CONCAT(
                            CONCAT(
                                'RULE_ID=', b.ID,
                                '|RULE_SEQ=', b.RULE_SEQ,
                                '|RULE_TYPE=', b.RULE_TYPE,
                                '|CONDITION=', COALESCE(b.CONDITION_TEXT, ''),
                                '|INSTRUCTION=', COALESCE(b.INSTRUCTION_TEXT, ''),
                                '|MANDATORY=', COALESCE(b.IS_MANDATORY, ''),
                                '|AGE=', COALESCE(b.AGE_CONDITION, ''),
                                '|SEX=', COALESCE(b.SEX_CONDITION, ''),
                                '|SOURCE=', COALESCE(b.SOURCE_TEXT, ''),
                                '|PAGE_PRINTED=', COALESCE(b.PAGE_PRINTED, ''),
                                '|PAGE_PDF=', COALESCE(b.PAGE_PDF, '')
                            )
                            ORDER BY b.RULE_SEQ ASC
                            SEPARATOR ';'
                        ) AS CODING_RULES
                    FROM dt01_ics_ch3_section a
                    LEFT JOIN dt01_ics_coding_rule b
                        ON b.SECTION_ID = a.ID
                    GROUP BY
                        a.ID,
                        a.SECTION_CODE,
                        a.TITLE_IND,
                        a.TITLE_ENG,
                        a.ICD_RANGE_TEXT
                    ORDER BY
                        a.MDC_ID ASC,
                        a.SEQUENCE_NO ASC;
                ";

        $recordset = $this->db->query($query);
        return $recordset->getResult();
    }

    function insertmastermdc($data){
        return $this->db->table('dt01_ics_mdc')->insert($data);
    }

    public function updatemastermcd($id, $data){
        return $this->db->table('dt01_ics_mdc')->where('id', $id)->update($data);
    }
    
}