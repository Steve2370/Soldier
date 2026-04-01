<?php

namespace App\Http\Controllers;

use App\Exceptions\InvalidMasterPasswordException;
use App\Helpers\SessionHelper;
use App\Http\Requests\Auth\ConnexionRequest;
use App\Http\Requests\Auth\InscriptionRequest;
use App\Models\User;
use App\Services\Auth\MfaService;
use App\Services\Coffre\CleManagementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function __construct(
        private readonly CleManagementService $cleManagement
    ) {}

    public function showInscription(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.inscription');
    }

    /**
     * @throws \SodiumException
     */
    public function inscrire(InscriptionRequest $request): RedirectResponse
    {
        # Dans AuthController::inscrire(), ajoute temporairement :
        \Log::info('inscrire request', [
            'all'       => $request->all(),
            'validated' => $request->validated(),
        ]);

        $user = User::create([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'password' => $request->validated('password'),
        ]);

        $this->cleManagement->initialiserClesUser($user, $request->validated('master_password'));
        Auth::login($user);

        $cles = $this->cleManagement->deverouillerCles($user, $request->validated('master_password'));

        $request->session()->regenerate();
        SessionHelper::deverouiller($cles['kek'], $cles['cle_privee']);
        sodium_memzero($cles['kek']);

        return redirect()->route('dashboard')
            ->with('toast', [
                'type' => 'success',
                'titre' => 'Bienvenue !!!',
                'message' => 'Votre compte a été créé avec succès.',
            ]);
    }

    public function showConnexion(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.connexion-email');
    }

    public function verifierEmail(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return back()->withErrors([
                'email' => 'Ces identifiants ne correspondent à aucun compte.',
            ]);
        }

        session(['login_email' => $request->email]);
        return redirect()->route('connexion.password');
    }

    public function showPassword(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        if (!session('login_email')) {
            return redirect()->route('connexion');
        }

        return view('auth.connexion-password');
    }

    /**
     * @throws \SodiumException
     */
    public function connecter(ConnexionRequest $request): RedirectResponse
    {
        \Log::info('connecter request', [
            'password' => substr($request->password, 0, 3) . '***',
            'master_password' => substr($request->master_password ?? 'NULL', 0, 3) . '***',
            'has_master' => $request->has('master_password'),
            'email_session' => session('login_email'),
        ]);

        $request->validate([
            'password' => ['required', 'string'],
            'master_password' => ['required', 'string'],
        ]);

        $email = session('login_email');

        if (!$email) {
            return redirect()->route('connexion');
        }

        if (!Auth::attempt(
            ['email' => $email, 'password' => $request->password],
            $request->boolean('remember'))) {
            return back()->withErrors(['password' => 'Mot de passe incorrect.',]);
        }

        $user = Auth::user();

        try {
            $cles = $this->cleManagement->deverouillerCles(
                $user,
                $request->master_password);
        } catch (InvalidMasterPasswordException) {
            Auth::logout();
            return back()->withErrors(['password' => 'Impossible de déverrouiller le coffre.',]);
        }

        $mfaActif = $user->mfa()->where('actif', true)->exists();

        if ($mfaActif) {
            session([
                'mfa_pending_kek' => base64_encode($cles['kek']),
                'mfa_pending_cle_privee' => $cles['cle_privee'],
            ]);

            SessionHelper::mfaUserIdPending($user->id);

            $mfaEmail = $user->mfa()->where('type', 'email')->where('actif', true)->first();
            if ($mfaEmail) {
                app(MfaService::class)->envoyerCodeEmail($user);
            }

            Auth::logout();
            sodium_memzero($cles['kek']);

            return redirect()->route('mfa.verify');
        }

        session()->forget('login_email');
        $request->session()->regenerate();

        SessionHelper::deverouiller($cles['kek'], $cles['cle_privee']);
        sodium_memzero($cles['kek']);

        return redirect()->intended(route('dashboard'))
            ->with('toast', [
                'type' => 'success',
                'titre' => 'Connexion réussie',
                'message' => "Bienvenue, {$user->name} !",
            ]);
    }

    public function showMfa(): View|RedirectResponse
    {
        if (!SessionHelper::obtenirMfaUserIdPending()) {
            return redirect()->route('connexion');
        }

        return view('auth.mfa');
    }

    /**
     * @throws \SodiumException
     */
    public function verifierMfa(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $userId = SessionHelper::obtenirMfaUserIdPending();

        if (!$userId) {
            return redirect()->route('connexion')
                ->withErrors(['code' => 'Session expirée. Reconnectez-vous.']);
        }

        $user = User::findOrFail($userId);
        $mafService = app(MfaService::class);

        if (!$mafService->verifierCode($user, $request->input('code'))) {
            return back()->withErrors(['code' => 'Code incorrect ou expiré.']);
        }

        $kek = base64_decode(session('mfa_pending_kek'));
        $clePrivee = session('mfa_pending_cle_privee');

        session()->forget(['mfa_pending_kek', 'mfa_pending_cle_privee']);

        Auth::loginUsingId($userId);
        $request->session()->regenerate();
        SessionHelper::deverouiller($kek, $clePrivee);
        SessionHelper::marquerMfaVerifie();
        sodium_memzero($kek);

        return redirect()->route('dashboard')
            ->with('toast', [
                'type' => 'success',
                'titre' => 'Connexion réussie',
                'message' => "Bienvenue, {$user->name} !",
            ]);
    }

    public function deconnexion(Request $request): RedirectResponse
    {
        SessionHelper::effacerCles();
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('connexion')
            ->with('toast', [
                'type' => 'info',
                'titre' => 'Déconnecté',
                'message' => 'Vous avez été déconnecté avec succès.'
            ]);
    }
}
