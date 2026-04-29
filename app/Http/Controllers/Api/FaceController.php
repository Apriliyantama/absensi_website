<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\FaceEmbedding;
use App\Services\FaceService;

class FaceController extends Controller
{
    protected $faceService;

    public function __construct(FaceService $faceService)
    {
        $this->faceService = $faceService;
    }

    // ================= REGISTER =================
    public function register(Request $request)
    {
        $validated = $request->validate([
            'embedding' => 'required|array|size:512',
            'is_first_capture' => 'required|boolean',
        ]);

        $user = $request->user();

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

    // ================= VERIFY =================
    public function verify(Request $request)
    {
        $mode = config('app.attendance_mode');

        $validated = $request->validate([
            'embedding' => 'required|array|size:512',
        ]);

        $user = $request->user();

        // TEST MODE SUPPORT
        if (!$user && $mode === 'test') {
            $userId = $request->input('user_id');

            if (!$userId) {
                return response()->json([
                    'message' => 'user_id required for testing'
                ], 400);
            }

            $user = (object) ['id' => $userId];
        }

        if (!$user) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }

        // ================= AMBIL SETTING =================
        if ($mode === 'production') {
            $setting = \App\Models\AttendanceSetting::first();

            $threshold = $setting->face_threshold ?? 0.78;
            $requiredPass = $setting->face_required_pass ?? 3;
        } else {
            $threshold = 0.70;
            $requiredPass = 1;
        }

        // ================= PANGGIL SERVICE =================
        $result = $this->faceService->verifyEmbedding(
            $user->id,
            $validated['embedding']
        );

        $scores = $result['scores'];
        $bestScore = $result['best_score'];

        // ================= HITUNG PASS =================
        $passCount = 0;

        foreach ($scores as $s) {
            if ($s >= $threshold) {
                $passCount++;
            }
        }

        $match = $passCount >= $requiredPass;

        // ================= LOG =================
        Log::info('FACE VERIFY DETAIL', [
            'user_id' => $user->id,
            'scores' => $scores,
            'best_score' => $bestScore,
            'threshold' => $threshold,
            'pass_count' => $passCount,
            'match' => $match,
        ]);

        return response()->json([
            'message' => $match ? 'WAJAH COCOK' : 'WAJAH TIDAK COCOK',
            'match' => $match,
            'mode' => $mode,
            'score' => round($bestScore, 4),
            'passed_embedding' => $passCount,
            'threshold' => $threshold,
        ]);
    }
}