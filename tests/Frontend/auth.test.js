const { test } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const { transformSync } = require('@babel/core');
const compiler = require('vue-template-compiler');

function loadModule(file, dependencies = {}, globals = {}) {
    let source = fs.readFileSync(path.resolve(__dirname, '../../resources/src', file), 'utf8');
    if (file.endsWith('.vue')) { source = compiler.parseComponent(source).script.content; }
    const { code } = transformSync(source, { babelrc: false, configFile: false, plugins: ['@babel/plugin-transform-modules-commonjs'] });
    const context = { exports: {}, require: (name) => dependencies[name], ...globals };
    vm.runInNewContext(code, context);
    return context.exports.default;
}

function authHarness(api) {
    const redirects = [];
    const module = loadModule('store/modules/auth.js', {
        '../../api': api,
        '../../api/routes': { endpoints: { me: '/auth/me', logout: '/auth/logout' }, pageRoutes: { csrfCookie: '/sanctum/csrf-cookie', loginStore: '/login', login: '/login' } },
    }, { window: { location: { replace: (url) => redirects.push(url) } } });
    const state = module.state();
    const context = { state, commit: (name, value) => module.mutations[name](state, value) };
    return { module, state, context, redirects };
}

test('session restoration waits for one shared request and reuses the verified user', async () => {
    let finish;
    let requests = 0;
    const user = { id: 1, permissions: ['hrm.view'] };
    const harness = authHarness({ get: () => { requests++; return new Promise((resolve) => { finish = resolve; }); } });

    const first = harness.module.actions.restore(harness.context);
    const second = harness.module.actions.restore(harness.context);
    assert.equal(harness.state.restored, false);
    assert.equal(harness.state.user, null);
    assert.equal(requests, 1);
    finish({ data: { user } });
    await Promise.all([first, second]);
    await harness.module.actions.restore(harness.context);

    assert.equal(requests, 1);
    assert.equal(harness.state.user, user);
    assert.equal(harness.state.restored, true);
});

test('an expired session restores as signed out', async () => {
    const harness = authHarness({ get: async () => { throw { response: { status: 401 } }; } });

    await harness.module.actions.restore(harness.context);

    assert.equal(harness.state.user, null);
    assert.equal(harness.state.restored, true);
});

test('session verification failures remain retryable instead of treating users as signed out', async () => {
    let requests = 0;
    const harness = authHarness({ get: async () => { requests++; throw new Error('Network Error'); } });

    await assert.rejects(harness.module.actions.restore(harness.context));
    await assert.rejects(harness.module.actions.restore(harness.context));

    assert.equal(harness.state.restored, false);
    assert.equal(requests, 2);
});

test('login refreshes CSRF before submitting and returns the server authorized destination', async () => {
    const calls = [];
    const user = { id: 1, permissions: ['payroll.view'] };
    const credentials = { email: 'person@example.test', password: 'test-password', remember: true };
    const harness = authHarness({
        get: async (url) => { calls.push(url); },
        post: async (url, payload) => { calls.push(url); assert.equal(payload, credentials); return { data: { user, redirect: '/app/hrm/payroll' } }; },
    });

    const destination = await harness.module.actions.login(harness.context, credentials);

    assert.deepEqual(calls, ['/sanctum/csrf-cookie', '/login']);
    assert.equal(destination, '/app/hrm/payroll');
    assert.equal(harness.state.user, user);
    assert.equal(harness.state.restored, true);
});

test('logout clears user state only after the server invalidates the session', async () => {
    let finish;
    const harness = authHarness({ post: () => new Promise((resolve) => { finish = resolve; }) });
    harness.state.user = { id: 1 };

    const request = harness.module.actions.logout(harness.context);
    assert.equal(harness.state.user.id, 1);
    assert.deepEqual(harness.redirects, []);
    finish();
    await request;

    assert.equal(harness.state.user, null);
    assert.deepEqual(harness.redirects, ['/login']);
});

function loginHarness(dispatch, dataset = {}) {
    const redirects = [];
    const component = loadModule('views/Login.vue', {}, {
        document: { body: { dataset: { applicationName: 'Phone Shop POS SaaS', ...dataset } } },
        window: { location: { replace: (url) => redirects.push(url) } },
    });
    const context = { ...component.data(), $store: { dispatch }, $refs: { form: { setErrors: () => {} } }, $nextTick: (callback) => callback() };
    return { component, context, redirects };
}

test('temporary preview sign in opens the dashboard without checking or submitting credentials', async () => {
    const harness = loginHarness(() => { throw new Error('Preview must not authenticate'); }, { loginPreviewUrl: '/app/preview' });

    await harness.component.methods.submit.call(harness.context);
    await harness.component.methods.submit.call(harness.context);

    assert.deepEqual(harness.redirects, ['/app/preview']);
    assert.equal(harness.context.busy, true);
});

test('preview dashboard skips private API requests', async () => {
    const component = loadModule('views/app/dashboard/Index.vue', {
        'vue-echarts': {},
        '../../../api': { get: () => { throw new Error('Preview must not request private data'); } },
        '../../../api/routes': { endpoints: { dashboard: '/dashboard' } },
    }, { document: { body: { dataset: { loginPreview: 'true' } } } });
    const context = component.data();

    await component.methods.load.call(context);

    assert.equal(context.loading, false);
    assert.equal(context.error, '');
    assert.deepEqual(Object.keys(context.summary), []);
});

test('the login form remains busy while submitting and ignores duplicate submissions', async () => {
    let finish;
    let requests = 0;
    const harness = loginHarness(() => { requests++; return new Promise((resolve) => { finish = resolve; }); });
    harness.context.credentials.password = 'test-password';

    const request = harness.component.methods.submit.call(harness.context);
    assert.equal(harness.context.busy, true);
    await harness.component.methods.submit.call(harness.context);
    assert.equal(requests, 1);
    finish('/app/');
    await request;

    assert.equal(harness.context.busy, false);
    assert.equal(harness.context.credentials.password, '');
    assert.deepEqual(harness.redirects, ['/app/']);
});

for (const [name, error, expected] of [
    ['invalid credentials', { response: { status: 422, data: { errors: { email: ['The provided credentials are incorrect.'] } } } }, /Check your email/],
    ['network failure', new Error('Network Error'), /Check your connection/],
    ['server failure', { response: { status: 500 } }, /Unable to sign in right now/],
    ['rate limit', { response: { status: 429 } }, /Too many sign-in attempts/],
    ['CSRF timeout', { response: { status: 419 } }, /session expired/],
]) {
    test('login displays a helpful error and allows retry after ' + name, async () => {
        const harness = loginHarness(async () => { throw error; });
        let fieldErrors;
        harness.context.$refs.form.setErrors = (errors) => { fieldErrors = errors; };

        await harness.component.methods.submit.call(harness.context);

        assert.match(harness.context.error, expected);
        assert.equal(harness.context.busy, false);
        assert.deepEqual(harness.redirects, []);
        if (error.response && error.response.status === 422) { assert.equal(fieldErrors.email[0], 'The provided credentials are incorrect.'); }
    });
}
