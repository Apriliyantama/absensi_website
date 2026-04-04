<?php

namespace App\Services;

use App\Models\FaceEmbedding;
use Illuminate\Support\Facades\Log;

class FaceService
{
    public function verifyEmbedding(int $userId, array $incoming): float
    {
        $storedEmbeddings = FaceEmbedding::where('user_id', $userId)->get();

        if ($storedEmbeddings->isEmpty()) {
            return 0;
        }

        $scores = [];

        foreach ($storedEmbeddings as $saved) {

            $score = $this->cosineSimilarity(
                $incoming,
                $saved->embedding
            );

            Log::info('FACE VERIFY SCORE', [
                'user_id' => $userId,
                'score' => $score,
            ]);

            $scores[] = $score;
        }

        $bestScore = max($scores);

        Log::info('FACE VERIFY RESULT', [
            'best_score' => $bestScore,
        ]);

        return $bestScore;
    }

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

        if ($denominator == 0) {
            return 0.0;
        }

        return max(-1.0, min(1.0, $dot / $denominator));
    }
}