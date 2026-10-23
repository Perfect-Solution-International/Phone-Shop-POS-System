import { createApiClient } from './client';
import { apiBase, pageRoutes } from './routes';

try { localStorage.removeItem('enterprise.token'); } catch (error) { /* Old browser tokens are no longer used. */ }

let redirecting = false;

export default createApiClient({
    baseURL: apiBase,
    onUnauthorized: () => {
        if (!redirecting && window.location.pathname !== pageRoutes.login) {
            redirecting = true;
            window.location.replace(pageRoutes.login);
        }
    },
});
