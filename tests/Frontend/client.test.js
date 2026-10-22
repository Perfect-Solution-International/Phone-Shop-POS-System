const { test } = require('node:test');
const assert = require('node:assert/strict');
const { createApiClient } = require('../../resources/src/api/client');

test('attaches the current bearer token to each request', async () => {
    let token = 'first-token';
    const client = createApiClient({ baseURL: '/api/v1', getToken: () => token, onUnauthorized: () => {} });
    const headers = [];
    client.defaults.adapter = async (config) => {
        headers.push(config.headers.Authorization);
        return { status: 200, data: {}, headers: {}, config };
    };

    await client.get('/employees');
    token = 'replacement-token';
    await client.get('/employees');

    assert.deepEqual(headers, ['Bearer first-token', 'Bearer replacement-token']);
});

test('does not attach authorization when signed out', async () => {
    const client = createApiClient({ baseURL: '/api/v1', getToken: () => null, onUnauthorized: () => {} });
    client.defaults.adapter = async (config) => {
        assert.equal(config.headers.Authorization, undefined);
        return { status: 200, data: {}, headers: {}, config };
    };

    await client.post('/auth/login', { email: 'user@example.test' });
});

test('invalidates the session on 401 and preserves the rejected response', async () => {
    let invalidated = false;
    const client = createApiClient({ baseURL: '/api/v1', getToken: () => 'expired', onUnauthorized: () => { invalidated = true; } });
    client.defaults.adapter = async () => { throw { response: { status: 401 } }; };

    await assert.rejects(client.get('/auth/me'), (error) => error.response.status === 401);
    assert.equal(invalidated, true);
});

test('preserves validation errors without invalidating the session', async () => {
    let invalidated = false;
    const client = createApiClient({ baseURL: '/api/v1', getToken: () => 'valid', onUnauthorized: () => { invalidated = true; } });
    client.defaults.adapter = async () => { throw { response: { status: 422, data: { errors: { name: ['The name field is required.'] } } } }; };

    await assert.rejects(client.post('/employees', {}), (error) => error.response.data.errors.name[0] === 'The name field is required.');
    assert.equal(invalidated, false);
});
