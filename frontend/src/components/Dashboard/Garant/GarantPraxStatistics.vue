<template>
    <section class="garant-prax-statistics">
        <header class="garant-prax-statistics__header">
            <h2 class="garant-prax-statistics__title">
                Štatistiky praxí – aktuálny semester
            </h2>

            <div class="garant-prax-statistics__divider"></div>
        </header>

        <!-- LOADING -->
        <p v-if="loading" class="garant-prax-statistics__state">
            Načítavam štatistiky…
        </p>

        <!-- ERROR -->
        <p v-else-if="error" class="garant-prax-statistics__state error">
            Nepodarilo sa načítať štatistiky.
        </p>

        <!-- STATS -->
        <div v-else class="garant-prax-statistics__grid">
            <div
                v-for="tile in tiles"
                :key="tile.key"
                class="garant-prax-statistics__tile"
            >
                <p class="garant-prax-statistics__count">
                    {{ stats[tile.key] ?? 0 }}
                </p>
                <p class="garant-prax-statistics__label">
                    {{ tile.label }}
                </p>
            </div>
        </div>

        <!-- BACK BUTTON -->
        <div class="garant-prax-statistics__actions">
            <button
                type="button"
                class="student-prax-detail__button student-prax-detail__button--ghost"
                @click="$emit('back')"
            >
                Späť na zoznam praxí
            </button>
        </div>
    </section>
</template>

<script setup>
import { onMounted } from 'vue';
import { storeToRefs } from 'pinia';
import { useGarantStatisticsStore } from '@/stores/garantStatistics';

defineEmits(['back']);

const store = useGarantStatisticsStore();
const { stats, isLoading: loading, error } = storeToRefs(store);

const tiles = [
    { key: 'Vytvorená', label: 'Vytvorené' },
    { key: 'Potvrdená', label: 'Potvrdené' },
    { key: 'Zamietnutá', label: 'Zamietnuté' },
    { key: 'Schválená', label: 'Schválené' },
    { key: 'Obhájená', label: 'Obhájené' },
    { key: 'Neobhájená', label: 'Neobhájené' },
];

onMounted(() => {
    store.load(); // cached, no refetch unless needed
});
</script>
