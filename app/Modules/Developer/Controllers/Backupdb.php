<?php

namespace Modules\Developer\Controllers;

use App\Controllers\BaseController;

class Backupdb extends BaseController{

    public function index(){
        return view('template/dashboard-light-aside', [
            'contents' => view(
                'Modules\Developer\Views\v_backupdb'
            )
        ]);
    }

    public function backup(){
        $db = db_connect();
        $backupPath = WRITEPATH . 'backups/';

        if (!is_dir($backupPath)) {
            mkdir($backupPath, 0755, true);
        }

        $tables = $db->listTables();

        $sql  = "-- =====================================================\n";
        $sql .= "-- INFINITE DATABASE BACKUP\n";
        $sql .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
        $sql .= "-- =====================================================\n\n";

        foreach ($tables as $table) {

            $sql .= "-- =====================================================\n";
            $sql .= "-- Table: {$table}\n";
            $sql .= "-- =====================================================\n\n";
            $sql .= "DROP TABLE IF EXISTS `{$table}`;\n\n";

            $create = $db->query("SHOW CREATE TABLE `{$table}`")->getRowArray();

            if (!empty($create)) {
                $createTable = $create['Create Table'] ?? '';
                if ($createTable !== '') {
                    $sql .= $createTable . ";\n\n";
                }
            }

            $rows = $db->query("SELECT * FROM `{$table}`")->getResultArray();

            foreach ($rows as $row) {
                $columns = array_map(function ($column) {return "`{$column}`";},array_keys($row));
                $values = array_map(function ($value) use ($db) {if ($value === null) {return 'NULL';}return "'" . $db->escapeString($value) . "'";},array_values($row));

                $sql .= "INSERT INTO `{$table}` (";
                $sql .= implode(', ', $columns);
                $sql .= ") VALUES (";
                $sql .= implode(', ', $values);
                $sql .= ");\n";
            }

            $sql .= "\n";
        }

        $filename = 'infinite_backup_' . date('Ymd_His') . '.sql';
        $filepath = $backupPath . $filename;
        $result = file_put_contents($filepath, $sql);

        if ($result === false) {
            return response()->setJSON([
                'responseCode'   => '01',
                'responseHead'   => 'error',
                'responseDesc'   => 'Failed to create database backup.',
                'responseResult' => []
            ]);
        }

        return response()->setJSON([
            'responseCode'   => '00',
            'responseHead'   => 'success',
            'responseDesc'   => 'Database backup successfully created.'
        ]);
    }

    public function backupics(){
        $db = db_connect();

        $backupPath = WRITEPATH . 'backups/';

        if (!is_dir($backupPath)) {
            mkdir($backupPath, 0755, true);
        }

        /*
        * TABEL YANG AKAN DIBACKUP
        *
        * Jangan gunakan $db->listTables()
        * karena akan membackup seluruh database.
        */
        $tables = [
            'dt01_ics_source_document',
            'dt01_ics_mdc',
            'dt01_ics_icd_code',
            'dt01_ics_ch3_section',
            'dt01_ics_ch3_section_icd_scope',
            'dt01_ics_coding_rule',
            'dt01_ics_coding_rule_code',
            'dt01_ics_case_example',
            'dt01_ics_case_example_item',
            'dt01_ics_idrg_dc',
            'dt01_ics_special_code_group',
            'dt01_ics_special_code_group_item',
            'dt01_ics_special_code_group_dc',
            'dt01_ics_idrg_error_code',
            'dt01_ics_idrg_error_required_code',
            'dt01_ics_drug_external_cause_map'
        ];

        $sql  = "-- =====================================================\n";
        $sql .= "-- INFINITE DATABASE BACKUP\n";
        $sql .= "-- Selected Tables Only\n";
        $sql .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
        $sql .= "-- =====================================================\n\n";

        $sql .= "SET NAMES utf8mb4;\n";
        $sql .= "SET FOREIGN_KEY_CHECKS = 0;\n\n";

        foreach ($tables as $table) {

            // Pastikan tabel memang ada
            if (!$db->tableExists($table)) {
                continue;
            }

            $sql .= "-- =====================================================\n";
            $sql .= "-- Table: {$table}\n";
            $sql .= "-- =====================================================\n\n";

            $sql .= "DROP TABLE IF EXISTS `{$table}`;\n\n";

            /*
            * CREATE TABLE
            */
            $create = $db->query(
                "SHOW CREATE TABLE `{$table}`"
            )->getRowArray();

            if (!empty($create)) {

                $createTable = $create['Create Table'] ?? '';

                if ($createTable !== '') {
                    $sql .= $createTable . ";\n\n";
                }
            }

            /*
            * DATA
            */
            $rows = $db->query(
                "SELECT * FROM `{$table}`"
            )->getResultArray();

            if (!empty($rows)) {

                foreach ($rows as $row) {

                    $columns = array_map(
                        function ($column) {
                            return "`{$column}`";
                        },
                        array_keys($row)
                    );

                    $values = array_map(
                        function ($value) use ($db) {

                            if ($value === null) {
                                return 'NULL';
                            }

                            return "'" . $db->escapeString($value) . "'";
                        },
                        array_values($row)
                    );

                    $sql .= "INSERT INTO `{$table}` (";
                    $sql .= implode(', ', $columns);
                    $sql .= ") VALUES (";
                    $sql .= implode(', ', $values);
                    $sql .= ");\n";
                }
            }

            $sql .= "\n";
        }

        $sql .= "SET FOREIGN_KEY_CHECKS = 1;\n";

        $filename = 'infinite_backup_ics_' . date('Ymd_His') . '.sql';

        $filepath = $backupPath . $filename;

        $result = file_put_contents($filepath, $sql);

        if ($result === false) {

            return response()->setJSON([
                'responseCode'   => '01',
                'responseHead'   => 'error',
                'responseDesc'   => 'Failed to create database backup.',
                'responseResult' => []
            ]);
        }

        return response()->setJSON([
            'responseCode'   => '00',
            'responseHead'   => 'success',
            'responseDesc'   => 'Database backup successfully created.',
            'responseResult' => [
                'filename' => $filename,
                'tables'   => count($tables),
                'size'     => $result
            ]
        ]);
    }

