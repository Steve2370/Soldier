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
let env, origin, server, browser, page, sharing;
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
        STRIPE_KEY: '', STRIPE_SECRET: '', STRIPE_WEBHOOK_SECRET: '', STRIPE_PRICE_FAMILLE: '',
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

test('admin grants and revokes complimentary access, confirms deletion, and enforces CSRF', async () => {
    await page.setViewportSize({ width: 1440, height: 1000 });
    assert.equal((await page.request.get(origin + '/admin', { headers: { Accept: 'application/json' } })).status(), 403);
    const target = JSON.parse(execFileSync('php', ['tests/Browser/prepare.php', 'admin'], { cwd: root, env, encoding: 'utf8' }));
    await page.goto(origin + '/admin?tab=users');
    await page.screenshot({ path: join(env.SOLDIER_E2E_DIRECTORY, 'admin-desktop.png'), fullPage: true });
    const row = () => page.locator('tr').filter({ has: page.getByText(target.email, { exact: true }) });
    await row().getByRole('button', { name: 'Offrir le forfait Famille', exact: true }).click();
    await page.waitForURL('**/admin?tab=users');
    await row().getByText('Famille offert', { exact: true }).waitFor();
    await row().locator('summary').filter({ hasText: 'Retirer l’accès offert' }).click();
    await row().locator('[name=confirmation]').check();
    await row().getByRole('button', { name: 'Retirer l’accès offert', exact: true }).click();
    await row().getByRole('button', { name: 'Offrir le forfait Famille', exact: true }).waitFor();

    const csrfFailure = await page.request.post(origin + `/admin/users/${target.id}`, {
        form: { _method: 'DELETE', confirmation_email: target.email }, headers: { Accept: 'application/json' },
    });
    assert.equal(csrfFailure.status(), 419, 'A forged request without a CSRF token must fail');
    await row().locator('summary').filter({ hasText: 'Supprimer le compte' }).click();
    await row().getByRole('button', { name: 'Annuler', exact: true }).click();
    assert.equal(await row().locator('form[action$="/users/' + target.id + '"]').isVisible(), false);
    await row().locator('summary').filter({ hasText: 'Supprimer le compte' }).click();
    await row().locator('[name=confirmation_email]').fill('wrong@example.test');
    await row().getByRole('button', { name: 'Supprimer définitivement', exact: true }).click();
    await page.getByRole('alert').filter({ hasText: 'L’adresse de confirmation' }).waitFor();
    assert.equal(await row().count(), 1);
    await row().locator('summary').filter({ hasText: 'Supprimer le compte' }).click();
    await row().locator('[name=confirmation_email]').fill(target.email);
    await row().getByRole('button', { name: 'Supprimer définitivement', exact: true }).click();
    await page.locator('.toast-title').filter({ hasText: 'Utilisateur supprimé' }).waitFor();
    assert.equal(await row().count(), 0);
    const selfRow = page.locator('tr').filter({ has: page.getByText('browser@example.test', { exact: true }) });
    assert.equal(await selfRow.locator('summary').filter({ hasText: 'Supprimer le compte' }).count(), 0);
    assert.deepEqual(errors, []);
});

test('payment pages handle missing Stripe config honestly and expose a safe coffee link', async () => {
    await page.goto(origin + '/pricing');
    const coffee = page.locator('a[href="https://paypal.me/BriceSteve"]');
    assert.equal(await coffee.count(), 1);
    assert.equal(await coffee.getAttribute('rel'), 'noopener noreferrer');
    await page.locator('form[action$="/pricing/checkout"] button').click();
    await page.getByRole('alert').filter({ hasText: 'Le service de paiement est indisponible' }).waitFor();
    const response = await page.goto(origin + '/pricing/succes?session_id=cs_fake');
    assert.equal(response.status(), 200);
    await page.getByRole('heading', { name: 'Confirmation du paiement en attente' }).waitFor();
    assert.deepEqual(errors, []);
});

