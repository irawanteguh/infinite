<?php
namespace App\Libraries;

/** Shared policy for route enforcement and navigation. Permission is per module, all its actions. */
class ModuleAccess
{
    public static function isWelcome(string $package, string $controller): bool
    {
        return strtolower($package . '/' . $controller) === 'additional/welcomepage';
    }

    public static function isManagement(array $module): bool
    {
        return strtolower(trim((string) ($module['package'] ?? '')) . '/' . trim((string) ($module['def_controller'] ?? ''))) === 'hr/access';
    }

    public static function hasRoute(array $module): bool
    {
        return trim((string) ($module['package'] ?? '')) !== ''
            && trim((string) ($module['def_controller'] ?? '')) !== ''
            && ($module['parent'] ?? '') !== 'H';
    }

    public static function moduleAllowed(array $module, array $state): bool
    {
        if (!self::hasRoute($module) || (isset($module['active']) && (string)$module['active'] !== '1')) return false;
        if (self::isWelcome((string) $module['package'], (string) $module['def_controller'])) return true;
        if (self::isManagement($module)) return !empty($state['manager']);
        return in_array((string) $module['modules_id'], $state['moduleIds'] ?? [], true);
    }

    public static function routeAllowed(string $package, string $controller, array $modules, array $state): bool
    {
        $package = strtolower($package);
        $controller = strtolower($controller);
        if (self::isWelcome($package, $controller)) return true;
        if ($package === 'hr' && $controller === 'access') return !empty($state['manager']);
        // Existing shared upload handlers are exposed by the toolbars of these packages.
        $sharedPackage = ['ur/importtxteklaim' => 'ur', 'bahv/importtxtbahv' => 'klaim'][$package . '/' . $controller] ?? null;
        foreach ($modules as $module) {
            if (!self::hasRoute($module) || !self::moduleAllowed($module, $state)) continue;
            if ($sharedPackage !== null && strtolower($module['package']) === $sharedPackage) return true;
            if (strtolower($module['package']) === $package && strtolower($module['def_controller']) === $controller) return true;
        }
        return false;
    }

    public static function navigation(array $modules, array $state): array
    {
        $byId = [];
        foreach ($modules as $module) $byId[(string) $module['modules_id']] = $module;
        $visible = [];
        foreach ($modules as $module) {
            if (!self::moduleAllowed($module, $state)) continue;
            $id = (string) $module['modules_id'];
            $visited = [];
            while (isset($byId[$id]) && !isset($visited[$id])) {
                $visited[$id] = true;
                $visible[$id] = true;
                $id = (string) ($byId[$id]['modules_header_id'] ?? '');
            }
        }
        // An ancestor may itself have a URL. Do not expose a denied ancestor's route.
        $result = [];
        foreach ($modules as $module) {
            if (!isset($visible[(string) $module['modules_id']])) continue;
            if (self::hasRoute($module) && !self::moduleAllowed($module, $state)) {
                $module['package'] = '';
                $module['def_controller'] = '';
            }
            $result[] = $module;
        }
        return $result;
    }

    public static function revision(array $ids): string
    {
        sort($ids, SORT_STRING);
        return hash('sha256', json_encode(array_values($ids)));
    }
}
