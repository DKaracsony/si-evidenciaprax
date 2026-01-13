<template>
    <div class="notification-dropdown">
        <header class="notification-dropdown__header">
            <strong class="notification-dropdown__title">
                Notifikácie
            </strong>

            <span class="notification-dropdown__count">
                neprečítané: {{ unreadCount }}
            </span>

            <button
                v-if="unreadCount"
                type="button"
                class="notification-dropdown__mark-all"
                @click="markAll"
                aria-label="Označiť všetky ako prečítané"
            >
                👁
            </button>
        </header>

        <div class="notification-dropdown__list">
            <div
                v-for="n in items"
                :key="n.id"
                class="notification-item"
                :class="{ 'notification-item--unread': !n.seen_at }"
            >
                <span class="notification-item__icon">
                    {{ iconFor(n.type) }}
                </span>

                <span class="notification-item__text"
                    v-html="n.text">
                </span>

                <button
                    v-if="!n.seen_at"
                    type="button"
                    class="notification-item__mark"
                    @click="markOne(n.id)"
                >
                    👁
                </button>
            </div>

            <div
                v-if="!items.length"
                class="notification-dropdown__empty"
            >
                Žiadne notifikácie
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, onMounted } from 'vue';
import { useNotificationStore } from '@/stores/notifications';

const store = useNotificationStore();

onMounted(() => {
    if (!store.hasLoaded) {
        store.fetchNotifications();
    }
});

const items = computed(() => store.items);
const unreadCount = computed(() => store.unreadCount);

const markOne = (id) => store.markAsSeen(id);
const markAll = () => store.markAllAsSeen();

function iconFor(type) {
    switch (type) {
        case 'status_changed': return '➜';
        case 'information': return 'ℹ';
        case 'completed': return '✔';
        default: return '•';
    }
}
</script>

<style scoped>
.notification-dropdown {
    position: absolute;
    top: calc(100% + 12px);
    right: 0; /* ✅ perfect alignment under bell */

    width: 380px;
    max-height: 420px;

    background: #ffffff;
    border-radius: 16px;
    border: 2px solid #a9c9ff;
    box-shadow: 0 14px 28px rgba(0, 0, 0, 0.25);

    overflow: hidden;
    z-index: 200;
}

.notification-dropdown__header {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 14px 16px;
    border-bottom: 1px solid #e5e7eb;
    color: #0f172a;
}

.notification-dropdown__title {
    font-size: 16px;
    font-weight: 700;
}

.notification-dropdown__count {
    margin-left: auto;
    font-size: 13px;
    color: #64748b;
}

.notification-dropdown__mark-all {
    background: none;
    border: none;
    cursor: pointer;
    color: #2563eb;
}

.notification-dropdown__list {
    max-height: 340px;
    overflow-y: auto;
}

.notification-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 16px;
    border-bottom: 1px solid #f1f5f9;
}

.notification-item--unread {
    background: #f8fafc;
    font-weight: 700;
}

.notification-item__icon {
    min-width: 20px;
    text-align: center;
    color: #2563eb;
}

.notification-item--unread .notification-item__icon {
    color: #dc2626;
}

.notification-item__text {
    color: #1f2937;
    font-size: 14px;
}

.notification-item__mark {
    margin-left: auto;
    background: none;
    border: none;
    cursor: pointer;
    color: #64748b;
}

.notification-dropdown__empty {
    padding: 20px;
    text-align: center;
    color: #64748b;
}
</style>