    public function listbackup(){
        $backupPath = WRITEPATH . 'backups/';

        if (!is_dir($backupPath)) {
            return [];
        }

        $files = [];

        foreach (glob($backupPath . '*.sql') as $filepath) {

            if (!is_file($filepath)) {
                continue;
            }

            $filename = basename($filepath);

            $files[] = [
                'filename'      => $filename,
                'size'          => filesize($filepath),
                'sizeFormatted' => $this->formatBytes(filesize($filepath)),
                'created'       => date('Y-m-d H:i:s', filemtime($filepath))
            ];
        }

        usort($files, function ($a, $b) {
            return strtotime($b['created']) <=> strtotime($a['created']);
        });

        if (!empty($files)) {
            $json["responseCode"]   = "00";
            $json["responseHead"]   = "success";
            $json["responseDesc"]   = "Backup files found.";
            $json["responseResult"] = $files;
        } else {
            $json["responseCode"]   = "01";
            $json["responseHead"]   = "info";
            $json["responseDesc"]   = "No backup files found.";
        }

        return response()->setJSON($json);
    }

    public function download($filename){

        $filename = basename($filename);
        $filepath = WRITEPATH.'backups/'.$filename;

        if (!is_file($filepath)) {
            return response()->setStatusCode(404)->setJSON([
                'responseCode'   => '01',
                'responseHead'   => 'error',
                'responseDesc'   => 'Backup file not found.'
            ]);
        }

        return response()->download($filepath, null)->setFileName($filename);
    }

    public function delete($filename){
        $filename = basename($filename);
        $filepath = WRITEPATH . 'backups/' . $filename;

        if (!is_file($filepath)) {
            return response()->setStatusCode(404)->setJSON([
                'responseCode' => '01',
                'responseHead' => 'error',
                'responseDesc' => 'Backup file not found.'
            ]);
        }

        if (!unlink($filepath)) {
            return response()->setStatusCode(500)->setJSON([
                'responseCode' => '02',
                'responseHead' => 'error',
                'responseDesc' => 'Backup file could not be deleted.'
            ]);
        }

        return response()->setJSON([
            'responseCode' => '00',
            'responseHead' => 'success',
            'responseDesc' => 'Backup file deleted successfully.'
        ]);
    }

    private function formatBytes($bytes, $precision = 2){
        if ($bytes <= 0) {
            return '0 Bytes';
        }

        $units = ['Bytes','KB','MB','GB','TB'];
        $bytes = max($bytes, 0);

        $power = floor(
            log($bytes, 1024)
        );

        $power = min(
            $power,
            count($units) - 1
        );

        $bytes /= pow(1024, $power);

        return round($bytes, $precision) . ' ' . $units[$power];
    }
}