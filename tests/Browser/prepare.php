<?php

use App\Models\FamilyGroup;
use App\Models\FamilyMember;
use App\Models\InvitationPartage;
use App\Models\User;
use App\Services\Auth\UserRegistrationService;
use App\Services\Coffre\CleManagementService;
use App\Services\Coffre\CoffreService;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\URL;

// This helper is only allowed to operate on the disposable browser-test database.
$directory = getenv('SOLDIER_E2E_DIRECTORY');
$database = getenv('DB_DATABASE');
if (! is_string($directory) || ! preg_match('#^/.*soldier-e2e-[^/]+$#', $directory)
    || ! is_dir($directory) || $database !== $directory.'/database.sqlite'
    || getenv('DB_CONNECTION') !== 'sqlite' || getenv('MAIL_MAILER') !== 'array'
    || getenv('APP_ENV') !== 'local' || getenv('DB_URL') !== '') {
    throw new RuntimeException('Refusing to use a non-isolated browser-test environment.');
}

require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

if (($argv[1] ?? '') === 'init') {
    touch($database);
    Artisan::call('migrate', ['--force' => true]);
    echo Artisan::output();
} elseif (($argv[1] ?? '') === 'verification') {
    $user = User::where('email', 'browser@example.test')->sole();
    echo URL::temporarySignedRoute('verification.verify', now()->addMinutes(5), [
        'id' => $user->id, 'hash' => sha1($user->getEmailForVerification()),
    ]);
} elseif (($argv[1] ?? '') === 'admin') {
    $admin = User::where('email', 'browser@example.test')->sole();
    $admin->forceFill(['is_admin' => true])->save();
    $target = User::factory()->create(['name' => 'Disposable Admin Target', 'email' => 'admin-target@example.test']);
    echo json_encode(['id' => $target->id, 'email' => $target->email], JSON_THROW_ON_ERROR);
} elseif (($argv[1] ?? '') === 'sharing') {
    $owner = User::where('email', 'browser@example.test')->sole();
    $owner->forceFill(['family_granted_at' => now()])->save();
    $group = FamilyGroup::where('owner_id', $owner->id)->sole();
    $members = [];
    foreach (['member-one', 'member-two', 'outsider'] as $name) {
        $result = app(UserRegistrationService::class)->inscrire([
            'name' => 'Browser '.$name, 'email' => $name.'@example.test',
            'password' => 'Synthetic-Account-Only-2026!', 'master_password' => 'Synthetic-Master-Only-2026!',
        ]);
        $recipient = $result['user'];
        $recipient->forceFill(['email_verified_at' => now()])->save();
        sodium_memzero($result['cles']['kek']);
        sodium_memzero($result['cles']['cle_privee']);
        if ($name !== 'outsider') {
            FamilyMember::create(['family_group_id' => $group->id, 'user_id' => $recipient->id, 'role' => 'member']);
            $members[] = $recipient->email;
        }
    }
    $vault = $owner->coffres()->where('id', '!=', $group->coffre_id)->firstOrFail();
    $otherVault = $owner->coffres()->findOrFail($group->coffre_id);
    $keys = app(CleManagementService::class)->deverouillerCles($owner, 'Browser-Master-Only-2026!');
    $key = app(CleManagementService::class)->dechiffrerDataKeyCoffre($otherVault->data_key_encrypted, $keys['kek']);
    $otherElement = app(CoffreService::class)->ajouterElement($otherVault, ['type' => 'note', 'label' => 'Other vault only', 'contenu' => 'Synthetic private note'], $key);
    sodium_memzero($key);
    sodium_memzero($keys['kek']);
    sodium_memzero($keys['cle_privee']);
    echo json_encode(['vault' => $vault->id, 'elements' => $vault->elements()->pluck('id')->all(),
        'otherVault' => $otherVault->id, 'otherElement' => $otherElement->id,
        'members' => $members, 'outsider' => 'outsider@example.test', 'group' => $group->id], JSON_THROW_ON_ERROR);
} elseif (($argv[1] ?? '') === 'sharing-status') {
    $owner = User::where('email', 'browser@example.test')->sole();
    echo InvitationPartage::where('expediteur_id', $owner->id)->orderBy('id')
        ->get(['email_destinataire', 'coffre_id', 'element_ids', 'family_group_id'])->toJson();
} else {
    throw new RuntimeException('Unknown browser-test operation.');
}
