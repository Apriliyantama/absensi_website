<?php

namespace App\Services;

use App\Models\FaceEmbedding;
use Illuminate\Support\Facades\Log;


class FaceService
{
    public function verifyEmbedding(int $userId, array $incoming, ?array $rawCaptures = null): array
    {
        $storedEmbeddings = FaceEmbedding::where('user_id', $userId)->get();

        if ($storedEmbeddings->isEmpty()) {
            return [
                'match' => false,
                'scores' => [],
                'best_score' => 0,
                'pass_count' => 0,
            ];
        }

        // normalize incoming
        $incoming = $this->normalizeEmbedding($incoming);

        Log::info('VERIFY EMBEDDING', [
            'embedding' => $incoming
        ]);

        if (!empty($rawCaptures)) {
            foreach ($rawCaptures as $index => $capture) {
                Log::info('RAW CAPTURE ' . ($index + 1), [
                    'data' => $capture
                ]);
            }
        }

        $scores = [];

        foreach ($storedEmbeddings as $saved) {
            $stored = $this->normalizeEmbedding($saved->embedding);
            $score = $this->cosineSimilarity($incoming, $stored);
            $scores[] = $score;
        }

        $bestScore = max($scores);

        return [
            'scores' => $scores,
            'best_score' => $bestScore,
        ];
    }

    // ================= NORMALIZE =================
    private function normalizeEmbedding(array $vector): array
    {
        $sum = 0.0;

        foreach ($vector as $v) {
            $sum += $v * $v;
        }

        $norm = sqrt($sum);

        if ($norm == 0) {
            return $vector;
        }

        return array_map(function ($v) use ($norm) {
            return $v / $norm;
        }, $vector);
    }

    // ================= COSINE =================
    private function cosineSimilarity(array $a, array $b): float
    {
        $a = array_values(array_map('floatval', $a));
        $b = array_values(array_map('floatval', $b));

        if (count($a) !== count($b)) {
            return 0.0;
        }

        $dot = 0.0;
        $normA = 0.0;
        $normB = 0.0;

        $n = count($a);

        for ($i = 0; $i < $n; $i++) {
            $dot += $a[$i] * $b[$i];
            $normA += $a[$i] * $a[$i];
            $normB += $b[$i] * $b[$i];
        }

        $denominator = sqrt($normA) * sqrt($normB);

        if ($denominator <= 0.0000001) {
            return 0.0;
        }

        return max(-1.0, min(1.0, $dot / $denominator));
    }

    public function verifyWithThreshold(int $userId, array $embedding, float $threshold, int $requiredPass, ?array $rawCaptures = null): array
    {
        $result = $this->verifyEmbedding($userId, $embedding, $rawCaptures);

        $scores = $result['scores'];

        if (empty($scores)) {
            return [
                'match' => false,
                'reason' => 'no_face_data',
                'scores' => [],
                'best_score' => 0,
                'pass_count' => 0,
            ];
        }

        $passCount = 0;

        foreach ($scores as $s) {
            if ($s >= $threshold) {
                $passCount++;
            }
        }

        return [
            'match' => $passCount >= $requiredPass,
            'scores' => $scores,
            'best_score' => $result['best_score'],
            'pass_count' => $passCount,
        ];
    }
}