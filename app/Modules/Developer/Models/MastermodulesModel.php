<?php

namespace Modules\Developer\Models;

use App\Models\Modelrouting;

class MastermodulesModel extends Modelrouting
{
    public function catalog(): array
    {
        return $this->menuRows(false);
    }

    public static function editRevision(array $module): string
    {
        return hash('sha256', serialize([$module['modules_name'], $module['icon'], $module['package'], $module['def_controller'], $module['status'], $module['version']]));
    }

    public function updateDetails(string $id, array $details, string $revision): void
    {
        $this->mutateCatalog(function (array $catalog) use ($id, $details, $revision): void {
            $current = array_column($catalog, null, 'modules_id')[$id] ?? null;
            if (!$current || !hash_equals(self::editRevision($current), $revision)) {
                throw new \DomainException('Data modul sudah berubah. Buka kembali formulir Edit.');
            }

            $data = $this->validatedDetails($catalog, $details, $current);
            if (!$this->db->table('dt01_gen_modules_ms')->where('modules_id', $id)->update($data)) {
                throw new \RuntimeException('Could not update module details.');
            }
        });
    }

    public function addChild(string $parent, array $details, string $actor): void
    {
        $this->mutateCatalog(function (array $catalog) use ($parent, $details, $actor): void {
            if (!isset(array_column($catalog, null, 'modules_id')[$parent])) {
                throw new \DomainException('Modul induk sudah tidak tersedia.');
            }

            $data = $this->validatedDetails($catalog, $details);
            $lastPosition = 0;
            foreach ($catalog as $module) {
                if ((string) $module['modules_header_id'] === $parent) {
                    $lastPosition = max($lastPosition, (int) $module['urut']);
                }
            }
            if (!$this->db->table('dt01_gen_modules_ms')->insert($data + [
                'modules_id' => generateuuid(),
                'modules_header_id' => $parent,
                'parent' => 'N',
                'active' => '1',
                'urut' => $lastPosition + 1,
                'created_by' => $actor,
            ])) {
                throw new \RuntimeException('Could not add submodule.');
            }
        });
    }

    private function validatedDetails(array $catalog, array $details, array $current = []): array
    {
        foreach ($details as $field => $value) {
            if (array_key_exists($field, $current) && $value === (string) $current[$field]) {
                $details[$field] = $current[$field];
            }
        }
        $icon = (string) $details['icon'];
        if ($icon !== (string) ($current['icon'] ?? '') && !preg_match('/\A(?:bi bi-[a-z0-9-]+)?\z/D', $icon)) {
            throw new \DomainException('Gunakan format ikon Bootstrap, misalnya bi bi-grid.');
        }

        $package = (string) $details['package'];
        $controller = (string) $details['def_controller'];
        if ($package === (string) ($current['package'] ?? '') && $controller === (string) ($current['def_controller'] ?? '')) {
            return $details;
        }
        if (($package === '') !== ($controller === '')
            || !preg_match('/\A(?:[a-z][a-z0-9_]*)?\z/D', $package)
            || !preg_match('/\A(?:[a-z][a-z0-9_]*)?\z/D', $controller)) {
            throw new \DomainException('Isi Package dan Def Controller dengan segmen rute huruf kecil, angka, atau garis bawah.');
        }
        if ($package !== '') {
            foreach ($catalog as $module) {
                if ($module['modules_id'] !== ($current['modules_id'] ?? null)
                    && strtolower(trim((string) $module['package'])) === $package
                    && strtolower(trim((string) $module['def_controller'])) === $controller) {
                    throw new \DomainException('Rute tersebut sudah digunakan oleh modul lain.');
                }
            }
        }

        return $details;
    }

    public static function revision(array $rows): string
    {
        return self::orderRevision(array_column($rows, 'modules_id'));
    }

    private static function orderRevision(array $ids): string
    {
        return hash('sha256', json_encode($ids));
    }

    public function reorder(string $parent, array $ids, string $revision): array
    {
        $details = $this->mutateCatalog(function (array $catalog) use ($parent, $ids, $revision): array {
            $siblings = array_values(array_filter($catalog, static function (array $module) use ($parent): bool {
                return (string) $module['modules_header_id'] === $parent;
            }));
            if (!hash_equals(self::revision($siblings), $revision)) {
                throw new \DomainException('Susunan modul sudah berubah. Muat ulang susunan, lalu coba lagi.');
            }
            $expected = array_column($siblings, 'modules_id');
            $received = $ids;
            sort($expected, SORT_STRING);
            sort($received, SORT_STRING);
            if (!$expected || $expected !== $received) {
                throw new \DomainException('Daftar modul tidak sesuai dengan grup yang dipilih.');
            }

            $previousOrder = array_column($siblings, 'modules_id');
            $previousIndexes = array_flip($previousOrder);
            $changes = [];
            if ($ids !== $previousOrder) {
                $byId = array_column($siblings, null, 'modules_id');
                foreach ($ids as $index => $id) {
                    $position = $index + 1;
                    if ($previousIndexes[$id] !== $index) {
                        $changes[] = [
                            'module' => $byId[$id]['modules_name'],
                            'before' => $previousIndexes[$id] + 1,
                            'after' => $position,
                        ];
                    }
                    if ((int) $byId[$id]['urut'] !== $position && !$this->db->table('dt01_gen_modules_ms')
                        ->where('modules_id', $id)->update(['urut' => $position])) {
                        throw new \RuntimeException('Could not update module position.');
                    }
                }
            }
            $parentModule = array_column($catalog, null, 'modules_id')[$parent] ?? null;
            return [
                'parent' => $parent === '' ? 'Modul utama' : ($parentModule['modules_name'] ?? $parent),
                'changes' => $changes,
            ];
        });
        return ['revision' => self::orderRevision($ids), 'details' => $details];
    }

    private function mutateCatalog(callable $change)
    {
        if (!$this->db->transBegin()) {
            throw new \RuntimeException('Could not start module transaction.');
        }
        try {
            $result = $change($this->menuRows(false, true));
            if (!$this->db->transStatus() || !$this->db->transCommit()) {
                throw new \RuntimeException('Could not commit changes to the module catalog.');
            }
        } catch (\Throwable $e) {
            $this->db->transRollback();
            throw $e;
        }

        return $result;
    }
}
