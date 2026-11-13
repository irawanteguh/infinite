<?php

namespace Modules\Pe\Database;

use CodeIgniter\Database\Migration;

class AddSurveyOptionsAndFeedback extends Migration
{
    public function up()
    {
        $this->db->resetDataCache();
        if (!$this->db->fieldExists('answer_options', 'dt01_pe_question_ms')) {
            $this->forge->addColumn('dt01_pe_question_ms', ['answer_options' => ['type' => 'TEXT', 'null' => true]]);
        }
        $fields = [];
        if (!$this->db->fieldExists('suggestions', 'dt01_pe_prem_response_hd')) $fields['suggestions'] = ['type' => 'TEXT', 'null' => true];
        if (!$this->db->fieldExists('score_scale', 'dt01_pe_prem_response_hd')) $fields['score_scale'] = ['type' => 'TINYINT', 'unsigned' => true, 'default' => 5];
        if ($fields) $this->forge->addColumn('dt01_pe_prem_response_hd', $fields);
        $this->db->resetDataCache();
    }

    public function down()
    {
        foreach (['dt01_pe_question_ms' => ['answer_options'], 'dt01_pe_prem_response_hd' => ['suggestions', 'score_scale']] as $table => $fields) {
            foreach ($fields as $field) if ($this->db->fieldExists($field, $table)) $this->forge->dropColumn($table, $field);
        }
        $this->db->resetDataCache();
    }
}