<template>
    <div v-if="ready">
        <b-navbar toggleable="md" type="dark" variant="dark">
            <b-navbar-brand
                :to="{ name: preview ? 'preview-dashboard' : 'dashboard' }"
                >{{ applicationName }}</b-navbar-brand
            >
            <b-navbar-toggle target="navigation" />
            <b-collapse id="navigation" is-nav>
                <b-navbar-nav class="ml-auto">
                    <b-nav-item href="/shop" class="mr-2 font-weight-bold text-info">
                        ⚡ Shop POS & Counter
                    </b-nav-item>
                    <b-nav-text class="mr-3">{{
                        user ? user.name : ""
                    }}</b-nav-text>
                    <b-nav-item @click="logout">Sign out</b-nav-item>
                </b-navbar-nav>
            </b-collapse>
        </b-navbar>
        <div class="workspace" :class="{ compact }">
            <aside class="workspace-sidebar" aria-label="Main navigation">
                <b-button
                    block
                    variant="outline-light"
                    size="sm"
                    class="mb-3"
                    @click="$store.commit('compactSidebar/toggle')"
                >
                    {{ compact ? "Expand menu" : "Compact menu" }}
                </b-button>
                <nav class="nav flex-column">
                    <router-link
                        v-for="item in navigation"
                        :key="item.name"
                        :to="{ name: item.name }"
                        class="nav-link"
                    >
                        {{ item.label }}
                    </router-link>
                    <a href="/shop" class="nav-link text-info font-weight-bold mt-2" style="border: 1px dashed rgba(56,189,248,0.4); border-radius: 6px;">
                        🛒 Shop POS & Inventory
                    </a>
                </nav>
                <small class="d-block mt-4 text-light">{{
                    user ? user.shop_name : ""
                }}</small>
            </aside>
            <main id="main-content" class="workspace-content">
                <b-alert v-if="preview" show variant="info"
                    >Temporary dashboard preview. No account is signed
                    in.</b-alert
                >
                <b-alert :show="Boolean(loadError)" variant="danger"
                    >{{ loadError }}
                    <b-button size="sm" @click="reload"
                        >Reload</b-button
                    ></b-alert
                >
                <router-view :key="$route.fullPath" />
            </main>
        </div>
    </div>
    <main v-else class="login-shell">
        <b-alert v-if="loadError" show variant="danger"
            >{{ loadError }}
            <b-button size="sm" @click="reload">Reload</b-button></b-alert
        >
        <p v-else role="status">
            <b-spinner small class="mr-2" />Checking your session...
        </p>
    </main>
</template>

<script>
import { pageRoutes } from "./api/routes";

export default {
    data: () => ({
        applicationName: document.title,
        loadError: "",
        preview: document.body.dataset.loginPreview === "true",
    }),
    computed: {
        user() {
            return this.$store.state.auth.user;
        },
        ready() {
            return (
                this.preview ||
                (this.$store.state.auth.restored && Boolean(this.user))
            );
        },
        compact() {
            return this.$store.state.compactSidebar.compact;
        },
        navigation() {
            if (this.preview) {
                return [{ name: "preview-dashboard", label: "Overview" }];
            }
            return [
                {
                    name: "dashboard",
                    label: "Overview",
                    permission: "hrm.view",
                },
                {
                    name: "employees",
                    label: "Employees",
                    permission: "hrm.view",
                },
                {
                    name: "attendance",
                    label: "Attendance",
                    permission: "hrm.view",
                },
                { name: "leave", label: "Leave", permission: "hrm.view" },
                {
                    name: "payroll",
                    label: "Payroll",
                    permission: "payroll.view",
                },
                {
                    name: "settings",
                    label: "Settings",
                    permission: "settings.manage",
                },
                { name: "messaging", label: "Messaging" },
            ].filter(
                (item) =>
                    !item.permission ||
                    this.$store.getters["auth/can"](item.permission)
            );
        },
    },
    mounted() {
        window.addEventListener("app-load-error", this.onLoadError);
    },
    beforeDestroy() {
        window.removeEventListener("app-load-error", this.onLoadError);
    },
    methods: {
        reload() {
            window.location.reload();
        },
        onLoadError(event) {
            this.loadError = event.detail;
        },
        async logout() {
            if (this.preview) {
                window.location.replace(pageRoutes.login);
                return;
            }
            try {
                await this.$store.dispatch("auth/logout");
            } catch (error) {
                this.$bvToast.toast("Unable to sign out. Please try again.", {
                    title: "Sign out failed",
                    variant: "danger",
                    solid: true,
                });
            }
        },
    },
};
</script>