test('complimentary owner can create a family and the admin user table remains usable on mobile', async () => {
    await page.goto(origin + '/admin?tab=users');
    const selfRow = page.locator('tr').filter({ has: page.getByText('browser@example.test', { exact: true }) });
    await selfRow.getByRole('button', { name: 'Offrir le forfait Famille', exact: true }).click();
    await selfRow.getByText('Famille offert', { exact: true }).waitFor();
    await page.goto(origin + '/famille');
    await page.getByRole('button', { name: /^Coffre famille/ }).click();
    await page.locator('form[action$="/famille/creer"] button[type=submit]').click();
    await page.waitForURL('**/famille');
    await page.locator('form[action$="/famille/inviter"]:visible').waitFor();
    assert.equal(await page.locator('form[action$="/famille/inviter"]:visible').count(), 1);
    await page.setViewportSize({ width: 390, height: 844 });
    await page.goto(origin + '/admin?tab=users');
    await selfRow.getByText('Famille offert', { exact: true }).waitFor();
    await page.screenshot({ path: join(env.SOLDIER_E2E_DIRECTORY, 'admin-mobile.png'), fullPage: true });
    await selfRow.locator('summary').filter({ hasText: 'Retirer l’accès offert' }).click();
    await selfRow.locator('[name=confirmation]').check();
    await selfRow.getByRole('button', { name: 'Retirer l’accès offert', exact: true }).click();
    await selfRow.getByRole('button', { name: 'Offrir le forfait Famille', exact: true }).waitFor();
    console.info('Admin browser screenshots:', env.SOLDIER_E2E_DIRECTORY);
    assert.deepEqual(errors, []);
});

const shareForm = () => page.locator('form[action$="/partage"]');
const shareData = () => shareForm().evaluate(form => [...new FormData(form).entries()]);
const shareStatus = () => JSON.parse(execFileSync('php', ['tests/Browser/prepare.php', 'sharing-status'], { cwd: root, env, encoding: 'utf8' }));
async function openSharing() {
    await page.goto(origin + '/partage');
    await page.getByRole('button', { name: 'Partager un coffre', exact: true }).click();
    await page.locator('#coffre_id').selectOption(String(sharing.vault));
}
async function selectOnlyElement(id) {
    for (const input of await shareForm().locator('input[name="element_ids[]"]:enabled').all()) {
        const wanted = await input.getAttribute('value') === String(id);
        if (await input.isChecked() !== wanted) await input.locator('..').click();
    }
}

test('sharing form submits exactly one audience and only elements of the selected vault', async () => {
    sharing = JSON.parse(execFileSync('php', ['tests/Browser/prepare.php', 'sharing'], { cwd: root, env, encoding: 'utf8' }));
    await page.setViewportSize({ width: 1440, height: 1000 });
    await openSharing();
    await page.getByText('Un utilisateur', { exact: true }).click();
    assert.deepEqual((await shareData()).filter(([key]) => key === 'partage_groupe'), [['partage_groupe', '0']]);
    assert.deepEqual((await shareData()).filter(([key]) => key === 'element_ids[]').map(([, value]) => Number(value)), sharing.elements);
    await page.locator('#email').fill('stale-invalid-email');
    await page.getByText('Groupe famille', { exact: true }).click();
    assert.deepEqual((await shareData()).filter(([key]) => key === 'partage_groupe'), [['partage_groupe', '1']]);
    assert.equal((await shareData()).some(([key]) => key === 'email'), false);
    assert.equal(await shareForm().evaluate(form => form.checkValidity()), true);
    await page.getByText('Un utilisateur', { exact: true }).click();
    await page.locator('#coffre_id').selectOption(String(sharing.otherVault));
    assert.deepEqual((await shareData()).filter(([key]) => key === 'element_ids[]'), [['element_ids[]', String(sharing.otherElement)]]);
    await page.locator('#coffre_id').selectOption(String(sharing.vault));
    await selectOnlyElement(sharing.elements[0]);
    assert.deepEqual((await shareData()).filter(([key]) => key === 'element_ids[]'), [['element_ids[]', String(sharing.elements[0])]]);
    const selected = shareForm().locator(`input[name="element_ids[]"][value="${sharing.elements[0]}"]`);
    await selected.focus();
    await page.keyboard.press('Space');
    assert.deepEqual((await shareData()).filter(([key]) => key === 'element_ids[]'), []);
    await page.keyboard.press('Space');
    assert.deepEqual((await shareData()).filter(([key]) => key === 'element_ids[]'), [['element_ids[]', String(sharing.elements[0])]]);
    const selectionStyle = await selected.locator('..').evaluate(label => {
        const style = getComputedStyle(label);
        return { border: style.borderTopColor, outline: style.outlineColor, width: style.outlineWidth };
    });
    assert.equal(selectionStyle.width, '2px', 'Keyboard focus must be visible');
    assert.equal(selectionStyle.border, selectionStyle.outline, 'The selected element must have the accent border');
    assert.deepEqual(errors, []);
});

