<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Profile\ProfileJsonService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function __construct(
        protected ProfileJsonService $profileService,
    ) {}

    public function self(Request $request): JsonResponse
    {
        $user = Auth::user();
        abort_unless($user, 401);

        $profile = $this->profileService->buildForUser($user);

        return response()->json([
            'profile' => $profile,
        ]);
    }

    public function signed(Request $request): JsonResponse
    {
        if (! $request->hasValidSignature()) {
            abort(403);
        }

        $userId = (int) $request->query('user');
        abort_if($userId <= 0, 422, 'Invalid user');

        $profile = $this->profileService->buildForUser($userId);

        return response()->json([
            'profile' => $profile,
        ]);
    }

    public function generateSigned(Request $request): JsonResponse
    {
        $user = Auth::user();
        abort_unless($user, 401);

        $url = \URL::temporarySignedRoute('api.profile.signed', now()->addDays(30), [
            'user' => $user->id,
        ]);

        return response()->json([
            'url' => $url,
            'expires_at' => now()->addDays(30)->toIso8601String(),
        ]);
    }
}
