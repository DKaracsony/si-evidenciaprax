<!-- resources/js/components/internships/StudentPraxList.vue -->
<template>
    <section class="student-prax-list">
        <header
            class="student-prax-list__header"
            :class="{
        'student-prax-list__header--with-button': internshipStore.hasData
    }"
        >
            <h2 class="student-prax-list__title">
                Zoznam odborných praxí
            </h2>

            <button
                v-if="internshipStore.hasData"
                type="button"
                class="student-prax-list__primary-button
           student-prax-list__primary-button--inline
           student-prax-list__header-button"
                @click="$emit('new-internship')"
            >
                Pridať prax
            </button>


            <div class="student-prax-list__divider"></div>
        </header>


        <!-- LOADING -->
        <div
            v-if="internshipStore.isLoading && !internshipStore.hasData"
            class="student-prax-list__state"
        >
            Načítavam tvoje praxe...
        </div>

        <!-- ERROR -->
        <div
            v-else-if="internshipStore.error"
            class="student-prax-list__state student-prax-list__state--error"
        >
            <p class="student-prax-list__state-text">
                Nepodarilo sa načítať tvoje praxe.
            </p>
            <button
                type="button"
                class="student-prax-list__primary-button student-prax-list__primary-button--outline"
                @click="reload"
            >
                Skúsiť znova
            </button>
        </div>

        <!-- EMPTY STATE (design z obrázka) -->
        <template v-else-if="internshipStore.isEmpty">
            <div class="student-prax-list__empty">
                <p class="student-prax-list__empty-text">
                    Aktuálne nemáte žiadny zaznamenaný prax, vyplňte formulár a pridajte
                    svoj odborný prax, aby bolo viditeľné školou a vami.
                </p>

                <button
                    type="button"
                    class="student-prax-list__primary-button"
                    @click="$emit('new-internship')"
                >
                    Pridať prax
                </button>
            </div>

            <div class="student-prax-list__divider student-prax-list__divider--bottom"></div>
        </template>

        <!-- LIST (keď budú dáta v DB) -->
        <template v-else>
            <ul class="student-prax-list__items">
                <li
                    v-for="internship in internshipStore.items"
                    :key="internship.id"
                    class="student-prax-item"
                >
                    <div class="student-prax-item__main">
                        <h3 class="student-prax-item__company">
                            {{ internship.company?.name ?? 'Neznáma firma' }}
                            <span
                                v-if="internship.company?.address?.city"
                                class="student-prax-item__company-city"
                            >
                                ({{ internship.company.address.city }})
                            </span>
                        </h3>

                        <p class="student-prax-item__dates">
                            {{ formatDate(internship.start_date) }}
                            &nbsp;–&nbsp;
                            {{ formatDate(internship.date_to) }}
                        </p>

                        <p
                            v-if="internship.semester"
                            class="student-prax-item__semester"
                        >
                            {{ formatSemester(internship.semester) }}
                        </p>

                        <div
                            v-if="internship.description"
                            class="student-prax-item__description"
                        >
                            <span class="student-prax-item__description-label">
                                Popis:
                            </span>
                            <p class="student-prax-item__description-text">
                                {{ internship.description }}
                            </p>
                        </div>
                    </div>

                    <div class="student-prax-item__side">
                        <InternshipStatusBadge
                            :is-draft="!!internship.is_draft"
                            :status-name="internship.status?.name ?? null"
                        />

                        <!-- Draft: dátum vytvorenia návrhu -->
                        <p
                            v-if="internship.is_draft && getDraftCreatedAt(internship)"
                            class="student-prax-item__status-meta"
                        >
                            Návrh vytvorený:
                            {{ formatDate(getDraftCreatedAt(internship)) }}
                        </p>

                        <!-- Normálna prax: stav aktualizovaný -->
                        <p
                            v-else-if="!internship.is_draft && internship.status?.changed_at"
                            class="student-prax-item__status-meta"
                        >
                            Stav aktualizovaný:
                            {{ formatDate(internship.status.changed_at) }}
                        </p>

                        <!-- AKCIE PODĽA STAVU -->
                        <!-- Návrh -->
                        <button
                            v-if="internship.is_draft"
                            type="button"
                            class="student-prax-item__cta-button"
                            @click="$emit('edit-draft', internship.id)"
                        >
                            Upraviť návrh
                        </button>

                        <!-- Nie draft – stavovo špecifické akcie -->
                        <template v-else>
                            <!-- Vytvorená / Potvrdená → Vygenerovať dohodu + Zobraziť detail vedľa seba -->
                            <div
                                v-if="
                                    hasStatus(internship, ['CREATED', 'VYTVORENA']) ||
                                    hasStatus(internship, ['CONFIRMED', 'POTVRDENA'])
                                "
                                class="student-prax-item__actions-row"
                            >
                                <button
                                    type="button"
                                    class="student-prax-item__cta-button"
                                    :disabled="generatingId === internship.id"
                                    @click="handleGenerateAgreement(internship.id)"
                                >
                                    <span v-if="generatingId === internship.id">Generujem dohodu...</span>
                                    <span v-else>Vygenerovať dohodu</span>
                                </button>

                                <button
                                    type="button"
                                    class="student-prax-item__cta-button"
                                    @click="$emit('open-detail', internship.id)"
                                >
                                    Zobraziť detail
                                </button>
                            </div>

                            <!-- Zamietnutá / Neobhájená → Odstrániť prax + Zobraziť detail vedľa seba -->
                            <div
                                v-else-if="
                                    hasStatus(internship, ['REJECTED', 'DENIED', 'ZAMIETNUTA']) ||
                                    hasStatus(internship, ['NOT_DEFENDED', 'NEOBHAJENA'])
                                "
                                class="student-prax-item__actions-row"
                            >
                                <button
                                    type="button"
                                    class="student-prax-item__cta-button"
                                    @click="handleDeleteNotImplemented(internship)"
                                >
                                    Odstrániť prax
                                </button>

                                <button
                                    type="button"
                                    class="student-prax-item__cta-button"
                                    @click="$emit('open-detail', internship.id)"
                                >
                                    Zobraziť detail
                                </button>
                            </div>

                            <!-- Schválená → 4 tlačidlá vedľa seba -->
                            <div
                                v-else-if="hasStatus(internship, ['APPROVED', 'SCHVALENA'])"
                                class="student-prax-item__actions-row"
                            >
                                <!-- 1. Vygenerovať dohodu -->
                                <button
                                    type="button"
                                    class="student-prax-item__cta-button"
                                    :disabled="generatingId === internship.id"
                                    @click="handleGenerateAgreement(internship.id)"
                                >
                                    <span v-if="generatingId === internship.id">Generujem dohodu...</span>
                                    <span v-else>Vygenerovať dohodu</span>
                                </button>

                                <!-- 2. Nahrať výkaz -->
                                <button
                                    type="button"
                                    class="student-prax-item__cta-button"
                                    @click="handleUploadNotImplemented(internship, 'vykaz')"
                                >
                                    Nahrať výkaz
                                </button>

                                <!-- 3. Nahrať dohodu -->
                                <button
                                    type="button"
                                    class="student-prax-item__cta-button"
                                    @click="handleUploadNotImplemented(internship, 'dohoda')"
                                >
                                    Nahrať dohodu
                                </button>

                                <!-- 4. Zobraziť detail -->
                                <button
                                    type="button"
                                    class="student-prax-item__cta-button"
                                    @click="$emit('open-detail', internship.id)"
                                >
                                    Zobraziť detail
                                </button>
                            </div>


                            <!-- fallback: len Zobraziť detail -->
                            <button
                                v-else
                                type="button"
                                class="student-prax-item__cta-button"
                                @click="$emit('open-detail', internship.id)"
                            >
                                Zobraziť detail
                            </button>
                        </template>

                        <!-- Chyba generovania dohody (ak nastane) -->
                        <p
                            v-if="pdfErrorId === internship.id && pdfErrorMessage"
                            class="student-prax-item__status-meta"
                        >
                            {{ pdfErrorMessage }}
                        </p>
                    </div>
                </li>
            </ul>

            <div class="student-prax-list__divider student-prax-list__divider--bottom"></div>
        </template>
    </section>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import { useInternshipStore } from '@/stores/internship.js';
