import assert from 'node:assert/strict';
import { after, afterEach, before, test } from 'node:test';
import { execFileSync, spawn } from 'node:child_process';
import { mkdtempSync, mkdirSync } from 'node:fs';
import { tmpdir } from 'node:os';
import { join, resolve } from 'node:path';
import { createServer } from 'node:net';

const { chromium } = await import(process.env.SOLDIER_PLAYWRIGHT_MODULE ?? 'playwright');
const root = resolve(import.meta.dirname, '../..');
const master = 'Browser-Master-Only-2026!';
let env, origin, server, browser, page;
const errors = [];
let serverOutput = '';

before(async () => {
    const directory = mkdtempSync(join(tmpdir(), 'soldier-e2e-'));
    mkdirSync(join(directory, 'views'));
    const probe = createServer();
    await new Promise(resolve => probe.listen(0, '127.0.0.1', resolve));
    const port = probe.address().port;
    await new Promise(resolve => probe.close(resolve));
    origin = `http://localhost:${port}`;
    env = { ...process.env, SOLDIER_E2E_DIRECTORY: directory, APP_ENV: 'local', APP_DEBUG: 'false',
        APP_KEY: `base64:${Buffer.alloc(32, 97).toString('base64')}`, APP_URL: origin,
        DB_CONNECTION: 'sqlite', DB_DATABASE: join(directory, 'database.sqlite'), DB_URL: '',
        APP_CONFIG_CACHE: join(directory, 'config.php'), APP_ROUTES_CACHE: join(directory, 'routes.php'),
        VIEW_COMPILED_PATH: join(directory, 'views'), SESSION_DRIVER: 'database', SESSION_DOMAIN: '',
        SESSION_SECURE_COOKIE: 'false', SESSION_COOKIE: 'soldier_browser_test', CACHE_STORE: 'database',
        MAIL_MAILER: 'array', QUEUE_CONNECTION: 'sync', LOG_CHANNEL: 'null',
        STRIPE_KEY: '', STRIPE_SECRET: '', STRIPE_WEBHOOK_SECRET: '',
        GOOGLE_CLIENT_ID: '', GOOGLE_CLIENT_SECRET: '', GITHUB_CLIENT_ID: '', GITHUB_CLIENT_SECRET: '',
    };
    execFileSync('php', ['tests/Browser/prepare.php', 'init'], { cwd: root, env });
    server = spawn('php', ['-S', `127.0.0.1:${port}`, join(root, 'vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php')], { cwd: join(root, 'public'), env });
    server.stderr.on('data', chunk => { serverOutput += chunk.toString(); });
    let ready = false;
    for (let i = 0; i < 100; i++) {
        try { if ((await fetch(`${origin}/up`)).ok) { ready = true; break; } } catch {}
        await new Promise(resolve => setTimeout(resolve, 100));
    }
    assert.ok(ready, serverOutput);
    browser = await chromium.launch({ headless: true, ...(process.env.SOLDIER_BROWSER_EXECUTABLE ? { executablePath: process.env.SOLDIER_BROWSER_EXECUTABLE } : {}) });
    page = await browser.newPage();
    page.setDefaultTimeout(10000);
    page.on('pageerror', error => errors.push(error.message));
    await page.route('**/*', route => {
        const url = new URL(route.request().url());
        if (url.origin !== origin) return route.abort();
        if (url.pathname === '/favicon') return route.fulfill({ status: 204 });
        return route.continue();
    });
});

after(async () => {
    await browser?.close();
    server?.kill();
    // Keep the specifically created temporary directory for diagnostics; never remove broad paths.
});

afterEach(async context => {
    if (! context.passed && page) console.error('Browser diagnostics:', page.url(), errors, await page.locator('.toast-title, .toast-message, .error-msg').allTextContents());
});

