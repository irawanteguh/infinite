<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Modules\Developer\Models\UserActivityModel;

class PruneUserActivity extends BaseCommand
{
    protected $group = 'Developer';
    protected $name = 'developer:prune-userlog';
    protected $description = 'Hapus log aktivitas di luar periode retensi.';
    protected $options = ['--dry-run' => 'Hitung log yang kedaluwarsa.'];

    public function run(array $params)
    {
        $dryRun = array_key_exists('dry-run', $params) || CLI::getOption('dry-run') !== null;
        $count = (new UserActivityModel())->pruneExpired($dryRun);
        CLI::write($dryRun ? "Log kedaluwarsa: $count." : "Log kedaluwarsa dihapus: $count.");

        return EXIT_SUCCESS;
    }
}
