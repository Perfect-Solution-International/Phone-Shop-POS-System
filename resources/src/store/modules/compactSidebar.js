export default {
    namespaced: true,
    state: () => ({ compact: false }),
    mutations: { toggle(state) { state.compact = !state.compact; } },
};