test('real registration, email verification and every vault form type', async () => {
    await page.goto(`${origin}/inscription`);
    await page.locator('[name=name]').fill('Browser Audit');
    await page.locator('[name=email]').fill('browser@example.test');
    await page.locator('[name=password]').fill('Browser-Account-Only-2026!');
    await page.locator('[name=password_confirmation]').fill('Browser-Account-Only-2026!');
    await page.locator('[name=master_password]').fill(master);
    await page.locator('form[action$="/inscription"] button[type=submit]').click();
    await page.waitForURL('**/email/verify');
    const verification = execFileSync('php', ['tests/Browser/prepare.php', 'verification'], { cwd: root, env, encoding: 'utf8' });
    await page.goto(verification.trim());
    await page.waitForURL('**/dashboard');

    const forms = [
        ['note', 'Note sécurisée', { contenu: 'Note réellement conservée : secret de test.' }],
        ['autre', 'Autre', { contenu: 'Token de test conservé', notes: 'Détail de test' }],
        ['login', 'Mot de passe', { identifiant: 'browser@example.test', mot_de_passe: 'Secret-Element-2026!' }],
        ['carte', 'Carte', { numero: '4242424242424242', titulaire: 'TEST ONLY', expiration: '12/30', cvv: '123', code_pin: '1234' }],
        ['identite', "Pièce d'identité", { prenom: 'Test', nom: 'Identity', email: 'identity@example.test' }],
        ['cles', 'Clé SSH', { serveur: 'test.invalid', username: 'test', passphrase_ssh: 'SSH-Secret-2026!', cle_privee: 'Synthetic test key, not a real credential' }],
    ];
    for (const [type, label, fields] of forms) {
        const elementName = type === 'autre' ? `Browser autre "quoted" 'apostrophe' <img src=x onerror="window.auditXss=true">` : `Browser ${type}`;
        await page.goto(`${origin}/services/creer`);
        await page.waitForFunction(() => Boolean(window.Alpine));
        await page.getByRole('button', { name: label, exact: true }).click();
        await page.locator('[name=label]').fill(elementName);
        for (const [name, value] of Object.entries(fields)) await page.locator(`[name=${name}]:visible`).fill(value);
        const submitted = await page.locator('form[action$="/services"]').evaluate(form => [...new FormData(form).entries()]);
        if ('contenu' in fields) assert.deepEqual(submitted.filter(([key]) => key === 'contenu'), [['contenu', fields.contenu]], 'Only the selected content field must be submitted');
        await page.getByRole('button', { name: 'Sauvegarder', exact: true }).click();
        await page.waitForURL('**/dashboard');
        await page.locator('.service-card').filter({ has: page.getByText(elementName, { exact: true }) }).locator('a[title="Voir"]').click();
        await page.waitForURL(/\/services\/\d+$/);
        const body = await page.locator('body').textContent();
        for (const [name, value] of Object.entries(fields)) {
            if (! ['mot_de_passe', 'cvv', 'code_pin', 'passphrase_ssh', 'cle_privee'].includes(name)) assert.ok(body.includes(value), `${type}: ${name} was not preserved`);
        }
        const detailUrl = page.url();
        await page.goto(`${detailUrl}/modifier`);
        await page.locator('[name=label]').fill(`Browser ${type} edited`);
        await page.locator('form').filter({ has: page.locator('[name=label]') }).locator('button[type=submit]').click();
        await page.waitForURL('**/dashboard');
        await page.goto(detailUrl);
        assert.ok((await page.locator('body').textContent()).includes(`Browser ${type} edited`));
        assert.equal(await page.evaluate(() => window.auditXss), undefined);
    }
    assert.deepEqual(errors, [], 'Pages must not produce JavaScript errors');
});

