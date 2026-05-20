<?php
namespace App\Http\Controllers;

use App\Mail\AdminMailMasse;
use App\Models\ActivityLog;
use App\Models\FamilyGroup;
use App\Models\User;
use App\Services\Logs\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Response;

class AdminController extends Controller
{
    public function index()
    {
        $totalUsers = User::count();
        $newUsersWeek = User::where('created_at', '>=', now()->subWeek())->count();
        $activeUsers30d = ActivityLog::where('created_at', '>=', now()->subDays(30))->distinct('user_id')->count('user_id');
        $totalServices = \DB::table('elements_coffres')->whereNull('deleted_at')->count();
        $totalShares = \DB::table('shares_coffre')->where('statut', 'accepte')->count();
        $familyGroups = FamilyGroup::with(['owner', 'members'])->get();
        $totalFamille = $familyGroups->count();

        $inscriptionsParJour = User::selectRaw('DATE(created_at) as date, COUNT(*) as total')
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')->orderBy('date')->get();

        $actionsParType = ActivityLog::selectRaw('action, COUNT(*) as total')
            ->groupBy('action')->orderByDesc('total')->get();

        $recentLogs = ActivityLog::with('user')->orderByDesc('created_at')->limit(10)->get();

        $users = User::withCount(['coffres'])->orderByDesc('created_at')->get();

        return view('admin.index', compact(
            'totalUsers', 'newUsersWeek', 'activeUsers30d',
            'totalServices', 'totalShares',
            'inscriptionsParJour', 'actionsParType',
            'recentLogs', 'users',
            'familyGroups', 'totalFamille'
        ));
    }

    public function logs(Request $request)
    {
        $query = ActivityLog::with('user')->orderByDesc('created_at');

        if ($request->user_id) $query->where('user_id', $request->user_id);
        if ($request->action)  $query->where('action', $request->action);
        if ($request->date_from) $query->where('created_at', '>=', $request->date_from);
        if ($request->date_to)   $query->where('created_at', '<=', $request->date_to . ' 23:59:59');

        $logs = $query->paginate(50);
        $users = User::orderBy('name')->get();
        $actions = ActivityLog::distinct('action')->pluck('action');

        return view('admin.logs', compact('logs', 'users', 'actions'));
    }

    public function exportLogs(Request $request)
    {
        $query = ActivityLog::with('user')->orderByDesc('created_at');

        if ($request->user_id) $query->where('user_id', $request->user_id);
        if ($request->action) $query->where('action', $request->action);
        if ($request->date_from) $query->where('created_at', '>=', $request->date_from);
        if ($request->date_to) $query->where('created_at', '<=', $request->date_to . ' 23:59:59');

        $logs = $query->get();

        $csv = "Date,Utilisateur,Email,Action,Description,IP,User Agent\n";
        foreach ($logs as $log) {
            $csv .= implode(',', [
                    $log->created_at,
                    '"' . ($log->user->name ?? 'Supprimé') . '"',
                    '"' . ($log->user->email ?? '') . '"',
                    $log->action,
                    '"' . str_replace('"', '""', $log->description ?? '') . '"',
                    $log->ip_address,
                    '"' . str_replace('"', '""', $log->user_agent ?? '') . '"',
                ]) . "\n";
        }

        return Response::make($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="soldier-logs-' . now()->format('Y-m-d') . '.csv"',
        ]);
    }

    public function supprimerUser(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.index')->with('toast', [
                'type' => 'error',
                'titre' => 'Action impossible',
                'message' => 'Vous ne pouvez pas supprimer votre propre compte.',
            ]);
        }

        $nom = $user->name;

        $user->coffres()->each(function ($coffre) {
            $coffre->elements()->forceDelete();
            $coffre->delete();
        });
        $user->clesUser()->delete();
        $user->mfa()->delete();
        $user->passkeys()->delete();
        $user->tokens()->delete();
        $user->forceDelete();

        ActivityLogService::log('user_supprime', "Utilisateur supprimé par admin : {$nom}");

        return redirect()->route('admin.index')->with('toast', [
            'type' => 'warning',
            'titre' => 'Utilisateur supprimé',
            'message' => "{$nom} a été supprimé définitivement.",
        ]);
    }

    public function envoyerMail(Request $request): RedirectResponse
    {
        $request->validate([
            'sujet' => ['required', 'string', 'max:255'],
            'contenu' => ['required', 'string'],
            'destinataire' => ['required', 'in:tous,abonnes,user'],
            'user_id' => ['nullable', 'exists:users,id'],
        ]);

        $users = match($request->destinataire) {
            'tous' => User::whereNull('deleted_at')->get(),
            'abonnes' => User::whereHas('subscriptions', fn($q) => $q->where('stripe_status', 'active'))->get(),
            'user' => User::where('id', $request->user_id)->get(),
        };

        foreach ($users as $user) {
            Mail::to($user->email)->send(new AdminMailMasse(
                $request->sujet,
                $request->contenu,
                $user->name,
            ));
        }

        ActivityLogService::log('admin_mail_masse', "Mail envoyé à {$users->count()} utilisateur(s) — sujet: {$request->sujet}");

        return back()->with('toast', [
            'type' => 'success',
            'titre' => 'Emails envoyés',
            'message' => "{$users->count()} email(s) envoyé(s) avec succès.",
        ]);
    }
}
