<?php
namespace Modules\Developer\Libraries;

class DatabaseDump
{
    public const ICS_TABLES = ['dt01_ics_source_document','dt01_ics_mdc','dt01_ics_icd_code','dt01_ics_ch3_section','dt01_ics_ch3_section_icd_scope','dt01_ics_coding_rule','dt01_ics_coding_rule_code','dt01_ics_case_example','dt01_ics_case_example_item','dt01_ics_idrg_dc','dt01_ics_special_code_group','dt01_ics_special_code_group_item','dt01_ics_special_code_group_dc','dt01_ics_idrg_error_code','dt01_ics_idrg_error_required_code','dt01_ics_drug_external_cause_map'];

    private $connection;

    public function __construct(?\CodeIgniter\Database\BaseConnection $connection = null)
    {
        $this->connection = $connection;
    }

    public function create(string $scope = 'full'): array
    {
        if (!in_array($scope, ['full','ics'], true)) throw new \InvalidArgumentException('Jenis backup tidak valid.');
        if (!function_exists('proc_open')) throw new \RuntimeException('Fungsi proc_open perlu diaktifkan agar dump database dapat dijalankan.');
        $db = $this->connection ?? db_connect();
        if ($db->DBDriver !== 'MySQLi') throw new \RuntimeException('Dump ini mendukung database MySQL/MariaDB.');
        $binary = $this->binary();
        $directory = WRITEPATH . 'backups';
        $temporary = WRITEPATH . 'cache/database-dump';
        foreach ([$directory, $temporary] as $folder) {
            if (!is_dir($folder) && !mkdir($folder, 0700, true) && !is_dir($folder)) throw new \RuntimeException('Direktori backup tidak dapat dibuat.');
            if (!is_writable($folder)) throw new \RuntimeException('Direktori backup tidak dapat ditulis.');
        }
        $lock = fopen($temporary . '/backup.lock', 'c');
        if (!$lock) throw new \RuntimeException('Kunci proses backup tidak dapat dibuat.');
        if (!flock($lock, LOCK_EX | LOCK_NB)) { fclose($lock); throw new \RuntimeException('Proses backup lain masih berjalan. Tunggu hingga selesai.'); }
        $id = bin2hex(random_bytes(8));
        $options = $temporary . '/dump-' . $id . '.cnf';
        $errors = $temporary . '/dump-' . $id . '.log';
        $filename = 'infinite_backup_' . ($scope === 'ics' ? 'ics_' : '') . date('Ymd_His') . '_' . substr($id,0,8) . '.sql';
        $final = $directory . '/' . $filename;
        $partial = $final . '.part';
        $process = null;
        $started = microtime(true);
        try {
            $tables = $db->listTables();
            if ($scope === 'ics') $tables = array_values(array_intersect(self::ICS_TABLES, $tables));
            if (!$tables) throw new \RuntimeException('Tidak ada tabel yang dapat di-backup.');
            $settings = "[client]\n";
            foreach (['host'=>$db->hostname, 'port'=>$db->port, 'user'=>$db->username, 'password'=>$db->password] as $key=>$value) {
                $settings .= $key . '="' . strtr((string)$value, ["\\"=>"\\\\", '"'=>'\\"', "\n"=>'\\n', "\r"=>'\\r', "\t"=>'\\t']) . "\"\n";
            }
            $handle = fopen($options, 'xb');
            if (!$handle) throw new \RuntimeException('Konfigurasi dump sementara tidak dapat dibuat.');
            chmod($options, 0600);
            try { if (fwrite($handle, $settings) !== strlen($settings)) throw new \RuntimeException('Konfigurasi dump sementara gagal ditulis.'); } finally { fclose($handle); }
            $command = [$binary, '--defaults-extra-file=' . $options, '--single-transaction', '--quick', '--skip-lock-tables', '--no-tablespaces', '--hex-blob', '--default-character-set=utf8mb4', '--triggers', '--result-file=' . $partial];
            if ($scope === 'full') { $command[] = '--routines'; $command[] = '--events'; }
            $command[] = '--';
            $command[] = $db->database;
            if ($scope === 'ics') $command = array_merge($command, $tables);
            @set_time_limit(0);
            $process = proc_open($command, [0=>['pipe','r'], 1=>['file',PHP_OS_FAMILY === 'Windows' ? 'NUL' : '/dev/null','w'], 2=>['file',$errors,'w']], $pipes, null, null, ['bypass_shell'=>true,'create_new_console'=>false]);
            if (!is_resource($process)) throw new \RuntimeException('Utilitas dump database gagal dijalankan.');
            fclose($pipes[0]);
            do {
                $status = proc_get_status($process);
                if (!$status['running']) break;
                if (microtime(true) - $started > 900) {
                    proc_terminate($process);
                    throw new \RuntimeException('Proses backup melewati batas 15 menit. Jalankan dump melalui server untuk database berukuran besar.');
                }
                usleep(100000);
            } while (true);
            $closed = proc_close($process);
            $process = null;
            $exit = $status['exitcode'] >= 0 ? $status['exitcode'] : $closed;
            clearstatcache(true, $partial);
            if ($exit !== 0 || !is_file($partial) || filesize($partial) === 0) {
                $detail = is_file($errors) ? trim((string)file_get_contents($errors)) : '';
                if ($db->password !== '') $detail = str_replace($db->password, '[redacted]', $detail);
                log_message('error', 'Database dump failed: {detail}', ['detail'=>mb_substr($detail,0,2000)]);
                throw new \RuntimeException('Dump database gagal. Periksa izin akun database serta log server; file yang belum lengkap tidak disimpan.');
            }
            $promoted = false;
            for ($attempt = 0; $attempt < 30; $attempt++) {
                if (@rename($partial, $final)) { $promoted = true; break; }
                usleep(200000);
            }
            if (!$promoted) throw new \RuntimeException('Hasil dump masih dikunci proses lain dan belum dapat dipindahkan. Coba kembali setelah beberapa saat.');
            chmod($final, 0600);
            return ['filename'=>$filename,'size'=>filesize($final),'tables'=>count($tables),'scope'=>$scope,'durationSeconds'=>round(microtime(true)-$started,2)];
        } finally {
            if (is_resource($process)) { proc_terminate($process); proc_close($process); }
            foreach ([$options,$errors,$partial] as $path) {
                if (!is_file($path)) continue;
                for ($attempt = 0; $attempt < 30; $attempt++) {
                    if (@unlink($path) || !is_file($path)) break;
                    usleep(100000);
                }
                if (is_file($path)) log_message('error', 'Temporary database dump file could not be removed: {name}', ['name'=>basename($path)]);
            }
            flock($lock, LOCK_UN); fclose($lock);
        }
    }

    private function binary(): string
    {
        $configured = env('database.backup.dumpBinary', '');
        $candidates = array_filter([$configured]);
        foreach ([dirname(PHP_BINARY), PHP_BINDIR, ROOTPATH] as $root) {
            for ($level = 0; $level < 6; $level++) {
                $candidates[] = rtrim($root, '/\\') . '/mysql/bin/mysqldump.exe';
                $candidates[] = rtrim($root, '/\\') . '/mysql/bin/mariadb-dump.exe';
                $parent = dirname($root);
                if ($parent === $root) break;
                $root = $parent;
            }
        }
        $candidates = array_merge($candidates, ['/usr/bin/mariadb-dump', '/usr/bin/mysqldump', '/usr/local/bin/mysqldump']);
        foreach ($candidates as $candidate) if (is_file($candidate)) return $candidate;
        throw new \RuntimeException('Utilitas mysqldump/mariadb-dump tidak ditemukan. Atur database.backup.dumpBinary pada konfigurasi server.');
    }
}
