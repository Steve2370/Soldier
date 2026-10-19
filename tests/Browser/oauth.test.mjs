import assert from 'node:assert/strict';
import { after, before, test } from 'node:test';
import { execFileSync, spawn } from 'node:child_process';
import { mkdtempSync, mkdirSync } from 'node:fs';
import { tmpdir } from 'node:os';
import { join, resolve } from 'node:path';
import { createServer as createHttpServer } from 'node:http';
import { createServer as createProbe } from 'node:net';

const { chromium } = await import(process.env.SOLDIER_PLAYWRIGHT_MODULE ?? 'playwright');
const root = resolve(import.meta.dirname, '../..');
const master = 'Synthetic-Google-Master-2026!';
const apps = [];
const allowedCallbacks = new Set();
let browser, providerServer, providerOrigin, lax;

async function startApp(sameSite) {
    const directory = mkdtempSync(join(tmpdir(), 'soldier-e2e-oauth-'));
    mkdirSync(join(directory, 'views'));
    const probe = createProbe();
    await new Promise(resolve => probe.listen(0, '127.0.0.1', resolve));
    const port = probe.address().port;
    await new Promise(resolve => probe.close(resolve));
    const origin = `http://localhost:${port}`;
    allowedCallbacks.add(origin + '/auth/google/callback');
    const env = { ...process.env, SOLDIER_E2E_DIRECTORY: directory, SOLDIER_E2E_PROVIDER_ORIGIN: providerOrigin,
        APP_ENV: 'local', APP_DEBUG: 'false', APP_KEY: `base64:${Buffer.alloc(32, 97).toString('base64')}`, APP_URL: origin,
        DB_CONNECTION: 'sqlite', DB_DATABASE: join(directory, 'database.sqlite'), DB_URL: '',
        APP_CONFIG_CACHE: join(directory, 'config.php'), APP_ROUTES_CACHE: join(directory, 'routes.php'),
        VIEW_COMPILED_PATH: join(directory, 'views'), SESSION_DRIVER: 'database', SESSION_DOMAIN: '',
        SESSION_SECURE_COOKIE: 'true', SESSION_HTTP_ONLY: 'true', SESSION_SAME_SITE: sameSite,
        SESSION_COOKIE: 'soldier_oauth_test', CACHE_STORE: 'database', MAIL_MAILER: 'array',
        QUEUE_CONNECTION: 'sync', LOG_CHANNEL: 'null',
        STRIPE_KEY: '', STRIPE_SECRET: '', STRIPE_WEBHOOK_SECRET: '', STRIPE_PRICE_FAMILLE: '',
        GOOGLE_CLIENT_ID: '', GOOGLE_CLIENT_SECRET: '', GITHUB_CLIENT_ID: '', GITHUB_CLIENT_SECRET: '',
    };
    execFileSync('php', ['tests/Browser/prepare.php', 'init'], { cwd: root, env });
    const phpProcess = spawn('php', ['-S', `127.0.0.1:${port}`, '-t', join(root, 'public'), join(root, 'tests/Browser/oauth-router.php')], { cwd: root, env });
    let output = '';
    phpProcess.stderr.on('data', chunk => { output += chunk.toString(); });
    const app = { origin, phpProcess, errors: [] };
    apps.push(app);
    let ready = false;
    for (let i = 0; i < 100; i++) {
        try { if ((await fetch(origin + '/up')).ok) { ready = true; break; } } catch {}
        await new Promise(resolve => setTimeout(resolve, 100));
    }
    assert.ok(ready, output);
    app.context = await browser.newContext();
    app.page = await app.context.newPage();
    app.page.setDefaultTimeout(10000);
    app.page.on('pageerror', error => app.errors.push(error.message));
    await app.page.route('**/*', route => {
        const url = new URL(route.request().url());
        return [origin, providerOrigin].includes(url.origin) ? route.continue() : route.abort();
    });
    return app;
}

before(async () => {
    // localhost and 127.0.0.1 are intentionally different sites: unlike a mock
    // callback on the same site, this exercises the browser's SameSite rules.
    providerServer = createHttpServer((request, response) => {
        const url = new URL(request.url, 'http://127.0.0.1');
        const callback = url.searchParams.get('redirect_uri');
        if (url.pathname !== '/google' || ! allowedCallbacks.has(callback)) {
            response.writeHead(400);
            return response.end('Unexpected test callback');
        }
        const destination = new URL(callback);
        destination.searchParams.set('code', 'synthetic-browser-code');
        destination.searchParams.set('state', url.searchParams.get('state'));
        response.writeHead(200, { 'Content-Type': 'text/html' });
        response.end(`<a href="${destination.href.replaceAll('&', '&amp;')}">Continuer vers Soldier</a>`);
    });
    await new Promise(resolve => providerServer.listen(0, '127.0.0.1', resolve));
    providerOrigin = `http://127.0.0.1:${providerServer.address().port}`;
    browser = await chromium.launch({ headless: true, ...(process.env.SOLDIER_BROWSER_EXECUTABLE ? { executablePath: process.env.SOLDIER_BROWSER_EXECUTABLE } : {}) });
    lax = await startApp('lax');
});

