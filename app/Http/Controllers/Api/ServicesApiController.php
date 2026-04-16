<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ShareCoffre;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ServicesApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $services = [];

        $coffres = $user->coffres()->with('elements')->get();
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

        $partages = ShareCoffre::with(['coffre.elements'])
            ->where('destinataire_id', $user->id)
            ->where('statut', 'accepte')
            ->get();

        foreach ($partages as $share) {
            $coffre = $share->coffre;
            if (!$coffre) continue;

            $elementIds = $share->element_ids ? json_decode($share->element_ids, true) : null;
            $elements = $elementIds ? $coffre->elements->whereIn('id', $elementIds)
                : $coffre->elements;

            foreach ($elements as $element) {
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
                    'partage' => 'true',
                    'proprietaire' => $share->proprietaire->name ?? '-',
                    'coffre' => [
                        'id' => $coffre->id,
                        'nom' => $coffre->nom,
                        'data_key_encrypted' => $coffre->data_key_destinataire_encrypted,
                        'data_key_type' => 'rsa',
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
