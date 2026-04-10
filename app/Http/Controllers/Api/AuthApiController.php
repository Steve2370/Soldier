<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\InvalidMasterPasswordException;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Coffre\CleManagementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Testing\Fluent\Concerns\Has;

class AuthApiController extends Controller
{
    public function __construct(
        private readonly CleManagementService $cleManagement,
    ) {}

    public function login(Request $request): JsonResponse
    {
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
            return response()->json(['error' => 'Coffre introuvable.'], 422);
        }

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
        ]);
    }

    public function profil(Request $request): JsonResponse
    {
        $user = $request->user();
        $cleUser = $user->clesUser();

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
