<?php

namespace Modules\Developer\Libraries;

/** Portable catalog validation and deterministic source-to-target module matching. */
class ModuleCatalogSync
{
    public const FIELDS = [
        'modules_id', 'modules_name', 'modules_header_id', 'package', 'def_controller',
        'parent', 'icon', 'urut', 'active', 'status', 'version',
    ];

    public static function export(array $catalog): array
    {
        return [
            'format' => 'infinite.modules',
            'version' => 1,
            'exported_at' => gmdate(DATE_ATOM),
            'modules' => self::normalize($catalog),
        ];
    }

    public static function import(string $json): array
    {
        if (strlen($json) > 2 * 1024 * 1024) {
            throw new \DomainException('Ukuran file maksimal 2 MB.');
        }
        try {
            $data = json_decode($json, true, 64, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new \DomainException('File JSON tidak valid.');
        }
        if (!is_array($data) || ($data['format'] ?? '') !== 'infinite.modules'
            || ($data['version'] ?? null) !== 1 || !is_array($data['modules'] ?? null)) {
            throw new \DomainException('Gunakan file Export Master Modules Infinite versi 1.');
        }
        return self::normalize($data['modules']);
    }

    public static function normalize(array $rows): array
    {
        if (!$rows || count($rows) > 1000 || array_values($rows) !== $rows) {
            throw new \DomainException('Katalog harus berisi 1 sampai 1.000 modul.');
        }
        $result = [];
        foreach ($rows as $row) {
            if (!is_array($row)) throw new \DomainException('Data modul tidak valid.');
            $clean = [];
            foreach ([
                'modules_id' => 36, 'modules_name' => 100, 'modules_header_id' => 36,
                'package' => 100, 'def_controller' => 100, 'parent' => 1,
                'icon' => 500, 'active' => 1, 'status' => 1, 'version' => 100,
            ] as $field => $limit) {
                $value = $row[$field] ?? '';
                if (!is_string($value) || !mb_check_encoding($value, 'UTF-8') || mb_strlen($value) > $limit) {
                    throw new \DomainException('Nilai kolom ' . $field . ' tidak valid.');
                }
                $clean[$field] = trim($value);
            }
            if (!preg_match('/\A[a-z0-9_-]{1,36}\z/iD', $clean['modules_id'])
                || $clean['modules_name'] === '' || $clean['status'] === ''
                || !in_array($clean['parent'], ['H', 'Y', 'N'], true)
                || !in_array($clean['active'], ['0', '1'], true)) {
                throw new \DomainException('Identitas atau status modul tidak valid.');
            }
            foreach (['package', 'def_controller'] as $field) {
                $clean[$field] = strtolower($clean[$field]);
                if (!preg_match('/\A(?:[a-z][a-z0-9_]*)?\z/D', $clean[$field])) {
                    throw new \DomainException('Route modul tidak valid: ' . $clean['modules_name'] . '.');
                }
            }
            if ($clean['package'] === '' && $clean['def_controller'] !== '') {
                throw new \DomainException('Package wajib diisi untuk route modul.');
            }
            if (preg_match('/\Abi-[a-z0-9-]+\z/D', $clean['icon'])) $clean['icon'] = 'bi ' . $clean['icon'];
            if (!preg_match('/\A(?:bi bi-[a-z0-9-]+)?\z/D', $clean['icon'])) {
                throw new \DomainException('Ikon modul harus menggunakan Bootstrap Icons.');
            }
            $position = $row['urut'] ?? null;
            if ((!is_int($position) && !(is_string($position) && ctype_digit($position)))
                || (int) $position < 0 || (int) $position > 1000000) {
                throw new \DomainException('Urutan modul tidak valid.');
            }
            $clean['urut'] = (int) $position;
            $clean['version'] = $clean['version'] === '' ? null : $clean['version'];
            if (isset($result[$clean['modules_id']])) throw new \DomainException('ID modul duplikat pada katalog.');
            $result[$clean['modules_id']] = $clean;
        }

        // Resolve parents iteratively so cycles and missing parents fail before any write.
        $ordered = [];
        while ($result) {
            $resolved = false;
            foreach ($result as $id => $row) {
                if ($row['modules_header_id'] !== '' && !isset($ordered[$row['modules_header_id']])) continue;
                $ordered[$id] = $row;
                unset($result[$id]);
                $resolved = true;
            }
            if (!$resolved) throw new \DomainException('Hierarki modul memiliki siklus atau induk yang tidak ditemukan.');
        }
        return array_values($ordered);
    }

    public static function fingerprint(array $catalog): string
    {
        $rows = $catalog ? self::normalize($catalog) : [];
        usort($rows, static fn(array $a, array $b): int => strcmp($a['modules_id'], $b['modules_id']));
        return hash('sha256', json_encode($rows, JSON_THROW_ON_ERROR));
    }

    private static function route(array $row): string
    {
        return $row['parent'] !== 'H' && $row['package'] !== '' && $row['def_controller'] !== ''
            ? $row['package'] . '/' . $row['def_controller'] : '';
    }

    public static function routeAvailable(string $package, string $controller): bool
    {
        $class = 'Modules\\' . ucfirst($package) . '\\Controllers\\' . ucfirst($controller);
        return class_exists($class) && method_exists($class, 'index')
            && (new \ReflectionMethod($class, 'index'))->isPublic();
    }

    public static function plan(array $source, array $target, ?callable $routeAvailable = null): array
    {
        $source = self::normalize($source);
        $target = $target ? self::normalize($target) : [];
        $byId = array_column($target, null, 'modules_id');
        $routes = [];
        foreach ($target as $row) {
            $route = self::route($row);
            if ($route !== '') $routes[$route][] = $row;
        }
        $routeAvailable = $routeAvailable ?? [self::class, 'routeAvailable'];
        $mapping = [];
        $used = [];
        $entries = [];
        $summary = ['add' => 0, 'update' => 0, 'unchanged' => 0, 'kept' => 0, 'activePreserved' => 0];
        $effective = $byId;
        foreach ($source as $row) {
            $route = self::route($row);
            $matches = $routes[$route] ?? [];
            if (count($matches) > 1) throw new \DomainException('Route target ambigu: ' . $route . '.');
            $sameId = $byId[$row['modules_id']] ?? null;
            $sameRoute = $matches[0] ?? null;
            if ($sameId && $sameRoute && $sameId['modules_id'] !== $sameRoute['modules_id']) {
                throw new \DomainException('Konflik ID dan route pada modul ' . $row['modules_name'] . '.');
            }
            $parent = $row['modules_header_id'] === '' ? '' : $mapping[$row['modules_header_id']];
            $current = $sameId ?? $sameRoute;
            if (!$current && $route === '') {
                $folders = array_values(array_filter($target, static function (array $candidate) use ($row, $parent): bool {
                    return self::route($candidate) === '' && $candidate['modules_header_id'] === $parent
                        && mb_strtolower($candidate['modules_name']) === mb_strtolower($row['modules_name']);
                }));
                if (count($folders) > 1) throw new \DomainException('Folder target ambigu: ' . $row['modules_name'] . '.');
                $current = $folders[0] ?? null;
            }
            if ($current && (self::route($current) === '') !== ($route === '')) {
                throw new \DomainException('Jenis folder dan halaman berbeda untuk ' . $row['modules_name'] . '.');
            }
            $id = $current['modules_id'] ?? $row['modules_id'];
            if (isset($used[$id])) throw new \DomainException('Beberapa modul sumber mengarah ke target yang sama.');
            $used[$id] = true;
            $mapping[$row['modules_id']] = $id;
            $data = $row;
            $data['modules_id'] = $id;
            $data['modules_header_id'] = $parent;
            if ($current) {
                if ($data['active'] !== $current['active']) $summary['activePreserved']++;
                $data['active'] = $current['active'];
            }
            if ($route !== '' && $data['active'] === '1' && (!$current || self::route($current) !== $route)
                && !$routeAvailable($data['package'], $data['def_controller'])) {
                throw new \DomainException('Controller belum tersedia di target untuk ' . $route . '. Deploy kode modul terlebih dahulu.');
            }
            $changes = [];
            foreach ($data as $field => $value) {
                if ($field !== 'modules_id' && (!$current || $current[$field] !== $value)) {
                    $changes[$field] = ['before' => $current[$field] ?? null, 'after' => $value];
                }
            }
            $action = !$current ? 'add' : ($changes ? 'update' : 'unchanged');
            $summary[$action]++;
            $entries[] = compact('action', 'id', 'route', 'data', 'changes') + ['name' => $row['modules_name']];
            $effective[$id] = $data;
        }
        // Catch route collisions and cycles introduced by moves, including target-only modules.
        $final = self::normalize(array_values($effective));
        $finalRoutes = [];
        foreach ($final as $row) {
            $route = self::route($row);
            if ($route !== '' && isset($finalRoutes[$route])) throw new \DomainException('Route duplikat setelah sinkronisasi: ' . $route . '.');
            if ($route !== '') $finalRoutes[$route] = true;
        }
        $summary['kept'] = count(array_diff(array_keys($byId), array_keys($used)));
        return ['revision' => self::fingerprint($target), 'entries' => $entries, 'summary' => $summary];
    }
}