import InternshipStatusBadge from '../General/InternshipStatusBadge.vue';
import { downloadStudentInternshipAgreementPdf } from '@/services/internship.js';

const internshipStore = useInternshipStore();

const generatingId = ref(null);
const pdfErrorId = ref(null);
const pdfErrorMessage = ref('');

const reload = () => {
    internshipStore.loadList({ force: true }).catch(() => {
        // error riešime cez internshipStore.error
    });
};

onMounted(() => {
    if (!internshipStore.hasLoadedOnce) {
        reload();
    }
});

function formatDate(value) {
    if (!value) return '';

    const datePart = String(value).split('T')[0];
    const [year, month, day] = datePart.split('-').map(Number);

    if (!year || !month || !day) return value;

    return `${day}.${month}.${year}`;
}

/**
 * Mapuje raw hodnotu z DB (winter/summer/zimny/letny) na text pre UI.
 */
function mapSeasonLabel(seasonRaw) {
    if (!seasonRaw) return '';

    const s = String(seasonRaw).toLowerCase();

    if (['winter', 'zimny', 'zimný', 'zima'].includes(s)) {
        return 'Zimný semester';
    }

    if (['summer', 'letny', 'letný', 'leto'].includes(s)) {
        return 'Letný semester';
    }

    // fallback – keby náhodou prišlo niečo iné
    return seasonRaw;
}

