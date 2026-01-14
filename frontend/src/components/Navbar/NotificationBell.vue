<template>
    <button
        type="button"
        class="notification-bell"
        aria-label="Notifikácie"
        @click="$emit('toggle')"
    >
        <img :src="iconPath('bell.svg')" alt="" />

        <span
            v-if="hasUnread"
            class="notification-bell__dot"
            aria-hidden="true"
        />
    </button>
</template>

<script setup>
import { computed } from 'vue';
import { useNotificationStore } from '@/stores/notifications';

const store = useNotificationStore();
const hasUnread = computed(() => store.hasUnread);

function iconPath(file) {
    return `/storage/icons/${file}`;
}
</script>

<style scoped>
.notification-bell {
    position: relative;
    background: none;
    border: none;
    padding: 4px;
    cursor: pointer;
}

.notification-bell img {
    width: 26px;
    height: 26px;
}

.notification-bell__dot {
    position: absolute;
    top: 0;
    right: 0;
    width: 10px;
    height: 10px;
    background: #ff0000;
    border-radius: 50%;
}
</style>