test('individual sharing preserves a restricted selection after an error and invites only that person', async () => {
    await openSharing();
    await selectOnlyElement(sharing.elements[0]);
    await page.locator('#email').fill('BROWSER@EXAMPLE.TEST');
    await shareForm().getByRole('button', { name: "Envoyer l'invitation" }).click();
    await page.getByText('Vous ne pouvez pas partager avec vous-même.', { exact: true }).waitFor();
    assert.deepEqual((await shareData()).filter(([key]) => key === 'element_ids[]'), [['element_ids[]', String(sharing.elements[0])]]);
    assert.deepEqual((await shareData()).filter(([key]) => key === 'partage_groupe'), [['partage_groupe', '0']]);
    assert.deepEqual(shareStatus(), []);
    await page.locator('#email').fill(sharing.outsider);
    await shareForm().getByRole('button', { name: "Envoyer l'invitation" }).click();
    await page.getByText('Invitations en attente (1)', { exact: false }).waitFor();
    assert.deepEqual(shareStatus(), [{ email_destinataire: sharing.outsider, coffre_id: sharing.vault, element_ids: [sharing.elements[0]], family_group_id: null }]);
    assert.deepEqual(errors, []);
});

test('family sharing on mobile retains an empty selection after validation and invites only members', async () => {
    await page.setViewportSize({ width: 390, height: 844 });
    await openSharing();
    await page.getByText('Groupe famille', { exact: true }).click();
    await selectOnlyElement(-1);
    await shareForm().getByRole('button', { name: "Envoyer l'invitation" }).click();
    await page.locator('[data-share-selection-error]').waitFor();
    assert.deepEqual((await shareData()).filter(([key]) => key === 'partage_groupe'), [['partage_groupe', '1']]);
    assert.deepEqual((await shareData()).filter(([key]) => key === 'element_ids[]'), []);
    assert.equal(shareStatus().length, 1);
    await selectOnlyElement(sharing.elements[1]);
    await page.screenshot({ path: join(env.SOLDIER_E2E_DIRECTORY, 'sharing-family-mobile.png'), fullPage: true });
    await shareForm().getByRole('button', { name: "Envoyer l'invitation" }).click();
    await page.getByText('Invitations en attente (3)', { exact: false }).waitFor();
    const invitations = shareStatus();
    assert.equal(invitations.length, 3);
    assert.deepEqual(invitations.filter(invitation => invitation.family_group_id !== null).map(invitation => invitation.email_destinataire).sort(), [...sharing.members].sort());
    for (const invitation of invitations.filter(invitation => invitation.family_group_id !== null)) {
        assert.equal(invitation.coffre_id, sharing.vault);
        assert.equal(invitation.family_group_id, sharing.group);
        assert.deepEqual(invitation.element_ids, [sharing.elements[1]]);
    }
    assert.deepEqual(errors, []);
});
