// resources/js/stores/internship.js
import { defineStore } from 'pinia';
import { fetchStudentInternships } from '../services/internship';

/**
 * @typedef {Object} InternshipItem
 * @property {number} id
 * @property {string} start_date
 * @property {string} date_to
 * @property {string|null} description
 * @property {boolean} is_draft
 * @property {string|null} submitted_at
 * @property {Object|null} company
 * @property {Object|null} semester
 * @property {{name: string, changed_at: string}|null} status
 * @property {Array} documents
 */

/**
 * @typedef {Object} InternshipState
 * @property {InternshipItem[]} items
 * @property {boolean} isLoading
 * @property {any} error
 * @property {boolean} hasLoadedOnce
 * @property {string|null} lastFetchedAt
 */

/** @returns {InternshipState} */
function state() {
    return {
        items: [],
        isLoading: false,
        error: null,
        hasLoadedOnce: false,
        lastFetchedAt: null,
    };
}

export const useInternshipStore = defineStore('internship', {
    state,

    getters: {
        /** @param {InternshipState} state */
        hasData: (state) => state.items.length > 0,

        /** @param {InternshipState} state */
        isEmpty: (state) =>
            !state.isLoading && state.items.length === 0 && !state.error,

        /** @param {InternshipState} state */
        internshipById: (state) => (id) =>
            state.items.find((item) => String(item.id) === String(id)) || null,
    },

    actions: {
        /**
         * Load internship list from backend.
         *
         * @param {{ force?: boolean }} options
         * @returns {Promise<InternshipItem[]>}
         */
        async loadList({ force = false } = {}) {
            if (this.hasLoadedOnce && !force) {
                return this.items;
            }

            this.isLoading = true;
            this.error = null;

            try {
                const list = await fetchStudentInternships();

                this.items = Array.isArray(list) ? list : [];
                this.hasLoadedOnce = true;
                this.lastFetchedAt = new Date().toISOString();

                return this.items;
            } catch (error) {
                console.error('[useInternshipStore] Failed to load internships:', error);
                this.error = error;
                this.items = [];
                this.hasLoadedOnce = false;
                throw error;
            } finally {
                this.isLoading = false;
            }
        },

        /**
         * Update or insert one internship without refetch.
         * @param {InternshipItem} internship
         */
        upsertInternship(internship) {
            if (!internship || internship.id == null) return;

            const id = internship.id;
            const index = this.items.findIndex(
                (item) => String(item.id) === String(id)
            );

            if (index === -1) {
                this.items.unshift(internship);
            } else {
                this.items.splice(index, 1, internship);
            }

            this.hasLoadedOnce = true;
            this.lastFetchedAt = new Date().toISOString();
        },

        /** @param {InternshipItem[]} list */
        setList(list) {
            this.items = Array.isArray(list) ? list : [];
            this.hasLoadedOnce = true;
            this.lastFetchedAt = new Date().toISOString();
            this.error = null;
        },

        reset() {
            this.items = [];
            this.isLoading = false;
            this.error = null;
            this.hasLoadedOnce = false;
            this.lastFetchedAt = null;
        },
    },
});