test('real generator, settings and trash pages render without script errors', async () => {
    for (const path of ['/generateur', '/settings', '/settings/sessions', '/partage', '/corbeille', '/dashboard']) {
        const response = await page.goto(origin + path);
        assert.equal(response.status(), 200, path);
        await page.waitForTimeout(100);
        assert.equal(new URL(page.url()).pathname, path);
    }
    await page.locator('.service-card').filter({ has: page.getByText('Browser note edited', { exact: true }) }).locator('a[title="Voir"]').click();
    await page.getByRole('button', { name: 'Supprimer', exact: true }).click();
    await page.locator('#modal-suppression-afficher').getByRole('button', { name: /Supprimer/ }).click();
    await page.waitForURL('**/dashboard');
    await page.goto(origin + '/corbeille');
    assert.ok((await page.locator('body').textContent()).includes('Browser note edited'));
    await page.getByRole('button', { name: 'Restaurer' }).click();
    await page.goto(origin + '/dashboard');
    assert.equal(await page.getByText('Browser note edited', { exact: true }).count(), 1);
    const card = page.locator('.service-card').filter({ has: page.getByText('Browser note edited', { exact: true }) });
    const favorite = page.waitForResponse(response => response.url().endsWith('/favori'));
    await card.locator('.favori-btn').click();
    assert.equal((await (await favorite).json()).favori, true);
    await page.getByRole('button', { name: 'Favoris', exact: true }).click();
    await page.waitForFunction(() => [...document.querySelectorAll('.service-card')].filter(card => getComputedStyle(card).display !== 'none').length === 1);
    assert.equal(await page.locator('.service-card:visible').count(), 1);
    assert.deepEqual(errors, []);
});

test('real WebAuthn registration, logout, assertion and vault unlock', async () => {
    await page.goto(origin + '/settings');
    const cdp = await page.context().newCDPSession(page);
    await cdp.send('WebAuthn.enable');
    await cdp.send('WebAuthn.addVirtualAuthenticator', { options: { protocol: 'ctap2', transport: 'internal', hasResidentKey: true, hasUserVerification: true, isUserVerified: true, automaticPresenceSimulation: true } });
    const registration = page.waitForResponse(response => response.url().endsWith('/passkeys/inscrire'));
    await page.evaluate(() => window.inscrirePasskey('Browser virtual authenticator'));
    const response = await registration;
    assert.equal(response.status(), 200, await response.text());
    assert.equal((await response.json()).success, true);
    const csrf = () => page.locator('meta[name="csrf-token"]').getAttribute('content');
    const replay = await page.request.post(origin + '/passkeys/inscrire', { data: response.request().postDataJSON(), headers: { 'X-CSRF-TOKEN': await csrf(), Accept: 'application/json' } });
    assert.equal(replay.status(), 422, 'Attestation challenge must be single-use');
    await page.locator('form[action$="/deconnexion"] button').first().click();
    await page.waitForURL('**/connexion');
    const assertion = page.waitForResponse(response => response.url().endsWith('/passkeys/connecter'));
    await page.evaluate(() => window.connecterAvecPasskey());
    const loggedIn = await assertion;
    assert.equal(loggedIn.status(), 200, await loggedIn.text());
    await page.waitForURL('**/oauth/master-password');
    await page.locator('[name=master_password]').fill(master);
    await page.locator('form[action$="/oauth/master-password"] button[type=submit]').click();
    await page.waitForURL('**/dashboard');
    const assertionReplay = await page.request.post(origin + '/passkeys/connecter', { data: loggedIn.request().postDataJSON(), headers: { 'X-CSRF-TOKEN': await csrf(), Accept: 'application/json' } });
    assert.equal(assertionReplay.status(), 422, 'Assertion challenge must be single-use');
    assert.deepEqual(errors, []);
});

test('mobile navigation opens, closes and reaches the vault', async () => {
    await page.setViewportSize({ width: 390, height: 844 });
    await page.goto(origin + '/dashboard');
    await page.getByRole('button', { name: 'Menu', exact: true }).click();
    await page.waitForFunction(() => document.querySelector('.sidebar').classList.contains('sidebar-open'));
    await page.locator('.sidebar a[href$="/corbeille"]').click();
    await page.waitForURL('**/corbeille');
    await page.getByRole('button', { name: 'Menu', exact: true }).click();
    await page.waitForFunction(() => document.querySelector('.sidebar').classList.contains('sidebar-open'));
    await page.locator('.sidebar a[href$="/dashboard"]').click();
    await page.waitForURL('**/dashboard');
    assert.deepEqual(errors, []);
});
