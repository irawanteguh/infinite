<?php

namespace Modules\Qi\Services;

class StoredSummaryReport
{
    private $root;

    public function __construct($root = null)
    {
        $this->root = $root ?? WRITEPATH . 'reports' . DIRECTORY_SEPARATOR . 'qi';
    }

    private function path($groupId, $orgId, $periodId)
    {
        return $this->root . DIRECTORY_SEPARATOR . hash('sha256', serialize([(string) $groupId, (string) $orgId, (string) $periodId])) . '.pdf';
    }

    public function updatedAt($groupId, $orgId, $periodId)
    {
        $path = $this->path($groupId, $orgId, $periodId);
        clearstatcache(true, $path);
        return is_file($path) ? date('Y-m-d H:i:s', filemtime($path)) : null;
    }

    public function read($groupId, $orgId, $periodId)
    {
        $path = $this->path($groupId, $orgId, $periodId);
        if (!is_file($path)) return null;
        $content = file_get_contents($path);
        if ($content === false) throw new \RuntimeException('PDF tersimpan tidak dapat dibaca.');
        return $content;
    }

    public function save($groupId, $orgId, $periodId, $content)
    {
        if (strncmp($content, '%PDF-', 5) !== 0) throw new \RuntimeException('Isi laporan bukan PDF.');
        if (!is_dir($this->root) && !mkdir($this->root, 0775, true) && !is_dir($this->root)) {
            throw new \RuntimeException('Folder laporan tidak dapat dibuat.');
        }
        $temporary = tempnam($this->root, 'report_');
        if ($temporary === false) throw new \RuntimeException('File sementara laporan tidak dapat dibuat.');
        try {
            if (file_put_contents($temporary, $content, LOCK_EX) !== strlen($content)
                || !rename($temporary, $this->path($groupId, $orgId, $periodId))) {
                throw new \RuntimeException('PDF gagal disimpan.');
            }
        } finally {
            if (is_file($temporary)) unlink($temporary);
        }
    }
}
