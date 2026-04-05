<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ServicesApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $coffres = $user->coffres()->with('elements')->get();
        $services = [];

        foreach ($coffres as $coffre) {
            foreach ($coffre->elements as $element) {
                $services[] = [
                    'id' => $element->id,
                    'label' => $element->label,
                    'url' => $element->url,
                    'favicon_url' => $element->favicon_url,
                    'type' => $element->type,
                    'favori' => $element->favori,
                    'payload_encrypted' => $element->payload_encrypted,
                    'iv' => $element->iv,
                    'auth_tag' => $element->auth_tag,
                    'coffre' => [
                        'id' => $coffre->id,
                        'nom' => $coffre->nom,
                        'data_key_encrypted' => $coffre->data_key_encrypted,
                    ],
                ];
            }
        }

        return response()->json([
            'services' => $services,
            'total' => count($services),
        ]);
    }
}
