import assert from 'node:assert/strict';
import { after, afterEach, before, beforeEach, test } from 'node:test';
import { createServer } from 'vite';

// Use a locally installed Playwright, or an explicit existing runtime module.
const { chromium } = await import(process.env.SOLDIER_PLAYWRIGHT_MODULE ?? 'playwright');
let server;
let browser;
let page;
let origin;

before(async () => {
    server = await createServer({ configFile: false, server: { host: '127.0.0.1', port: 0 }, logLevel: 'error' });
    await server.listen();
    origin = `http://127.0.0.1:${server.httpServer.address().port}`;
    browser = await chromium.launch({
        headless: true,
        ...(process.env.SOLDIER_BROWSER_EXECUTABLE ? { executablePath: process.env.SOLDIER_BROWSER_EXECUTABLE } : {}),
    });
});

beforeEach(async () => {
    page = await browser.newPage();
    await page.route('**/*', route => route.request().url().startsWith(origin + '/') ? route.continue() : route.abort());
    await page.goto(`${origin}/tests/Browser/fixture.html`);
});
afterEach(async () => { await page?.close(); });
after(async () => { await browser?.close(); await server?.close(); });

async function component(module, name, args = []) {
    await page.evaluate(async ({ module, name, args }) => {
        window.audit = (await import(`/resources/js/pages/${module}.ts`))[name](...args);
    }, { module, name, args });
}

test('generator: password respects length and digits-only options', async () => {
    await component('generateur', 'generateur');
    const password = await page.evaluate(() => {
        audit.longueur = 32;
        audit.options = { majuscules: false, minuscules: false, chiffres: true, symboles: false, similaires: false };
        audit.generer();
        return audit.motDePasse;
    });
    assert.match(password, /^[0-9]{32}$/);
});

test('generator: ambiguous characters are excluded', async () => {
    await component('generateur', 'generateur');
    const password = await page.evaluate(() => { audit.longueur = 256; audit.generer(); return audit.motDePasse; });
    assert.doesNotMatch(password, /[0O1lI]/);
});

test('generator: disabling all character sets produces an empty result', async () => {
    await component('generateur', 'generateur');
    const state = await page.evaluate(() => {
        audit.options = { majuscules: false, minuscules: false, chiffres: false, symboles: false, similaires: false };
        audit.generer();
        return [audit.motDePasse, audit.entropie];
    });
    assert.deepEqual(state, ['', 0]);
});

test('generator: passphrase respects word count and separator', async () => {
    await component('generateur', 'generateur');
    const phrase = await page.evaluate(() => {
        audit.type = 'passphrase'; audit.nbMots = 7; audit.separateur = '_'; audit.generer(); return audit.motDePasse;
    });
    assert.equal(phrase.split('_').length, 7);
    assert.ok(phrase.split('_').every(Boolean));
});

test('generator: displayed passphrase entropy matches the real dictionary', async () => {
    await component('generateur', 'generateur');
    const result = await page.evaluate(() => {
        const original = crypto.getRandomValues.bind(crypto);
        // Enumerate dictionary indices deterministically, without statistical sampling.
        crypto.getRandomValues = array => { for (let i = 0; i < array.length; i++) array[i] = i; return array; };
        audit.nbMots = 10000; audit.genererPassphrase();
        const dictionarySize = new Set(audit.motDePasse.split('-')).size;
        crypto.getRandomValues = original;
        audit.nbMots = 5; audit.type = 'passphrase'; audit.generer();
        return { dictionarySize, displayed: audit.entropie, expected: Math.round(5 * Math.log2(dictionarySize)) };
    });
    assert.equal(result.displayed, result.expected, JSON.stringify(result));
});

test('generator: digit entropy accounts for excluded ambiguous digits', async () => {
    await component('generateur', 'generateur');
    const entropy = await page.evaluate(() => {
        audit.options = { majuscules: false, minuscules: false, chiffres: true, symboles: false, similaires: true };
        audit.longueur = 20; audit.generer(); return audit.entropie;
    });
    assert.equal(entropy, 60); // 20 independent draws from digits 2..9 = 20 * log2(8).
});

