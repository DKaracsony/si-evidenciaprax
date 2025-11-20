// resources/js/services/auth.js
import axios from 'axios';
import { useAuthStore } from '../stores/auth';

// API endpoints based on routes/api.php
const LOGIN_ENDPOINT = '/oauth/token';                   // Passport default
const USER_DETAILS_ENDPOINT = '/api/user';               // AuthController@userDetails
const LOGOUT_ENDPOINT = '/api/logout';                   // AuthController@logout
const CHANGE_PASSWORD_ENDPOINT = '/api/account/password';// PasswordResetController@changePassword

// Support both Vite (VITE_*) and Vue CLI (VUE_APP_*) env vars
const env = (typeof import.meta !== 'undefined' && import.meta.env)
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
 * - sends client_id + client_secret + username/password
 * - stores accessToken/refreshToken in Pinia
 * - fetches user profile and stores it
 *
 * @param {{ email: string, password: string }} payload
 * @returns {Promise<object|null>} user profile after login
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
    // Expected Passport-style response:
    // { token_type, expires_in, access_token, refresh_token, ... }

    const authStore = useAuthStore();
    authStore.setAuth({
        accessToken: data.access_token,
        refreshToken: data.refresh_token ?? null,
        profile: null, // will be filled by fetchAndStoreUser()
    });

    // Immediately fetch user details and store them
    await fetchAndStoreUser();

    return authStore.profile;
}

/**
 * Fetch current user details from backend and store them in Pinia.
 * Uses AuthController@userDetails, which returns: { user: { ... } }
 *
 * @returns {Promise<object|null>} user object or null if not authenticated
 */
export async function fetchAndStoreUser() {
    const authStore = useAuthStore();

    if (!authStore.accessToken) {
        return null;
    }

    const { data } = await axios.get(USER_DETAILS_ENDPOINT);

    // Your AuthController wraps it as { user: $userData }
    const user = data.user ?? data;

    authStore.setProfile(user);

    return user;
}

/**
 * Logout:
 * - Call API to revoke token
 * - Clear auth store regardless of network errors
 *
 * @returns {Promise<void>}
 */
export async function logout() {
    const authStore = useAuthStore();

    try {
        await axios.post(LOGOUT_ENDPOINT);
    } catch (error) {
        // Even if request fails (network, already logged out, etc.) we still clear local state.
        console.warn('Logout request failed (continuing anyway).', error);
    }

    authStore.clearAuth();
}

/**
 * Change password for a logged-in user.
 * Endpoint: PATCH /api/account/password
 * (PasswordResetController@changePassword)
 *
 * @param {{ currentPassword: string, newPassword: string, newPasswordConfirmation: string }} payload
 * @returns {Promise<any>} backend response data (for toasts, messages, etc.)
 */
export async function changePassword({
                                         currentPassword,
                                         newPassword,
                                         newPasswordConfirmation,
                                     }) {
    const requestBody = {
        current_password: currentPassword,
        new_password: newPassword,
        new_password_confirmation: newPasswordConfirmation,
    };

    const { data } = await axios.patch(CHANGE_PASSWORD_ENDPOINT, requestBody);
    return data;
}

