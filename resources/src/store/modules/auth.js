import api from '../../api';
import { endpoints, pageRoutes } from '../../api/routes';

let restoreRequest = null;

export default {
    namespaced: true,
    state: () => ({ user: null, restored: false }),
    getters: {
        can: (state) => (permission) => Boolean(state.user && state.user.permissions.includes(permission)),
    },
    mutations: {
        setUser(state, user) { state.user = user; },
        restored(state) { state.restored = true; },
    },
    actions: {
        async login({ commit }, credentials) {
            await api.get(pageRoutes.csrfCookie, { baseURL: '/' });
            const { data } = await api.post(pageRoutes.loginStore, credentials, { baseURL: '/' });
            commit('setUser', data.user);
            commit('restored');
            return data.redirect;
        },
        async restore({ commit, state }) {
            if (state.restored) { return; }
            if (!restoreRequest) {
                restoreRequest = (async () => {
                    try {
                        const { data } = await api.get(endpoints.me);
                        commit('setUser', data.user);
                        commit('restored');
                    } catch (error) {
                        if (!error.response || ![401, 419].includes(error.response.status)) { throw error; }
                        commit('setUser', null);
                        commit('restored');
                    } finally { restoreRequest = null; }
                })();
            }
            return restoreRequest;
        },
        async logout({ commit }) {
            await api.post(endpoints.logout);
            commit('setUser', null);
            window.location.replace(pageRoutes.login);
        },
    },
};