test('create form: favicon stays on the local proxy and encodes domain', async () => {
    await component('creer', 'creerService');
    assert.equal(await page.evaluate(() => {
        audit.url = 'https://example.com/account'; audit.mettreAJourFavicon(); return audit.faviconUrl;
    }), '/favicon?domain=example.com');
});

test('create form: empty label and URL reset favicon', async () => {
    await component('creer', 'creerService');
    assert.equal(await page.evaluate(() => { audit.mettreAJourFavicon(); return audit.faviconUrl; }), '');
});

test('create form: generated password is placed into the visible state', async () => {
    await component('creer', 'creerService');
    const result = await page.evaluate(() => {
        document.body.insertAdjacentHTML('beforeend', '<input id="mdp_visuel" type="password">');
        audit.genererMdp(); return [audit.motDePasse.length, audit.showMdp, document.querySelector('#mdp_visuel').type];
    });
    assert.deepEqual(result, [20, true, 'text']);
});

test('edit form: password generation updates the submitted input', async () => {
    await component('modifier', 'modifierService', ['GitHub', 'https://github.com', '']);
    assert.equal(await page.evaluate(() => {
        document.body.insertAdjacentHTML('beforeend', '<input id="mot_de_passe" type="password">');
        audit.genererMdp(); return document.querySelector('#mot_de_passe').value === audit.motDePasse;
    }), true);
});

test('dashboard: favorites filter excludes non-favorites', async () => {
    await component('dashboard', 'dashboard');
    const result = await page.evaluate(() => {
        audit.filtreActif = 'Favoris'; return [audit.filtrerElement('github', 'github.com', false), audit.filtrerElement('github', 'github.com', true)];
    });
    assert.deepEqual(result, [false, true]);
});

test('dashboard: search finds a matching normalized name or URL', async () => {
    await component('dashboard', 'dashboard');
    assert.equal(await page.evaluate(() => { audit.recherche = 'GITHUB'; return audit.filtrerElement('github', 'https://github.com', false); }), true);
});

test('dashboard: failed favorite request does not report success', async () => {
    await component('dashboard', 'dashboard');
    await page.evaluate(async () => {
        window.fetch = async () => new Response(JSON.stringify({ message: 'Forbidden' }), { status: 403 });
        const button = document.createElement('button');
        button.innerHTML = '<svg></svg>'; document.body.appendChild(button);
        await audit.toggleFavori(1, button);
    });
    assert.equal(await page.locator('.toast-title').last().textContent(), 'Erreur');
});

test('toast: user HTML is rendered as text and cannot execute', async () => {
    await page.evaluate(async () => {
        const { showToast } = await import('/resources/js/toast.ts');
        showToast('info', '<img src=x onerror="window.auditXss=true">', '<script>window.auditXss=true</script>');
    });
    assert.equal(await page.locator('#toast-container img').count(), 0);
    assert.equal(await page.evaluate(() => window.auditXss), undefined);
    assert.match(await page.locator('.toast-title').textContent(), /<img/);
});

test('sharing dialog: recipient name cannot inject HTML', async () => {
    await component('partage', 'partage', [false, 'lecture']);
    await page.evaluate(() => {
        audit.confirmerRevocation({ target: document.createElement('form') }, '<img src=x onerror="window.auditXss=true">');
    });
    assert.equal(await page.locator('#modal-revocation img').count(), 0);
    assert.match(await page.locator('.revocation-name').textContent(), /<img/);
});

test('dashboard deletion dialog: cancel leaves the form unsubmitted', async () => {
    await component('dashboard', 'dashboard');
    await page.evaluate(() => {
        const form = document.createElement('form');
        form.submit = () => { window.auditSubmitted = true; };
        audit.confirmerSuppression({ target: form }, 'Audit');
    });
    await page.locator('#modal-annuler').click();
    assert.equal(await page.locator('#modal-suppression').count(), 0);
    assert.equal(await page.evaluate(() => window.auditSubmitted), undefined);
});

