import { defineStore } from 'pinia';
import axios from 'axios';

export const useNotificationStore = defineStore('notification', {
    state: () => ({
        items: [],
        isLoading: false,
        hasLoaded: false,
    }),

    getters: {
        unreadCount: (state) =>
            state.items.filter(n => !n.seen_at).length,

        hasUnread: (state) =>
            state.items.some(n => !n.seen_at),
    },

    actions: {
        async fetchNotifications() {
            if (this.isLoading) return;

            this.isLoading = true;
            try {
                const { data } = await axios.get('/api/notifications');
                this.items = data;
                this.hasLoaded = true;
            } finally {
                this.isLoading = false;
            }
        },

        async markAsSeen(notificationId) {
            const n = this.items.find(i => i.id === notificationId);
            if (!n || n.seen_at) return;

            await axios.patch(`/api/notifications/${notificationId}/seen`);
            n.seen_at = new Date().toISOString();
        },

        async markAllAsSeen() {
            await axios.patch('/api/notifications/seen-all');
            const now = new Date().toISOString();
            this.items.forEach(n => {
                if (!n.seen_at) n.seen_at = now;
            });
        },

        reset() {
            this.items = [];
            this.isLoading = false;
            this.hasLoaded = false;
        },
    },
});
