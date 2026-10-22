<template>
    <div>
        <b-navbar toggleable="md" type="dark" variant="dark">
            <b-navbar-brand :to="{ name: 'dashboard' }">{{ applicationName }}</b-navbar-brand>
            <b-navbar-toggle target="navigation" />
            <b-collapse id="navigation" is-nav>
                <b-navbar-nav class="ml-auto">
                    <b-nav-text class="mr-3">{{ user ? user.name : '' }}</b-nav-text>
                    <b-nav-item @click="logout">Sign out</b-nav-item>
                </b-navbar-nav>
            </b-collapse>
        </b-navbar>
        <div class="workspace" :class="{ compact }">
            <aside class="workspace-sidebar" aria-label="Main navigation">
                <b-button block variant="outline-light" size="sm" class="mb-3" @click="$store.commit('compactSidebar/toggle')">
                    {{ compact ? 'Expand menu' : 'Compact menu' }}
                </b-button>
                <nav class="nav flex-column">
                    <router-link v-for="item in navigation" :key="item.name" :to="{ name: item.name }" class="nav-link">
                        {{ item.label }}
                    </router-link>
                </nav>
                <small class="d-block mt-4 text-light">{{ user ? user.shop_name : '' }}</small>
            </aside>
            <main id="main-content" class="workspace-content">
                <b-alert :show="Boolean(loadError)" variant="danger">{{ loadError }} <b-button size="sm" @click="reload">Reload</b-button></b-alert>
                <router-view :key="$route.fullPath" />
            </main>
        </div>
    </div>
</template>

<script>
export default {
    data: () => ({ applicationName: document.title, loadError: '' }),
    computed: {
        user() { return this.$store.state.auth.user; },
        compact() { return this.$store.state.compactSidebar.compact; },
        navigation() {
            return [
                { name: 'dashboard', label: 'Overview', permission: 'hrm.view' },
                { name: 'employees', label: 'Employees', permission: 'hrm.view' },
                { name: 'attendance', label: 'Attendance', permission: 'hrm.view' },
                { name: 'leave', label: 'Leave', permission: 'hrm.view' },
                { name: 'payroll', label: 'Payroll', permission: 'payroll.view' },
                { name: 'settings', label: 'Settings', permission: 'settings.manage' },
                { name: 'messaging', label: 'Messaging' },
            ].filter((item) => !item.permission || this.$store.getters['auth/can'](item.permission));
        },
    },
    mounted() { window.addEventListener('app-load-error', this.onLoadError); },
    beforeDestroy() { window.removeEventListener('app-load-error', this.onLoadError); },
    methods: {
        reload() { window.location.reload(); },
        onLoadError(event) { this.loadError = event.detail; },
        async logout() {
            try { await this.$store.dispatch('auth/logout'); }
            catch (error) { this.$bvToast.toast('Unable to sign out. Please try again.', { title: 'Sign out failed', variant: 'danger', solid: true }); }
        },
    },
};
</script>
