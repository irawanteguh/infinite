<?php

namespace Modules\Developer\Controllers;

use App\Controllers\BaseController;

class Backupdb extends BaseController
{
    /**
     * Halaman Backup Database
     */
    public function index()
    {
        return view('template/dashboard-light-aside', [
            'contents' => view(
                'Modules\Developer\Views\v_backupdb'
            )
        ]);
    }

    /**
     * Create Backup Database
     */
    public function backup()
    {
        $db = db_connect();

        /*
        |--------------------------------------------------------------------------
        | Backup Directory
        |--------------------------------------------------------------------------
        */
        $backupPath = WRITEPATH . 'backups/';

        /*
        |--------------------------------------------------------------------------
        | Create Backup Directory
        |--------------------------------------------------------------------------
        */
        if (!is_dir($backupPath)) {
            mkdir($backupPath, 0755, true);
        }

        /*
        |--------------------------------------------------------------------------
        | Get Database Tables
        |--------------------------------------------------------------------------
        */
        $tables = $db->listTables();

        /*
        |--------------------------------------------------------------------------
        | Initialize SQL
        |--------------------------------------------------------------------------
        */
        $sql  = "-- =====================================================\n";
        $sql .= "-- INFINITE DATABASE BACKUP\n";
        $sql .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
        $sql .= "-- =====================================================\n\n";

        /*
        |--------------------------------------------------------------------------
        | Backup Each Table
        |--------------------------------------------------------------------------
        */
        foreach ($tables as $table) {

            $sql .= "-- =====================================================\n";
            $sql .= "-- Table: {$table}\n";
            $sql .= "-- =====================================================\n\n";

            /*
            |--------------------------------------------------------------------------
            | Drop Table
            |--------------------------------------------------------------------------
            */
            $sql .= "DROP TABLE IF EXISTS `{$table}`;\n\n";

            /*
            |--------------------------------------------------------------------------
            | Create Table
            |--------------------------------------------------------------------------
            */
            $create = $db
                ->query("SHOW CREATE TABLE `{$table}`")
                ->getRowArray();

            if (!empty($create)) {

                $createTable = $create['Create Table'] ?? '';

                if ($createTable !== '') {
                    $sql .= $createTable . ";\n\n";
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Get Table Data
            |--------------------------------------------------------------------------
            */
            $rows = $db
                ->query("SELECT * FROM `{$table}`")
                ->getResultArray();

            /*
            |--------------------------------------------------------------------------
            | Generate INSERT Statement
            |--------------------------------------------------------------------------
            */
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

            $sql .= "\n";
        }

        /*
        |--------------------------------------------------------------------------
        | Backup Filename
        |--------------------------------------------------------------------------
        */
        $filename = 'infinite_backup_' . date('Ymd_His') . '.sql';

        /*
        |--------------------------------------------------------------------------
        | Backup File Path
        |--------------------------------------------------------------------------
        */
        $filepath = $backupPath . $filename;

        /*
        |--------------------------------------------------------------------------
        | Save Backup
        |--------------------------------------------------------------------------
        */
        $result = file_put_contents($filepath, $sql);

        if ($result === false) {

            return response()->setJSON([
                'responseCode'   => '01',
                'responseHead'   => 'error',
                'responseDesc'   => 'Failed to create database backup.',
                'responseResult' => []
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Get All Backup Files
        |--------------------------------------------------------------------------
        */
        $files = $this->getBackupFiles();

        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */
        return response()->setJSON([
            'responseCode'   => '00',
            'responseHead'   => 'success',
            'responseDesc'   => 'Database backup successfully created.',
            'responseResult' => $files
        ]);
    }

    /**
     * Get All Backup Files
     */
    public function listbackup()
    {
        $files = $this->getBackupFiles();

        return response()->setJSON([
            'responseCode'   => '00',
            'responseHead'   => 'success',
            'responseDesc'   => 'Backup files found.',
            'responseResult' => $files
        ]);
    }

    /**
     * Read Backup Directory
     */
    private function getBackupFiles()
    {
        $backupPath = WRITEPATH . 'backups/';

        /*
        |--------------------------------------------------------------------------
        | Directory Not Found
        |--------------------------------------------------------------------------
        */
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
                'created'       => date(
                    'Y-m-d H:i:s',
                    filemtime($filepath)
                )
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Sort Newest First
        |--------------------------------------------------------------------------
        */
        usort($files, function ($a, $b) {
            return strtotime($b['created']) <=> strtotime($a['created']);
        });

        return $files;
    }

    /**
     * Format File Size
     */
    private function formatBytes($bytes, $precision = 2)
    {
        if ($bytes <= 0) {
            return '0 Bytes';
        }

        $units = [
            'Bytes',
            'KB',
            'MB',
            'GB',
            'TB'
        ];

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

    public function download($filename)
    {
        $filename = basename($filename);

        $filepath = WRITEPATH . 'backups/' . $filename;

        /*
        |--------------------------------------------------------------------------
        | Check File
        |--------------------------------------------------------------------------
        */
        if (!is_file($filepath)) {

            return response()
                ->setStatusCode(404)
                ->setJSON([
                    'responseCode'   => '01',
                    'responseHead'   => 'error',
                    'responseDesc'   => 'Backup file not found.',
                    'responseResult' => []
                ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Download File
        |--------------------------------------------------------------------------
        */
        return response()
            ->download($filepath, null)
            ->setFileName($filename);
    }
}