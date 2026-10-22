import { createApiClient } from './client';
import { apiBase } from './routes';

export default createApiClient({
    baseURL: apiBase,
    getToken: () => localStorage.getItem('enterprise.token'),
    onUnauthorized: () => {
        localStorage.removeItem('enterprise.token');
        if (window.location.pathname !== '/login') {
            window.location.replace('/login');
        }
    },
});
