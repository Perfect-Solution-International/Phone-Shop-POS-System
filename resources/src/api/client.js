const axios = require('axios');

function createApiClient({ baseURL, onUnauthorized }) {
    const client = axios.create({
        baseURL,
        headers: { Accept: 'application/json' },
        withCredentials: true,
        withXSRFToken: true,
        timeout: 15000,
    });
    client.interceptors.response.use(
        (response) => response,
        (error) => {
            if (error.response && [401, 419].includes(error.response.status)) {
                onUnauthorized();
            }
            return Promise.reject(error);
        }
    );
    return client;
}

module.exports = { createApiClient };
