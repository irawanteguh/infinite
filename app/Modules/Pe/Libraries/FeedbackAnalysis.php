<?php
namespace Modules\Pe\Libraries;

class FeedbackAnalysis
{
    public static function summarize(array $comments): array
    {
        $stopwords = array_fill_keys(explode(' ', 'yang dan di ke dari untuk dengan pada dalam ini itu saya kami kita anda pasien rumah sakit rsud skm prem dummy mohon agar supaya lebih sangat sudah telah akan juga masih bisa dapat adalah ada tidak bukan belum jangan kurang tanpa ya pak bu terima kasih atas serta atau sebagai secara saat hari kali pelayanan petugas'), true);
        $positive = array_fill_keys(explode(' ', 'baik bagus ramah keramahan cepat bersih nyaman puas membantu profesional jelas mudah terjangkau sopan tanggap responsif memuaskan mantap'), true);
        $negative = array_fill_keys(explode(' ', 'buruk lambat lama kotor mahal bingung sulit kecewa kasar jutek ribet sesak rusak terlambat mengecewakan'), true);
        $sentiments = ['positive' => 0, 'neutral' => 0, 'negative' => 0];
        $words = [];
        $total = 0;
        foreach ($comments as $comment) {
            $text = trim((string) $comment);
            if ($text === '' || !preg_match('/\p{L}/u', $text)) continue;
            $total++;
            $text = mb_strtolower(strip_tags($text), 'UTF-8');
            $text = preg_replace('/[\w.+-]+@[\w.-]+\.[a-z]{2,}/u', ' ', $text);
            preg_match_all('/\p{L}+/u', $text, $matches);
            $tokens = $matches[0];
            $score = 0;
            $seen = [];
            foreach ($tokens as $index => $word) {
                if (isset($positive[$word]) || isset($negative[$word])) {
                    $weight = isset($positive[$word]) ? 1 : -1;
                    $previous = array_slice($tokens, max(0, $index - 2), min(2, $index));
                    if (array_intersect($previous, ['tidak', 'kurang', 'belum', 'bukan'])) $weight *= -1;
                    if ($weight > 0 && in_array('lebih', $previous, true) && array_intersect($tokens, ['mohon', 'harap', 'tolong', 'agar'])) $weight = 0;
                    $score += $weight;
                }
                if (mb_strlen($word, 'UTF-8') < 3 || isset($stopwords[$word])) continue;
                $seen[$word] = true;
            }
            foreach ($seen as $word => $_) $words[$word] = ($words[$word] ?? 0) + 1;
            $sentiments[$score > 0 ? 'positive' : ($score < 0 ? 'negative' : 'neutral')]++;
        }
        arsort($words);
        $cloud = [];
        foreach (array_slice($words, 0, 50, true) as $word => $count) $cloud[] = ['word' => $word, 'count' => $count];
        return ['total' => $total, 'sentiments' => $sentiments, 'words' => $cloud];
    }
}
