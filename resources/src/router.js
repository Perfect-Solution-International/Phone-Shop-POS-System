import Vue from "vue";
import VueRouter from "vue-router";
import store from "./store";
import { pageRoutes } from "./api/routes";

Vue.use(VueRouter);

const router = new VueRouter({
    mode: "history",
    base: "/app/",
    routes: [
        {
            path: "/",
            name: "dashboard",
            component: () =>
                import(
                    /* webpackChunkName: "dashboard" */ "./views/app/dashboard/Index.vue"
                ),
            meta: { permission: "hrm.view" },
        },
        {
            path: "/preview",
            name: "preview-dashboard",
            component: () =>
                import(
                    /* webpackChunkName: "dashboard" */ "./views/app/dashboard/Index.vue"
                ),
        },
        ...["employees", "attendance", "leave", "payroll"].map((module) => ({
            path: "/hrm/" + module,
            name: module,
            component: () =>
                import(
                    /* webpackChunkName: "hrm" */ "./views/app/hrm/Index.vue"
                ),
            props: { module },
            meta: {
                permission: module === "payroll" ? "payroll.view" : "hrm.view",
            },
        })),
        {
            path: "/settings",
            name: "settings",
            component: () =>
                import(
                    /* webpackChunkName: "settings" */ "./views/app/settings/Index.vue"
                ),
            meta: { permission: "settings.manage" },
        },
        {
            path: "/messaging",
            name: "messaging",
            component: () =>
                import(
                    /* webpackChunkName: "messaging" */ "./views/app/messaging/Index.vue"
                ),
        },
        {
            path: "/forbidden",
            name: "forbidden",
            component: () => import("./views/Forbidden.vue"),
        },
        { path: "*", component: () => import("./views/NotFound.vue") },
    ],
});

router.beforeEach(async (to, from, next) => {
    if (
        document.body.dataset.loginPreview === "true" &&
        to.name === "preview-dashboard"
    ) {
        return next();
    }
    try {
        await store.dispatch("auth/restore");
        if (!store.state.auth.user) {
            window.location.replace(pageRoutes.login);
            return next(false);
        }
        if (
            to.meta.permission &&
            (!store.state.auth.user.shop_id ||
                !store.getters["auth/can"](to.meta.permission))
        ) {
            return next({ name: "forbidden" });
        }
        next();
    } catch (error) {
        if (!error.response || error.response.status !== 401) {
            window.dispatchEvent(
                new CustomEvent("app-load-error", {
                    detail: "Unable to load your session. Check your connection and reload.",
                })
            );
        }
        next(false);
    }
});

export default router;
