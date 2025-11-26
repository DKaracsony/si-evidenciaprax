// resources/js/stores/internship.js
import { defineStore } from 'pinia';
import { fetchStudentInternships } from '../services/internship';

export const useInternshipStore = defineStore('internship', {
    state: () => ({
        items: [],            // zoznam praxí
        isLoading: false,     // globálny loading flag
        error: null,          // posledná chyba (ak nejaká bola)
        hasLoadedOnce: false, // či už sme raz úspešne nahrali zoznam
        lastFetchedAt: null,  // ISO timestamp posledného refreshu
    }),

    getters: {
        hasData: (state) => state.items.length > 0,

        isEmpty: (state) =>
            !state.isLoading && state.items.length === 0 && !state.error,

        internshipById: (state) => (id) =>
            state.items.find((item) => String(item.id) === String(id)) || null,
    },

    actions: {
        /**
         * Načíta zoznam praxí z backendu a uloží ho do store.
         * - ak už bol zoznam raz načítaný, nevolá API (pokiaľ force !== true)
         *
         * @param {{ force?: boolean }} options
         * @returns {Promise<Array>}
         */
        async loadList({ force = false } = {}) {
            if (this.hasLoadedOnce && !force) {
                return this.items;
            }

            this.isLoading = true;
            this.error = null;

            try {
                const list = await fetchStudentInternships();

                if (!Array.isArray(list)) {
                    console.warn(
                        '[useInternshipStore] Expected internships array, got:',
                        list
                    );
                    this.items = [];
                } else {
                    this.items = list;
                }

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
         * Napr. po vytvorení / update praxe, aby si nemusel robiť hneď refetch.
         */
        upsertInternship(internship) {
            if (!internship || typeof internship.id === 'undefined') {
                return;
            }

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
