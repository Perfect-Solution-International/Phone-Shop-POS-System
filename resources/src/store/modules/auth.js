import api from '../../api';
import { endpoints } from '../../api/routes';

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
            const { data } = await api.post(endpoints.login, credentials);
            try {
                localStorage.setItem('enterprise.token', data.token);
            } catch (error) {
                await api.post(endpoints.logout, {}, { headers: { Authorization: 'Bearer ' + data.token } });
                throw new Error('Browser storage is unavailable. Allow storage to sign in.');
            }
            commit('setUser', data.user);
        },
        async restore({ commit, state }) {
            if (state.restored) { return; }
            if (localStorage.getItem('enterprise.token')) {
                const { data } = await api.get(endpoints.me);
                commit('setUser', data.user);
            }
            commit('restored');
        },
        async logout({ commit }) {
            await api.post(endpoints.logout);
            localStorage.removeItem('enterprise.token');
            commit('setUser', null);
            window.location.assign('/login');
        },
    },
};
