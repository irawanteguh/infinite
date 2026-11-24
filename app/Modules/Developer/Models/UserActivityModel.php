<?php

namespace Modules\Developer\Models;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Model;

class UserActivityModel extends Model
{
    public const RETENTION_DAYS = 30;

    public const EVENTS = [
        'login' => ['Login', 'bi-box-arrow-in-right', 'success'],
        'logout' => ['Logout', 'bi-box-arrow-right', 'warning'],
        'page' => ['Buka halaman', 'bi-window', 'primary'],
        'read' => ['Permintaan baca', 'bi-arrow-down-circle', 'info'],
        'download' => ['Unduh file', 'bi-download', 'info'],
        'request' => ['Permintaan aksi', 'bi-lightning-charge', 'primary'],
        'denied' => ['Akses ditolak', 'bi-shield-lock', 'danger'],
    ];

    protected $table = 'dt01_gen_user_activity';

    public static function retentionCutoff(): string
    {
        return (new \DateTimeImmutable())->modify('-' . self::RETENTION_DAYS . ' days')->format('Y-m-d H:i:s');
    }

    public function pruneExpired(bool $dryRun = false): int
    {
        $query = $this->db->table($this->table)->where('created_at <', self::retentionCutoff());
        if ($dryRun) {
            return $query->countAllResults();
        }
        if (!$query->delete()) {
            throw new \RuntimeException('Gagal menghapus log aktivitas kedaluwarsa.');
        }

        return $this->db->affectedRows();
    }

    public function record(array $activity): void
    {
        if (!$this->builder()->insert($activity)) {
            throw new \RuntimeException('Gagal menyimpan aktivitas pengguna.');
        }
    }

    public function timeline(string $group, string $org, string $before, int $limit): array
    {
        $query = $this->scopedActivities($group, $org);
        if ($before !== '') {
            $query->where('activity_id <', $before);
        }

        return $query->orderBy('activity_id', 'DESC')->get($limit)->getResultArray();
    }

    public function latestPerUser(string $group, string $org): array
    {
        $latestIds = $this->scopedActivities($group, $org)
            ->selectMax('activity_id')->groupBy('user_id');

        return $this->db->table($this->table)->whereIn('activity_id', $latestIds)
            ->orderBy('activity_id', 'DESC')->get()->getResultArray();
    }

    private function scopedActivities(string $group, string $org): BaseBuilder
    {
        return $this->db->table($this->table)->where('group_id', $group)->where('org_id', $org)
            ->where('created_at >=', self::retentionCutoff());
    }
}
