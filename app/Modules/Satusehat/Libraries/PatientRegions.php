<?php
namespace Modules\Satusehat\Libraries;
class PatientRegions
{
    public const LEVELS = ['provinces' => [null, 0, 2], 'cities' => ['province_codes', 2, 4], 'districts' => ['city_codes', 4, 6], 'sub-districts' => ['district_codes', 6, 10]];
    public function query(string $level, string $parent, int $page, string $cursor): array
    {
        if (!isset(self::LEVELS[$level])) throw new \InvalidArgumentException('Jenis wilayah tidak valid.');
        [$filter, $length] = self::LEVELS[$level];
        if ($page < 1 || $page > 10000 || strlen($cursor) > 500) throw new \InvalidArgumentException('Halaman wilayah tidak valid.');
        $query = ['current_page' => $page, 'next' => $cursor, 'prev' => ''];
        if ($filter) {
            if (!preg_match('/^\d{' . $length . '}$/D', $parent)) throw new \InvalidArgumentException('Pilih wilayah induk terlebih dahulu.');
            $query[$filter] = $parent;
        }
        return $query;
    }
    public function normalize(array $body, string $level, string $parent): array
    {
        if (!isset(self::LEVELS[$level]) || !isset($body['data']) || !is_array($body['data'])) throw new \RuntimeException('Respons master wilayah tidak valid.');
        $rows = [];
        foreach ($body['data'] as $row) {
            if (!is_array($row) || !preg_match('/^\d{' . self::LEVELS[$level][2] . '}$/D', (string)($row['code'] ?? '')) || !is_string($row['name'] ?? null)) continue;
            if ($parent !== '' && (string)($row['parent_code'] ?? '') !== $parent) continue;
            $rows[] = ['id' => (string)$row['code'], 'text' => $row['name']];
        }
        $page = $body['meta']['page'] ?? [];
        $more = (int)($page['current'] ?? 1) < (int)($page['total_page'] ?? 1);
        return ['results' => $rows, 'more' => $more, 'next' => $more ? (string)($body['meta']['cursors']['next'] ?? '') : ''];
    }
}
