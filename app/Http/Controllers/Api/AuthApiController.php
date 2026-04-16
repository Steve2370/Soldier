<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Coffre\CleManagementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Exceptions\ThrottleRequestsException;

class AuthApiController extends Controller
{
    public function __construct(
        private readonly CleManagementService $cleManagement,
    ) {}

    /**
     * @throws \SodiumException
     */
    public function login(Request $request): JsonResponse
    {
        $key = 'login:' . str($request->email)->lower() . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            return response()->json([
                'error' => "Trop de tentatives. Réessayez dans {$seconds} secondes."
            ], 429);
        }

        RateLimiter::hit($key, 300);

        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['nullable', 'string'],
            'master_password' => ['required', 'string'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['error' => 'Identifiants incorrects.'], 401);
        }

        if (!$user->oauth_provider) {
            if (!$request->password || !Hash::check($request->password, $user->password)) {
                return response()->json(['error' => 'Identifiants incorrects.'], 401);
            }
        }

        try {
            $cles = $this->cleManagement->deverouillerCles($user, $request->master_password);
            sodium_memzero($cles['kek']);
            sodium_memzero($cles['cle_privee']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Master password incorrect.'], 401);
        }

        $cleUser = $user->clesUser;
        if (!$cleUser) {
            return response()->json(['error' => 'Coffre non initialisé.'], 422);
        }

        $coffre = $user->coffres()->first();
        if (!$coffre) {
            $clesTemp = $this->cleManagement->deverouillerCles($user, $request->master_password);
            $coffre = app(\App\Services\Coffre\CoffreService::class)->creerCoffre($user, [
                'nom'    => 'Mon coffre',
                'couleur' => '#217eaa',
            ], $clesTemp['kek']);
            sodium_memzero($clesTemp['kek']);
        }

        RateLimiter::clear($key);
        $token = $user->createToken('extension-chrome', ['read:services'])->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'kdf' => [
                'sel' => $cleUser->kdf_salt,
                'algorithme' => $cleUser->kdf_algorithme,
                'params' => $cleUser->kdf_params,
            ],
            'coffre' => [
                'encrypted_kek' => $cleUser->encrypted_kek,
                'data_key_encrypted' => $coffre->data_key_encrypted,
                'verification' => $cleUser->verification_master_key,
            ],
            'cle_privee_chiffree' => $cleUser->encrypted_private_key,
        ]);
    }

    public function oauthData(Request $request): JsonResponse
    {
        $user = $request->user();

        $cleUser = $user->clesUser;
        if (!$cleUser) {
            return response()->json(['error' => 'Coffre non initialisé.'], 422);
        }

        $coffre = $user->coffres()->first();
        if (!$coffre) {
            return response()->json(['error' => 'Coffre introuvable.'], 422);
        }

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $user->avatar ? 'https://soldierkey.com' . \Storage::url($user->avatar) : null,
            ],
            'kdf' => [
                'sel' => $cleUser->kdf_salt,
                'algorithme' => $cleUser->kdf_algorithme,
                'params' => $cleUser->kdf_params,
            ],
            'coffre' => [
                'encrypted_kek' => $cleUser->encrypted_kek,
                'data_key_encrypted' => $coffre->data_key_encrypted,
                'verification' => $cleUser->verification_master_key,
            ],
            'cle_privee_chiffree' => $cleUser->encrypted_private_key,
        ]);
    }

    public function profil(Request $request): JsonResponse
    {
        $user = $request->user();
        $cleUser = $user->clesUser;

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'kdf' => [
                'sel' => $cleUser->kdf_salt,
                'algorithme' => $cleUser->kdf_algorithme,
                'params' => $cleUser->kdf_params,
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Déconnecté.']);
    }
}