function formatSemester(semester) {
    if (!semester) return '';

    const startYear = new Date(semester.start_date).getFullYear();
    const endYear = new Date(semester.end_date).getFullYear();
    const range =
        startYear && endYear && startYear !== endYear
            ? `${startYear}/${String(endYear).slice(-2)}`
            : startYear || '';

    const seasonLabel = mapSeasonLabel(semester.season);

    // Výsledok: "2024/25 – Zimný semester" alebo len "Zimný semester", ak by náhodou rok chýbal
    return [range, seasonLabel].filter(Boolean).join(' – ');
}

/**
 * Nájde dátum, ktorý použijeme ako "Návrh vytvorený".
 * Ideálne backend pošle created_at, inak fallback.
 */
function getDraftCreatedAt(internship) {
    return (
        internship['created_at'] ??
        internship.submitted_at ??
        internship.status?.changed_at ??
        null
    );
}

function normalizeStatusKey(raw) {
    return (raw || '')
        .toString()
        .trim()
        .toUpperCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '');
}

function getStatusKey(internship) {
    const raw = internship.status?.name || '';
    return normalizeStatusKey(raw);
}

function hasStatus(internship, keys) {
    const key = getStatusKey(internship);
    return keys.includes(key);
}

async function handleGenerateAgreement(id) {
    generatingId.value = id;
    pdfErrorId.value = null;
    pdfErrorMessage.value = '';

    try {
        const response = await downloadStudentInternshipAgreementPdf(id);
        const blob = new Blob([response.data], { type: 'application/pdf' });
        const url = URL.createObjectURL(blob);

        const a = document.createElement('a');
        a.href = url;
        a.download = `dohoda-praxe-${id}.pdf`;
        a.click();

        URL.revokeObjectURL(url);
    } catch (e) {
        console.error('[StudentPraxList] Failed to generate/download agreement PDF', e);
        pdfErrorId.value = id;
        pdfErrorMessage.value =
            'Pri generovaní dohody došlo k chybe. Skúste to znova.';
    } finally {
        generatingId.value = null;
    }
}

// placeholdery – BE ešte nemáme
function handleDeleteNotImplemented(internship) {
    console.log(
        '[StudentPraxList] Delete action not implemented yet for internship',
        internship.id
    );
}

function handleUploadNotImplemented(internship, type) {
    console.log(
        `[StudentPraxList] Upload ${type} not implemented yet for internship`,
        internship.id
    );
}
</script>
