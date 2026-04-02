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
            'embedding' => 'required|array|size:128',
            'is_first_capture' => 'required|boolean',
        ]);

        $user = $request->user();

        /**
         * ✅ reset embedding lama
         */
        if ($validated['is_first_capture']) {

            FaceEmbedding::where('user_id', $user->id)->delete();

            Log::info('FACE REGISTER RESET', [
                'user_id' => $user->id
            ]);
        }

        FaceEmbedding::create([
            'user_id' => $user->id,
            'embedding' => array_values(
                array_map('floatval', $validated['embedding'])
            ),
        ]);

        $total = FaceEmbedding::where('user_id', $user->id)->count();

        return response()->json([
            'message' => "Capture berhasil ($total/5)",
            'total_capture' => $total,
            'completed' => $total >= 5,
        ]);
    }

    // //Verifikasi Wajah
    public function verify(Request $request)
    {
        $validated = $request->validate([
            'embedding' => 'required|array|size:128',
        ]);

        $user = $request->user();

        // ambil semua embedding milik user
        $storedEmbeddings = FaceEmbedding::where('user_id', $user->id)->get();

        if ($storedEmbeddings->isEmpty()) {
            return response()->json([
                'message' => 'Belum ada data wajah',
                'match' => false,
            ], 404);
        }

        // normalize incoming embedding
        $incoming = $this->normalizeEmbedding($validated['embedding']);

        $scores = [];

        foreach ($storedEmbeddings as $saved) {

            $stored = $this->normalizeEmbedding($saved->embedding);

            $score = $this->cosineSimilarity($incoming, $stored);

            Log::info('FACE VERIFY SCORE', [
                'user_id' => $user->id,
                'score' => $score,
            ]);

            $scores[] = $score;
        }

        // ================= DECISION STRATEGY =================

        $bestScore = max($scores);

        // minimal berapa embedding harus lolos
        $threshold = 0.80;
        $requiredPass = 2;

        $passCount = 0;

        foreach ($scores as $s) {
            if ($s >= $threshold) {
                $passCount++;
            }
        }

        $match = $passCount >= $requiredPass;

        Log::info('FACE VERIFY RESULT', [
            'best_score' => $bestScore,
            'pass_count' => $passCount,
            'match' => $match,
        ]);

        return response()->json([
            'message' => $match ? 'WAJAH COCOK' : 'WAJAH TIDAK COCOK',
            'match' => $match,
            'score' => round($bestScore, 4),
            'passed_embedding' => $passCount,
            'threshold' => $threshold,
        ]);
    }

    // Normalize Embedding
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

    // Cosine Similarity
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

        $similarity = $dot / $denominator;

        // clamp value
        return max(-1.0, min(1.0, $similarity));
    }
}
