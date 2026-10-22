const axios = require('axios');

function createApiClient({ baseURL, getToken, onUnauthorized }) {
    const client = axios.create({ baseURL, headers: { Accept: 'application/json' } });
    client.interceptors.request.use((config) => {
        const token = getToken();
        if (token) {
            config.headers.Authorization = 'Bearer ' + token;
        }
        return config;
    });
    client.interceptors.response.use(
        (response) => response,
        (error) => {
            if (error.response && error.response.status === 401) {
                onUnauthorized();
            }
            return Promise.reject(error);
        }
    );
    return client;
}

module.exports = { createApiClient };
