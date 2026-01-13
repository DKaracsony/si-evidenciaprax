// resources/js/stores/garantStatistics.js
import { defineStore } from 'pinia';
import axios from 'axios';

export const useGarantStatisticsStore = defineStore('garantStatistics', {
    state: () => ({
        stats: {},
        academicYearId: null,
        isLoading: false,
        error: false,
        lastFetchedAt: null,
    }),

    getters: {
        hasData: (s) => Object.keys(s.stats).length > 0,
    },

    actions: {
        async load({ force = false } = {}) {
            const TTL = 5 * 60 * 1000; // 5 minutes

            if (
                !force &&
                this.lastFetchedAt &&
                Date.now() - this.lastFetchedAt < TTL
            ) {
                return;
            }

            this.isLoading = true;
            this.error = false;

            try {
                // 1️⃣ current academic year
                const yearsRes = await axios.get('/api/academic-years');
                const currentYear = yearsRes.data?.data?.[0];
                this.academicYearId = currentYear?.id ?? null;

                // 2️⃣ stats
                const statsRes = await axios.get(
                    '/api/internship/status-counts',
                    {
                        params: {
                            academic_year_id: this.academicYearId,
                        },
                    }
                );

                this.stats = statsRes.data ?? {};
                this.lastFetchedAt = Date.now();
            } catch (e) {
                this.error = true;
                this.stats = {};
            } finally {
                this.isLoading = false;
            }
        },

        reset() {
            this.stats = {};
            this.academicYearId = null;
            this.isLoading = false;
            this.error = false;
            this.lastFetchedAt = null;
        },
    },
});
