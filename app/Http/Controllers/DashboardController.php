<?php

namespace App\Http\Controllers;

use App\Helpers\SessionHelper;
use App\Http\Requests\StoreElementRequest;
use App\Models\Coffre;
use App\Models\ElementCoffre;
use App\Services\Coffre\CleManagementService;
use App\Services\Coffre\CoffreService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private readonly CoffreService $coffreService,
        private readonly CleManagementService $cleManagement,
    ) {}

    /**
     * @throws \SodiumException
     */
    public function index(): View
    {
        $user = auth()->user();
        $kek = SessionHelper::obtenirKek();

        if ($kek === null) {
            return view('dashboard.index', ['services' => collect()]);
        }
        $coffres = $user->coffres()->withCount('elements')->get();

        $services = $coffres->map(function (Coffre $coffre) use ($kek) {
            $dataKey = $this->cleManagement->dechiffrerDataKeyCoffre(
                $coffre->data_key_encrypted,
                $kek
            );
            $elements = $this->coffreService->listerElements($coffre, $dataKey);
            sodium_memzero($dataKey);

            return [
                'coffre' => $coffre,
                'elements' => $elements,
            ];
        });

        sodium_memzero($kek);

        return view('dashboard.index', compact('services'));
    }

    public function creer(): View
    {
        return view('dashboard.creer');
    }

    /**
     * @throws \SodiumException
     */
    public function stocker(StoreElementRequest $request): RedirectResponse
    {
        $user = auth()->user();
        $kek = SessionHelper::obtenirKek();
        $coffre = $user->coffres()->first()
            ?? $this->coffreService->creerCoffre($user, [
                'nom' => 'Mon coffre',
                'couleur' => '#03A63C',
            ], $kek);

        $dataKey = $this->cleManagement->dechiffrerDataKeyCoffre($coffre->data_key_encrypted, $kek);
        $faviconUrl = $this->coffreService->resoudreFavicon($request->validated('url') ?? '');

        $this->coffreService->ajouterElement($coffre, array_merge(
            $request->validated(),
            ['favicon_url' => $faviconUrl]
        ), $dataKey);

        sodium_memzero($dataKey);
        sodium_memzero($kek);

        return redirect()->route('dashboard')
            ->with('toast', [
                'type' => 'success',
                'titre' => 'Service ajouté',
                'message' => "« {$request->validated('label')} » a été enregistré.",
            ]);
    }

    /**
     * @throws \SodiumException
     */
    public function afficher(ElementCoffre $element): View
    {
        $this->verifierAcces($element);

        $kek = SessionHelper::obtenirKek();
        $dataKey = $this->cleManagement->dechiffrerDataKeyCoffre(
            $element->coffre->data_key_encrypted,
            $kek
        );
        $donnees = $this->coffreService->lireElement($element, $dataKey);

        sodium_memzero($dataKey);
        sodium_memzero($kek);

        return view('dashboard.afficher', compact('element', 'donnees'));
    }

    /**
     * @throws \SodiumException
     */
    public function modifier(ElementCoffre $element): View
    {
        $this->verifierAcces($element);

        $kek = SessionHelper::obtenirKek();
        $dataKey = $this->cleManagement->dechiffrerDataKeyCoffre(
            $element->coffre->data_key_encrypted,
            $kek
        );
        $donnees = $this->coffreService->lireElement($element, $dataKey);

        sodium_memzero($dataKey);
        sodium_memzero($kek);

        return view('dashboard.modifier', compact('element', 'donnees'));
    }

    /**
     * @throws \SodiumException
     */
    public function mettreAJour(StoreElementRequest $request, ElementCoffre $element): RedirectResponse
    {
        $this->verifierAcces($element);

        $kek = SessionHelper::obtenirKek();
        $dataKey = $this->cleManagement->dechiffrerDataKeyCoffre(
            $element->coffre->data_key_encrypted,
            $kek
        );
        $faviconUrl = $this->coffreService->resoudreFavicon($request->validated('url') ?? '');

        $this->coffreService->mettreAJourElement($element, array_merge(
            $request->validated(),
            ['favicon_url' => $faviconUrl]
        ), $dataKey);

        sodium_memzero($dataKey);
        sodium_memzero($kek);

        return redirect()->route('dashboard')
            ->with('toast', [
                'type' => 'success',
                'titre' => 'Service mis à jour',
                'message' => "« {$request->validated('label')} » a été modifié.",
            ]);
    }

    public function supprimer(ElementCoffre $element): RedirectResponse
    {
        $this->verifierAcces($element);

        $label = $element->label;
        $this->coffreService->supprimerElement($element);

        return redirect()->route('dashboard')
            ->with('toast', [
                'type' => 'warning',
                'titre' => 'Service supprimé',
                'message' => "« {$label} » a été déplacé dans la corbeille.",
            ]);
    }

    public function toggleFavori(ElementCoffre $element): JsonResponse
    {
        $this->verifierAcces($element);
        $favori = $this->coffreService->toggleFavori($element);

        return response()->json(['favori' => $favori]);
    }

    private function verifierAcces(ElementCoffre $element): void
    {
        if ($element->coffre->user_id !== auth()->id()) {
            abort(403, 'Accès non autorisé.');
        }
    }
}
