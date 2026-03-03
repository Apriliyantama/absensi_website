<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\FaceEmbedding;

class FaceController extends Controller
{
    // Daftarkan Wajah
    public function register(Request $request)
    {
        $validated = $request->validate([
            'embedding' => 'required|array',
        ]);

        $user = $request->user(); // sanctum user

        $face = FaceEmbedding::updateOrCreate(
            ['user_id' => $user->id],
            [
                'embedding' => $validated['embedding'],
                'embedding_version' => 'facenet-v1',
            ]
        );

        return response()->json([
            'message' => 'Wajah berhasil disimpan',
            'face_embedding_id' => $face->id,
            'user_id' => $face->user_id,
        ], 201);
    }

    // //Verifikasi Wajah
    public function verify(Request $request)
    {
        $validated = $request->validate([
            'embedding' => 'required|array',
        ]);

        $user = $request->user();
        $stored = FaceEmbedding::where('user_id', $user->id)->first();

        if (!$stored) {
            return response()->json([
                'message' => 'Belum ada data wajah terdaftar',
                'match' => 'false',
                'score' => 'null',
                'threshold' => 0.75,
            ], 404);
        }

        Log::info('VERIFY COMPARE', [
            'user_id' => $user->id,
            'incoming_head5' => array_slice($validated['embedding'], 0, 5),
            'stored_head5' => array_slice($stored->embedding, 0, 5),
            'incoming_count' => count($validated['embedding']),
            'stored_count' => count($stored->embedding),
        ]);

        $score = $this->cosineSimilarity($validated['embedding'], $stored->embedding);
        $threshold = 0.75; // tuning nanti
        $match = $score >= $threshold;

        return response()->json([
            'message' => $match ? 'WAJAH COCOK' : 'WAJAH TIDAK COCOK',
            'match' => $match,
            'score' => round($score, 6),
            'threshold' => $threshold,
        ], 200);
    }

    // Helper Cosine Similarity
    private function cosineSimilarity(array $a, array $b): float
    {
        // paksa index 0..n-1 & float
        $a = array_values(array_map('floatval', $a));
        $b = array_values(array_map('floatval', $b));

        $n = min(count($a), count($b));
        if ($n === 0) return 0.0;

        $dot = 0.0;
        $normA = 0.0;
        $normB = 0.0;

        for ($i = 0; $i < $n; $i++) {
            $x = $a[$i];
            $y = $b[$i];

            $dot += $x * $y;
            $normA += $x * $x;
            $normB += $y * $y;
        }

        $den = sqrt($normA) * sqrt($normB);
        if ($den <= 0.0) return 0.0;

        $sim = $dot / $den;

        // clamp untuk floating error
        if ($sim > 1.0) $sim = 1.0;
        if ($sim < -1.0) $sim = -1.0;

        return $sim;
    }
    // private function cosineSimilarity(array $a, array $b): float
    // {
    //     $n = min(count($a), count($b));
    //     if ($n === 0) return 0.0;

    //     $dot = 0.0;
    //     $normA = 0.0;
    //     $normB = 0.0;

    //     for ($i = 0; $i < $n; $i++) {
    //         $x = (float) $a[$i];
    //         $y = (float) $b[$i];

    //         $dot += $x * $y;
    //         $normA += $x * $y;
    //         $normB += $x * $y;
    //     }

    //     $den = sqrt($normA) * sqrt($normB);
    //     if ($den === 0.0) return 0.0;

    //     return $dot / $den;
    // }
}
