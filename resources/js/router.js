// resources/js/router.js
import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from './stores/auth';

import LandingPage from "./pages/LandingPage.vue";
import RegisterPage from "./pages/register/RegisterPage.vue";
import RegisterConfirmationPage from "./pages/register/RegisterConfirmationPage.vue";
import CompanyActivationPage from "./pages/register/CompanyActivationPage.vue";
import RegisterSentPage from "./pages/register/RegisterConfirmationPage.vue";
import LoginPage from "./pages/login/LoginPage.vue";
import DashboardPage from "./pages/DashboardPage.vue";
import SettingsPage from "./pages/settings/SettingsPage.vue";
import ForgotPasswordPageFirst from "./pages/passwordreset/ForgotPasswordPageFirst.vue";
import ForgotPasswordPageSecond from "./pages/passwordreset/ForgotPasswordPageSecond.vue";
import FirstLoginPage from "./pages/FirstLoginPage.vue";

const publicRouteNames = new Set([
    'LandingPage',
    'LoginPage',
    'RegisterPage',
    'RegisterConfirmationPage',
    'RegisterSentPage',
    'CompanyActivationPage',
    'ForgotPasswordPageFirst',
    'ForgotPasswordPageSecond',
]);

export const router = createRouter({
    history: createWebHistory(),
    routes: [
        // verejný landing
        { path: '/', component: LandingPage, name: 'LandingPage' },

        // auth
        { path: '/login', component: LoginPage, name: 'LoginPage' },

        // forgot password – prvý krok
        {
            path: '/password/forgot',
            component: ForgotPasswordPageFirst,
            name: 'ForgotPasswordPageFirst',
        },

        // reset password – druhý krok (z e-mail linku)
        {
            path: '/password/reset',
            component: ForgotPasswordPageSecond,
            name: 'ForgotPasswordPageSecond',
        },

        // register flow
        { path: '/register', component: RegisterPage, name: 'RegisterPage' },
        {
            path: '/register/sent',
            component: RegisterConfirmationPage,
            name: 'RegisterConfirmationPage',
        },
        {
            path: '/register/sent-preview',
            component: RegisterSentPage,
            name: 'RegisterSentPage',
        },
        {
            path: '/company/activate',
            component: CompanyActivationPage,
            name: 'CompanyActivationPage',
        },

        // povinná zmena hesla po prvom prihlásení
        {
            path: '/first-login',
            component: FirstLoginPage,
            name: 'FirstLoginPage',
        },

        // dashboard – landing po prihlásení
        { path: '/dashboard', component: DashboardPage, name: 'DashboardPage' },

        // settings page
        { path: '/settings', component: SettingsPage, name: 'SettingsPage' },

        // fallback
        { path: '/:pathMatch(.*)*', redirect: '/' },
    ],
});

// GLOBAL GUARDS
router.beforeEach((to, from, next) => {
    const authStore = useAuthStore();
    const isPublic = publicRouteNames.has(to.name);

    // 1) Neprihlásený používateľ sa nedostane na chránené route
    if (!isPublic && !authStore.isAuthenticated) {
        return next({
            name: 'LoginPage',
            query: { redirect: to.fullPath },
        });
    }

    // 2) Prihlásený + password_reset_needed => vždy presmeruj na FirstLoginPage
    if (authStore.isAuthenticated && authStore.passwordResetNeeded) {
        if (to.name !== 'FirstLoginPage') {
            return next({ name: 'FirstLoginPage' });
        }
    }

    // 3) Ak už reset netreba, nedovoľ ísť naspäť na FirstLoginPage
    if (
        to.name === 'FirstLoginPage' &&
        authStore.isAuthenticated &&
        !authStore.passwordResetNeeded
    ) {
        return next({ name: 'DashboardPage' });
    }

    return next();
});
