<?php

namespace Modules\Risk\Libraries;

final class RiskMatrix
{
    private const SCORES = [
        [1, 3, 5, 8, 20],
        [2, 7, 11, 13, 21],
        [4, 10, 14, 17, 22],
        [6, 12, 16, 19, 24],
        [9, 15, 18, 23, 25],
    ];

    public static function assess(int $likelihood, int $impact): array
    {
        if ($likelihood < 1 || $likelihood > 5 || $impact < 1 || $impact > 5) {
            throw new \InvalidArgumentException('Level kemungkinan dan dampak harus antara 1 dan 5.');
        }
        $score = self::SCORES[$likelihood - 1][$impact - 1];
        // Keep the same score bands as the register assessment preview.
        if ($score <= 5) { $color = 'Biru'; $hex = '#0070c0'; }
        elseif ($score <= 11) { $color = 'Hijau'; $hex = '#92d050'; }
        elseif ($score <= 15) { $color = 'Kuning'; $hex = '#ffff00'; }
        elseif ($score <= 19) { $color = 'Oranye'; $hex = '#ffc000'; }
        else { $color = 'Merah'; $hex = '#ff0000'; }

        return ['score' => $score, 'color' => $color, 'level' => $color, 'hex' => $hex, 'requires_action' => $score >= 12];
    }

    public static function trend(int $baseline, int $score): string
    {
        return $score < $baseline ? 'Menurun' : ($score > $baseline ? 'Meningkat' : 'Tetap');
    }
}
