<?php

namespace App\Http\Controllers;

use App\Models\ElementCoffre;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class TrashController extends Controller
{
    public function index(Request $request): View
    {
        $elements = ElementCoffre::onlyTrashed()
            ->whereHas('coffre', fn ($q) => $q->where('user_id', $request->user()->id))
            ->where('deleted_at', '>', now()->subDays(30))
            ->latest('deleted_at')->paginate(50);

        return view('dashboard.corbeille', compact('elements'));
    }

    public function restore(Request $request, int $id): RedirectResponse
    {
        // Restore only one's own items, never an old recipient's copy or an
        // item beyond retention, even if the scheduled purge has not run yet.
        $element = ElementCoffre::onlyTrashed()
            ->whereHas('coffre', fn ($q) => $q->where('user_id', $request->user()->id))
            ->where('deleted_at', '>', now()->subDays(30))->findOrFail($id);
        $element->restore();

        return redirect()->route('corbeille')->with('toast', [
            'type' => 'success', 'titre' => 'Service restauré', 'message' => 'Le service est de nouveau dans votre coffre.',
        ]);
    }
}
