// resources/js/stores/auth.js
import { defineStore } from 'pinia';

const STORAGE_KEY = 'auth';

/**
 * UserProfile type definition (helps Volar/WebStorm understand backend fields)
 *
 * @typedef {Object} UserProfile
 * @property {string} first_name
 * @property {string} last_name
 * @property {string} email
 * @property {string|null} role
 * @property {boolean} password_reset_needed
 */

/**
 * @typedef {Object} AuthState
 * @property {string|null} accessToken
 * @property {string|null} refreshToken
 * @property {UserProfile|null} profile
 */

/** @returns {AuthState} */
function state() {
    return {
        accessToken: null,     // string | null
        refreshToken: null,    // string | null
        profile: null,         // UserProfile | null
    };
}

export const useAuthStore = defineStore('auth', {
    state,

    getters: {
        /** @param {AuthState} state */
        isAuthenticated: (state) => !!state.accessToken,

        /** @param {AuthState} state */
        fullName: (state) =>
            state.profile
                ? `${state.profile.first_name ?? ''} ${state.profile.last_name ?? ''}`.trim()
                : '',

        /** @param {AuthState} state */
        role: (state) => state.profile?.role ?? null,

        /** @param {AuthState} state */
        passwordResetNeeded: (state) =>
            Boolean(state.profile?.password_reset_needed),
    },

    actions: {
        /**
         * Initialize store from localStorage (call once on app startup).
         */
        hydrateFromStorage() {
            try {
                const raw = window.localStorage.getItem(STORAGE_KEY);
                if (!raw) return;

                const parsed = JSON.parse(raw);

                this.accessToken = parsed.accessToken ?? null;
                this.refreshToken = parsed.refreshToken ?? null;
                this.profile = parsed.profile ?? null;
            } catch (error) {
                console.warn('Failed to hydrate auth store.', error);
                window.localStorage.removeItem(STORAGE_KEY);
            }
        },

        /**
         * Save current auth state to localStorage.
         * (No password is ever stored here.)
         */
        persist() {
            const data = {
                accessToken: this.accessToken,
                refreshToken: this.refreshToken,
                profile: this.profile,
            };
            window.localStorage.setItem(STORAGE_KEY, JSON.stringify(data));
        },

        /**
         * Set tokens + profile after successful login or token refresh.
         * Example payload: { accessToken, refreshToken, profile }
         */
        setAuth({ accessToken, refreshToken = null, profile }) {
            this.accessToken = accessToken;
            this.refreshToken = refreshToken;
            this.profile = profile;
            this.persist();
        },

        /**
         * Update only profile (e.g., after calling /user-details).
         */
        setProfile(profile) {
            this.profile = profile;
            this.persist();
        },

        /**
         * Clear all auth data (logout, token expired, etc.)
         */
        clearAuth() {
            this.accessToken = null;
            this.refreshToken = null;
            this.profile = null;
            window.localStorage.removeItem(STORAGE_KEY);
        },
    },
});
