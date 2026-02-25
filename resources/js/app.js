import './common/plugins';
import '../less/custom.less';
import 'ant-design-vue/dist/reset.css';

import { createApp } from 'vue';
import Antd from 'ant-design-vue';
import { PerfectScrollbarPlugin } from 'vue3-perfect-scrollbar';
import { VueQueryPlugin } from '@tanstack/vue-query';
import App from './main/views/App.vue';
import routes from './main/router'
import store from './main/store';
import { setupI18n } from './common/i18n';

import 'vue3-perfect-scrollbar/style.css';
import print from 'vue3-print-nb';

import AdminPageFilter from "./common/layouts/AdminPageFilters.vue";
import AdminPageTableContent from "./common/layouts/AdminPageTableContent.vue";

async function bootstrap() {
    // Check if we're on a landing page - if so, don't initialize the main app
    if (window.location.pathname.startsWith('/landing/')) {
        return;
    }
    
    // Check if the main app element exists - if not, don't initialize
    if (!document.getElementById('app')) {
        return;
    }
    
    if (store.getters["auth/isLoggedIn"]) {
        store.dispatch("auth/updateUser");
    }

    store.dispatch("auth/updateGlobalSetting");
    await store.dispatch("auth/updateApp");
    store.dispatch("auth/updateAllLangs");
    store.commit("auth/updateActiveModules", window.config.modules);
    store.dispatch("auth/updateVisibleSubscriptionModules");

    // Determine language: user preference > company language > stored > default
    let currentLang = store.state.auth.lang;
    if (store.state.auth.user && store.state.auth.user.language) {
        currentLang = store.state.auth.user.language;
        store.commit("auth/updateLang", currentLang);
    } else if (store.state.auth.appSetting && store.state.auth.appSetting.lang) {
        currentLang = store.state.auth.appSetting.lang.key;
        store.commit("auth/updateLang", currentLang);
    }

    const app = createApp(App);

    const i18n = setupI18n({ legacy: false, globalInjection: true, locale: currentLang, warnHtmlMessage: false });
    // Translations are now loaded directly in setupI18n - no async loading needed

    app.config.devtools = true;
    app.config.debug = true;

    app.use(i18n);
    app.use(Antd);
    app.use(PerfectScrollbarPlugin)
    app.use(store);
    app.use(print);
    app.use(routes);
    app.use(VueQueryPlugin, {
        queryClientConfig: {
            defaultOptions: {
                queries: {
                    refetchOnWindowFocus: false,
                    retry: 1,
                    staleTime: 5 * 60 * 1000, // 5 minutes
                },
            },
        },
    });

    // Global Components
    app.component('admin-page-filters', AdminPageFilter);
    app.component('admin-page-table-content', AdminPageTableContent);

    window.i18n = i18n;

    routes.isReady().then(() => {
        app.mount("#app");
    })
}

bootstrap();