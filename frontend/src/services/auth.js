// resources/js/services/auth.js
import axios from 'axios';
import { useAuthStore } from '../stores/auth';
import { useInternshipStore } from '@/stores/internship';

// API endpoints based on routes/api.php
const LOGIN_ENDPOINT = '/oauth/token';                   // Passport default
const USER_DETAILS_ENDPOINT = '/api/user';               // AuthController@userDetails
const LOGOUT_ENDPOINT = '/api/logout';                   // AuthController@logout
const CHANGE_PASSWORD_ENDPOINT = '/api/account/password';// PasswordResetController@changePassword

// Support both Vite (VITE_*) and Vue CLI (VUE_APP_*) env vars
const env =
    (typeof import.meta !== 'undefined' && import.meta.env)
        ? import.meta.env
        : (typeof process !== 'undefined' ? process.env : {});

const clientId =
    env.VITE_OAUTH_CLIENT_ID ||
    env.VUE_APP_OAUTH_CLIENT_ID ||
    null;

const clientSecret =
    env.VITE_OAUTH_CLIENT_SECRET ||
    env.VUE_APP_OAUTH_CLIENT_SECRET ||
    null;

/**
 * Login with email + password via Laravel Passport (/oauth/token).
 */
export async function login({ email, password }) {
    if (!clientId || !clientSecret) {
        console.warn(
            'OAuth client env vars not set. ' +
            'Check your .env (VITE_OAUTH_CLIENT_ID / VITE_OAUTH_CLIENT_SECRET or VUE_APP_*).'
        );
    }

    const requestBody = {
        grant_type: 'password',
        client_id: clientId,
        client_secret: clientSecret,
        username: email,
        password,
        scope: '*',
    };

    const { data } = await axios.post(LOGIN_ENDPOINT, requestBody);

    const accessToken = data['access_token'];
    const refreshToken = data['refresh_token'] ?? null;

    const authStore = useAuthStore();
    authStore.setAuth({
        accessToken,
        refreshToken,
        profile: null,
    });

    await fetchAndStoreUser();

    return authStore.profile;
}

/**
 * Fetch current user details and store them in Pinia.
 */
export async function fetchAndStoreUser() {
    const authStore = useAuthStore();

    if (!authStore.accessToken) {
        return null;
    }

    const { data } = await axios.get(USER_DETAILS_ENDPOINT);
    const user = data.user ?? data;

    authStore.setProfile(user);

    return user;
}

/**
 * Logout + FULL APP STATE CLEANUP
 */
export async function logout() {
    const authStore = useAuthStore();
    const internshipStore = useInternshipStore();

    try {
        await axios.post(LOGOUT_ENDPOINT);
    } catch (error) {
        console.warn('Logout request failed (continuing anyway).', error);
    }

    authStore.clearAuth();
    internshipStore.reset(); // CRITICAL FIX
}

/**
 * Change password for a logged-in user.
 */
export async function changePassword({
                                         currentPassword,
                                         newPassword,
                                         newPasswordConfirmation,
                                     }) {
    const requestBody = {
        new_password: newPassword,
        new_password_confirmation: newPasswordConfirmation,
    };

    if (currentPassword) {
        requestBody.current_password = currentPassword;
    }

    const { data } = await axios.patch(CHANGE_PASSWORD_ENDPOINT, requestBody);
    return data;
}
