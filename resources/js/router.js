// resources/js/router.js
import { createRouter, createWebHistory } from 'vue-router'

import LandingPage from "./pages/LandingPage.vue";
import RegisterPage from "./pages/register/RegisterPage.vue";
import RegisterConfirmationPage from "./pages/register/RegisterConfirmationPage.vue";
import CompanyActivationPage from "./pages/register/CompanyActivationPage.vue";
import RegisterSentPage from "./pages/register/RegisterConfirmationPage.vue";

export const router = createRouter({
    history: createWebHistory(),
    routes: [
        { path: '/', component: LandingPage, name: 'LandingPage' },
        { path: '/register', component: RegisterPage, name: 'RegisterPage' },
        { path: '/register/sent', component: RegisterConfirmationPage, name: 'RegisterConfirmationPage' },
        { path: '/activation/company/:token', component: CompanyActivationPage, name: 'CompanyActivationPage' },
        //testing, delete later!
        { path: '/register/sent-preview', component: RegisterSentPage, name: 'RegisterSentPage' },
        { path: '/:pathMatch(.*)*', redirect: '/' },


    ],
})