test('clipboard: delayed clearing preserves newer user content', async () => {
    await page.clock.install();
    await component('afficher', 'afficherService');
    await page.evaluate(async () => {
        window.auditClipboard = '';
        Object.defineProperty(navigator, 'clipboard', { configurable: true, value: {
            writeText: async text => { window.auditClipboard = text; },
            readText: async () => window.auditClipboard,
        } });
        await audit.copier('old-secret', 'Secret');
        window.auditClipboard = 'new-user-content';
    });
    await page.clock.fastForward(30001);
    assert.equal(await page.evaluate(() => window.auditClipboard), 'new-user-content');
});

test('settings: TOTP QR is drawn locally without contacting an external service', async () => {
    await component('settings', 'settings', ['securite', false]);
    assert.equal(await page.evaluate(async () => {
        const canvas = document.createElement('canvas'); document.body.appendChild(canvas);
        audit.$refs = { totpQrCanvas: canvas }; audit.$nextTick = async () => {};
        window.fetch = async () => new Response(JSON.stringify({ secret: 'TESTSECRET', otpauth_url: 'otpauth://totp/Audit?secret=JBSWY3DPEHPK3PXP' }));
        await audit.configurerTotp('/settings/totp/configurer');
        return audit.totpSecret === 'TESTSECRET' && canvas.width === 150;
    }), true);
});

test('uniform random indices reject the biased tail instead of reducing it modulo the alphabet', async () => {
    const result = await page.evaluate(async () => {
        const { randomIndices } = await import('/resources/js/security/random.ts');
        let calls = 0;
        crypto.getRandomValues = array => { array.fill(calls++ === 0 ? 4294967295 : 9); return array; };
        return { values: randomIndices(10, 2), get calls() { return calls; } };
    });
    assert.deepEqual(result.values, [9, 9]);
    assert.equal(result.calls, 2);
});

test('clipboard: unchanged copied secret is cleared when the browser permits reading', async () => {
    await page.clock.install();
    await page.evaluate(async () => {
        const { copySecret } = await import('/resources/js/security/clipboard.ts');
        window.auditClipboard = '';
        document.hasFocus = () => true;
        Object.defineProperty(navigator, 'clipboard', { configurable: true, value: {
            writeText: async text => { window.auditClipboard = text; }, readText: async () => window.auditClipboard,
        } });
        await copySecret('temporary-test-secret');
    });
    await page.clock.fastForward(30001);
    assert.equal(await page.evaluate(() => window.auditClipboard), '');
});

test('clipboard: denied read permission never destroys unknown clipboard content', async () => {
    await page.clock.install();
    await page.evaluate(async () => {
        const { copySecret } = await import('/resources/js/security/clipboard.ts');
        document.hasFocus = () => true;
        Object.defineProperty(navigator, 'clipboard', { configurable: true, value: {
            writeText: async text => { window.auditClipboard = text; },
            readText: async () => { throw new DOMException('Denied', 'NotAllowedError'); },
        } });
        await copySecret('temporary-test-secret');
        window.auditClipboard = 'new-user-content';
    });
    await page.clock.fastForward(30001);
    assert.equal(await page.evaluate(() => window.auditClipboard), 'new-user-content');
});

test('passkey frontend: assertion binary buffers are serialized to base64url', async () => {
    await page.evaluate(async () => {
        const { connecterAvecPasskey } = await import('/resources/js/pages/passkey.ts');
        Object.defineProperty(navigator, 'credentials', { configurable: true, value: {
            get: async () => ({ id: 'AQID', rawId: new Uint8Array([1, 2, 3]).buffer, type: 'public-key', response: {
                clientDataJSON: new Uint8Array([4, 5]).buffer, authenticatorData: new Uint8Array([6, 7]).buffer,
                signature: new Uint8Array([8, 9]).buffer, userHandle: null,
            } }),
        } });
        window.fetch = async (url, options) => {
            if (url.includes('options-connexion')) return new Response(JSON.stringify({ challenge: 'AQID', rpId: 'localhost' }));
            window.auditPayload = JSON.parse(options.body);
            return new Response(JSON.stringify({ success: false, error: 'Synthetic stop after serialization' }), { status: 422 });
        };
        await connecterAvecPasskey();
    });
    const payload = await page.evaluate(() => window.auditPayload);
    assert.equal(payload.credential.rawId, 'AQID');
    assert.equal(payload.credential.response.signature, 'CAk');
    assert.equal(payload.credential.response.userHandle, null);
});
