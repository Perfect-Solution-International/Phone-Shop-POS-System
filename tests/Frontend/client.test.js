const { test } = require('node:test');
const assert = require('node:assert/strict');
const { createApiClient } = require('../../resources/src/api/client');

test('uses session cookies and CSRF headers without exposing a bearer token', async () => {
    const client = createApiClient({ baseURL: '/api/v1', onUnauthorized: () => {} });
    client.defaults.adapter = async (config) => {
        assert.equal(config.withCredentials, true);
        assert.equal(config.withXSRFToken, true);
        assert.equal(config.headers.Authorization, undefined);
        assert.equal(config.timeout, 15000);
        return { status: 200, data: {}, headers: {}, config };
    };

    await client.get('/employees');
});

test('does not attach authorization when signed out', async () => {
    const client = createApiClient({ baseURL: '/api/v1', onUnauthorized: () => {} });
    client.defaults.adapter = async (config) => {
        assert.equal(config.headers.Authorization, undefined);
        return { status: 200, data: {}, headers: {}, config };
    };

    await client.post('/auth/login', { email: 'user@example.test' });
});

test('invalidates the session on 401 and preserves the rejected response', async () => {
    let invalidated = false;
    const client = createApiClient({ baseURL: '/api/v1', onUnauthorized: () => { invalidated = true; } });
    client.defaults.adapter = async () => { throw { response: { status: 401 } }; };

    await assert.rejects(client.get('/auth/me'), (error) => error.response.status === 401);
    assert.equal(invalidated, true);
});

test('preserves validation errors without invalidating the session', async () => {
    let invalidated = false;
    const client = createApiClient({ baseURL: '/api/v1', onUnauthorized: () => { invalidated = true; } });
    client.defaults.adapter = async () => { throw { response: { status: 422, data: { errors: { name: ['The name field is required.'] } } } }; };

    await assert.rejects(client.post('/employees', {}), (error) => error.response.data.errors.name[0] === 'The name field is required.');
    assert.equal(invalidated, false);
});

test('expires the browser session on a CSRF session timeout', async () => {
    let invalidated = false;
    const client = createApiClient({ baseURL: '/api/v1', onUnauthorized: () => { invalidated = true; } });
    client.defaults.adapter = async () => { throw { response: { status: 419 } }; };

    await assert.rejects(client.post('/auth/logout'), (error) => error.response.status === 419);
    assert.equal(invalidated, true);
});