after(async () => {
    await browser?.close();
    for (const app of apps) app.phpProcess.kill();
    if (providerServer) await new Promise(resolve => providerServer.close(resolve));
});

async function continueWithGoogle(app, sameSite) {
    await app.page.getByRole('link', { name: 'Continuer avec Google', exact: true }).click();
    await app.page.waitForURL(url => url.origin === providerOrigin);
    const cookie = (await app.context.cookies(app.origin)).find(cookie => cookie.name === 'soldier_oauth_test');
    assert.ok(cookie);
    assert.equal(cookie.sameSite, sameSite);
    assert.equal(cookie.secure, true);
    assert.equal(cookie.httpOnly, true);
    const callbackRequest = app.page.waitForRequest(request => new URL(request.url()).pathname === '/auth/google/callback');
    await app.page.getByRole('link', { name: 'Continuer vers Soldier', exact: true }).click();
    const request = await callbackRequest;
    const headers = await request.allHeaders();
    assert.equal((headers.cookie ?? '').includes('soldier_oauth_test='), sameSite === 'Lax', 'Check the actual cookie sent on the cross-site callback');
    const response = await request.response();
    assert.equal(response.status(), 302);
    assert.equal(new URL(response.headers().location).pathname, sameSite === 'Lax' ? '/oauth/master-password' : '/connexion');
    await app.page.waitForURL(url => url.origin === app.origin);
}

test('new Google account returns cross-site to master-password setup without an email or account password', async () => {
    const { page, origin } = lax;
    await page.goto(origin + '/inscription');
    await continueWithGoogle(lax, 'Lax');
    await page.getByRole('heading', { name: 'Créer votre Master Password', exact: true }).waitFor();
    assert.equal(await page.locator('input[name=email], input[name=password]').count(), 0);
    await page.goto(origin + '/dashboard');
    await page.waitForURL('**/oauth/master-password');
    const noCsrf = await page.request.post(origin + '/oauth/master-password', { form: { master_password: master, master_password_confirmation: master } });
    assert.equal(noCsrf.status(), 419, 'OAuth must not bypass CSRF on master-password setup');
    await page.locator('[name=master_password]').fill(master);
    await page.locator('[name=master_password_confirmation]').fill(master);
    await page.getByRole('button', { name: 'Créer mon coffre', exact: true }).click();
    await page.waitForURL('**/dashboard');
    assert.deepEqual(lax.errors, []);
});

test('returning Google account needs only the existing master password and remains locked on an incorrect one', async () => {
    const { page, origin } = lax;
    await page.locator('form[action$="/deconnexion"]').first().evaluate(form => form.requestSubmit());
    await page.waitForURL('**/connexion');
    await continueWithGoogle(lax, 'Lax');
    await page.getByRole('heading', { name: 'Déverrouiller le coffre', exact: true }).waitFor();
    assert.equal(await page.locator('input[name=email], input[name=password], input[name=master_password_confirmation]').count(), 0);
    await page.locator('[name=master_password]').fill('Incorrect-Master-Only!');
    await page.getByRole('button', { name: 'Déverrouiller', exact: true }).click();
    await page.getByText('Master password incorrect.', { exact: true }).waitFor();
    await page.goto(origin + '/dashboard');
    await page.waitForURL('**/oauth/master-password');
    await page.locator('[name=master_password]').fill(master);
    await page.getByRole('button', { name: 'Déverrouiller', exact: true }).click();
    await page.waitForURL('**/dashboard');
    assert.deepEqual(lax.errors, []);
});

test('negative control: legacy Strict cookie reproduces the failed Google return and classic login form', async () => {
    const strict = await startApp('strict');
    await strict.page.goto(strict.origin + '/connexion');
    await continueWithGoogle(strict, 'Strict');
    await strict.page.waitForURL('**/connexion');
    // Strict can also omit the replacement cookie on the redirect to /connexion,
    // losing the flashed error. The callback rejection is asserted above.
    await strict.page.locator('input[name=email]').waitFor();
    assert.equal(await strict.page.locator('input[name=email]').count(), 1);
    await strict.page.locator('[name=email]').fill('google-browser@example.test');
    await strict.page.getByRole('button', { name: 'Continuer →', exact: true }).click();
    await strict.page.waitForURL('**/connexion/password');
    assert.equal(await strict.page.locator('input[name=password], input[name=master_password]').count(), 2);
    assert.deepEqual(strict.errors, []);
});
