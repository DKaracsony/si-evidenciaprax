
<template>
    <span class="internship-status-badge" :class="`internship-status-badge--${config.variant}`">
        {{ config.label }}
    </span>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    statusName: {
        type: String,
        default: null,
    },
    isDraft: {
        type: Boolean,
        default: false,
    },
});

// helper: uppercase + remove diacritics (á -> A, etc.)
function normalizeStatus(raw) {
    return (raw || '')
        .toString()
        .trim()
        .toUpperCase()
        .normalize('NFD')                 // split letter + accent
        .replace(/[\u0300-\u036f]/g, ''); // drop accents
}

const normalizedStatusName = computed(() => normalizeStatus(props.statusName));

// mapovanie backend -> label + farba
const STATUS_CONFIG = {
    // Návrh
    DRAFT:      { label: 'Návrh',      variant: 'danger' },
    NAVRH:      { label: 'Návrh',      variant: 'danger' },

    // Vytvorená
    CREATED:    { label: 'Vytvorená',  variant: 'success' },
    VYTVORENA:  { label: 'Vytvorená',  variant: 'success' },

    // Potvrdená
    CONFIRMED:  { label: 'Potvrdená',  variant: 'success' },
    POTVRDENA:  { label: 'Potvrdená',  variant: 'success' },

    // Schválená
    APPROVED:   { label: 'Schválená',  variant: 'success' },
    SCHVALENA:  { label: 'Schválená',  variant: 'success' },

    // Obhájená
    DEFENDED:   { label: 'Obhájená',   variant: 'success' },
    OBHAJENA:   { label: 'Obhájená',   variant: 'success' },

    // Neobhájená
    NOT_DEFENDED: { label: 'Neobhájená', variant: 'danger' },
    NEOBHAJENA:   { label: 'Neobhájená', variant: 'danger' },

    // Zamietnutá
    REJECTED:   { label: 'Zamietnutá', variant: 'danger' },
    DENIED:     { label: 'Zamietnutá', variant: 'danger' },
    ZAMIETNUTA: { label: 'Zamietnutá', variant: 'danger' },
};

const config = computed(() => {
    // 1) Draft má prioritu
    if (props.isDraft) {
        return { label: 'Návrh', variant: 'danger' };
    }

    // 2) Podľa status.name
    const fromMap = STATUS_CONFIG[normalizedStatusName.value];
    if (fromMap) return fromMap;

    // 3) Fallback – neznámy stav
    const raw = props.statusName || '';
    if (!raw) {
        return { label: 'Neznámy stav', variant: 'neutral' };
    }

    const label = raw.charAt(0).toUpperCase() + raw.slice(1);
    return { label, variant: 'neutral' };
});
</script>
