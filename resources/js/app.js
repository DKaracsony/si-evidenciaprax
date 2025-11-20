import './bootstrap';
import { createApp } from 'vue';
import { createPinia } from 'pinia';
import '../scss/app.scss';
import { router } from './router';

import App from './App.vue';
import { useAuthStore } from './stores/auth';

// Simple helper for auth-related error messages
// Later you can replace alert/console with a real toast library.
function showAuthError(message) {
    console.error(message);
    alert(message);
}

function setupAxiosInterceptors(authStore) {
    if (!window.axios) {
        console.warn('Axios not found on window. Did you import it in bootstrap.js?');
        return;
    }

    // REQUEST INTERCEPTOR – attach Bearer token if available
    window.axios.interceptors.request.use(
        (config) => {
            if (authStore.accessToken) {
                config.headers = config.headers || {};
                config.headers.Authorization = `Bearer ${authStore.accessToken}`;
            }
            return config;
        },
        (error) => Promise.reject(error)
    );

    // RESPONSE INTERCEPTOR – handle 401 / 403 globally
    window.axios.interceptors.response.use(
        (response) => response,
        (error) => {
            const { response } = error;

            if (!response) {
                // Network error, timeout, etc. – let caller handle it.
                return Promise.reject(error);
            }

            const status = response.status;
            const url = response.config?.url || '';

            // Ignore auth logic on the login endpoint itself
            const isLoginEndpoint = url.includes('/oauth/token');

            if (status === 401 && !isLoginEndpoint) {
                if (authStore.isAuthenticated) {
                    // token probably expired or got invalidated
                    authStore.clearAuth();
                    showAuthError('Vaše prihlásenie vypršalo. Prosím, prihláste sa znova.');
                } else {
                    showAuthError('Na vykonanie tejto akcie sa musíte prihlásiť.');
                }

                // Teraz už máme /login route => presmeruj na LoginPage
                void router.push({ name: 'LoginPage' });
            } else if (status === 403) {
                showAuthError('Nemáte oprávnenie vykonať túto akciu.');
            }

            return Promise.reject(error);
        }
    );
}

const app = createApp(App);

// Pinia
const pinia = createPinia();
app.use(pinia);

// Router
app.use(router);

// Auth store: restore from localStorage
const authStore = useAuthStore();
authStore.hydrateFromStorage();

// Setup Axios interceptors AFTER auth store exists
setupAxiosInterceptors(authStore);

app.mount('#app');
