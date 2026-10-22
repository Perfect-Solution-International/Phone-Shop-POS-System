import api from '../../api';

export default function resourceModule(endpoint) {
    return {
        namespaced: true,
        state: () => ({ rows: [], total: 0, loading: false, requestId: 0 }),
        mutations: {
            begin(state) { state.requestId += 1; state.loading = true; },
            loaded(state, data) { state.rows = data.data; state.total = data.meta.total; },
            done(state) { state.loading = false; },
        },
        actions: {
            async fetch({ commit, state }, params) {
                commit('begin');
                const requestId = state.requestId;
                try {
                    const { data } = await api.get(endpoint, { params });
                    if (requestId === state.requestId) { commit('loaded', data); }
                } finally {
                    if (requestId === state.requestId) { commit('done'); }
                }
            },
            async save(context, { id, values }) {
                return id ? api.put(endpoint + '/' + id, values) : api.post(endpoint, values);
            },
            async remove(context, id) { await api.delete(endpoint + '/' + id); },
        },
    };
}
