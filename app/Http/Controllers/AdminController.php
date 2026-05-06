<?php
namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class AdminController extends Controller
{
    public function index()
    {
        $totalUsers = User::count();
        $newUsersWeek = User::where('created_at', '>=', now()->subWeek())->count();
        $activeUsers30d = ActivityLog::where('created_at', '>=', now()->subDays(30))
            ->distinct('user_id')->count('user_id');
        $totalServices = \DB::table('elements_coffres')->whereNull('deleted_at')->count();
        $totalShares  = \DB::table('shares_coffre')->where('statut', 'accepte')->count();

        $inscriptionsParJour = User::selectRaw('DATE(created_at) as date, COUNT(*) as total')
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $actionsParType = ActivityLog::selectRaw('action, COUNT(*) as total')
            ->groupBy('action')
            ->orderByDesc('total')
            ->get();

        $recentLogs = ActivityLog::with('user')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        $users = User::orderByDesc('created_at')->get();

        return view('admin.index', compact(
            'totalUsers', 'newUsersWeek', 'activeUsers30d',
            'totalServices', 'totalShares',
            'inscriptionsParJour', 'actionsParType',
            'recentLogs', 'users'
        ));
    }

    public function logs(Request $request)
    {
        $query = ActivityLog::with('user')->orderByDesc('created_at');

        if ($request->user_id) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->action) {
            $query->where('action', $request->action);
        }
        if ($request->date_from) {
            $query->where('created_at', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $query->where('created_at', '<=', $request->date_to . ' 23:59:59');
        }

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
}
