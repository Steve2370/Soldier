<?php

use App\Models\User;
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
} else {
    throw new RuntimeException('Unknown browser-test operation.');
}
