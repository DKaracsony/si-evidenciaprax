<template>
    <section class="student-prax-detail">
        <header class="student-prax-detail__header">
            <h2 class="student-prax-detail__title">
                Detail odbornej praxe
            </h2>

            <div class="student-prax-list__divider student-prax-detail__divider"></div>
        </header>

        <!-- LOADING -->
        <div v-if="isLoading" class="student-prax-detail__state">
            Načítavam detail praxe...
        </div>

        <!-- ERROR -->
        <div
            v-else-if="error"
            class="student-prax-detail__state student-prax-detail__state--error"
        >
            <p>{{ error }}</p>
            <button
                type="button"
                class="student-prax-detail__button student-prax-detail__button--outline"
                @click="reload"
            >
                Skúsiť znova
            </button>
        </div>

        <!-- CONTENT -->
        <div v-else-if="internship" class="student-prax-detail__content">
            <!-- Firma + kontakt -->
            <section class="student-prax-detail__section">
                <h3 class="student-prax-detail__section-title">
                    Firma
                </h3>

                <p class="student-prax-detail__company-name">
                    {{ internship.company?.name ?? 'Neznáma firma' }}
                </p>

                <p
                    v-if="internship.company?.description"
                    class="student-prax-detail__text-muted"
                >
                    {{ internship.company.description }}
                </p>

                <p
                    v-if="internship.company?.website"
                    class="student-prax-detail__row"
                >
                    <span class="student-prax-detail__row-label">Web:</span>
                    <a
                        :href="internship.company.website"
                        target="_blank"
                        rel="noopener noreferrer"
                    >
                        {{ internship.company.website }}
                    </a>
                </p>

                <p
                    v-if="internship.company?.address"
                    class="student-prax-detail__row"
                >
                    <span class="student-prax-detail__row-label">Adresa:</span>
                    <span>
                        {{ internship.company.address.street }}
                        {{ internship.company.address.house_number }},
                        {{ internship.company.address.postal_code }}
                        {{ internship.company.address.city }}
                        <span v-if="internship.company.address.country">
                            , {{ internship.company.address.country.name }}
                        </span>
                    </span>
                </p>

                <p
                    v-if="contactPerson"
                    class="student-prax-detail__row"
                >
                    <span class="student-prax-detail__row-label">Kontakt:</span>
                    <span>
                        {{ contactPerson.title_before }}
                        {{ contactPerson.first_name }}
                        {{ contactPerson.last_name }}
                        {{ contactPerson.title_after }}
                        <span v-if="contactPerson.email">
                            &nbsp;– {{ contactPerson.email }}
                        </span>
                        <span v-if="contactPerson.role_at_company">
                            &nbsp;({{ contactPerson.role_at_company }})
                        </span>
                        <span v-if="contactPerson.phone_number">
                            &nbsp;· Tel: {{ contactPerson.phone_number }}
                        </span>
                    </span>
                </p>

                <!-- Non-blocking warning if detail fetch failed but we still show store fallback -->
                <p
                    v-if="nonBlockingError"
                    class="student-prax-detail__text-muted"
                >
                    {{ nonBlockingError }}
                </p>
            </section>

            <!-- Termíny + semester -->
            <section class="student-prax-detail__section">
                <h3 class="student-prax-detail__section-title">
                    Termín a semester
                </h3>

                <p class="student-prax-detail__row">
                    <span class="student-prax-detail__row-label">Začiatok:</span>
                    <span>{{ formatDate(internship.start_date) }}</span>
                </p>

                <p class="student-prax-detail__row">
                    <span class="student-prax-detail__row-label">Koniec:</span>
                    <span>{{ formatDate(internship.date_to) }}</span>
                </p>

                <p
                    v-if="internship.semester"
                    class="student-prax-detail__row"
                >
                    <span class="student-prax-detail__row-label">
                        Akademický rok / semester:
                    </span>
                    <span>{{ formatSemester(internship.semester) }}</span>
                </p>
            </section>

            <!-- Popis -->
            <section class="student-prax-detail__section">
                <h3 class="student-prax-detail__section-title">
                    Popis praxe
                </h3>

                <p class="student-prax-detail__description">
                    {{ internship.description || 'Bez popisu.' }}
                </p>
            </section>

            <!-- Stav -->
            <section class="student-prax-detail__section student-prax-detail__section--status">
                <div class="student-prax-detail__status-main">
                    <InternshipStatusBadge
                        :is-draft="!!internship.is_draft"
                        :status-name="internship.status?.name ?? null"
                    />

                    <p
                        v-if="internship.is_draft && internship['created_at']"
                        class="student-prax-detail__status-meta"
                    >
                        Návrh vytvorený: {{ formatDate(internship['created_at']) }}
                    </p>

                    <p
                        v-else-if="!internship.is_draft && internship.status?.changed_at"
                        class="student-prax-detail__status-meta"
                    >
                        Stav aktualizovaný:
                        {{ formatDate(internship.status.changed_at) }}
                    </p>
                </div>
            </section>

            <!-- Akcie -->
            <section class="student-prax-detail__actions">
                <button
                    type="button"
                    class="student-prax-detail__button"
                    :disabled="isDownloading"
                    @click="downloadPdf"
                >
                    <span v-if="isDownloading">Sťahujem PDF...</span>
                    <span v-else>Stiahnuť PDF dohody</span>
                </button>

                <button
                    type="button"
                    class="student-prax-detail__button student-prax-detail__button--ghost"
                    @click="emit('back-to-list')"
                >
                    Späť na zoznam
                </button>
            </section>

            <p
                v-if="pdfError"
                class="student-prax-detail__pdf-error"
            >
                {{ pdfError }}
            </p>
        </div>
    </section>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { useInternshipStore } from '@/stores/internship';
import {
    fetchStudentInternshipDetail,
    downloadStudentInternshipAgreementPdf,
} from '@/services/internship';
import InternshipStatusBadge from '@/components/Dashboard/General/InternshipStatusBadge.vue';

const props = defineProps({
    internshipId: {
        type: [Number, String],
        required: true,
    },
});

const emit = defineEmits(['back-to-list']);

const internshipStore = useInternshipStore();

const internship = ref(null);
const isLoading = ref(false);
const error = ref('');
const nonBlockingError = ref('');
const isDownloading = ref(false);
const pdfError = ref('');

const numericId = computed(() => {
    const n = Number(props.internshipId);
    return Number.isFinite(n) ? n : null;
});

const contactPerson = computed(() =>
    internship.value?.company?.['contact_person'] ?? null
);

function toTime(value) {
    const t = new Date(value).getTime();
    return Number.isFinite(t) ? t : 0;
}

async function loadDetail() {
    isLoading.value = true;
    error.value = '';
    nonBlockingError.value = '';
    pdfError.value = '';

    if (!numericId.value) {
        internship.value = null;
        error.value = 'Neplatné ID praxe.';
        isLoading.value = false;
        return;
    }

    try {
        // If we have an item in store, use it as quick fallback (clone to avoid mutating Pinia object)
        const fromStore = internshipStore.internshipById(numericId.value);
        if (fromStore) {
            internship.value = { ...fromStore };
        }

        const data = await fetchStudentInternshipDetail(numericId.value);

        // Primary data
        internship.value = data;

        // Extract latest status safely (do not assume ordering)
        const historyRaw = Array.isArray(data?.['status_history'])
            ? data['status_history']
            : [];

        const history = historyRaw.flat ? historyRaw.flat() : historyRaw;

        if (history.length > 0) {
            const latest = history
                .slice()
                .sort(
                    (a, b) =>
                        toTime(b?.['status_changed_at']) -
                        toTime(a?.['status_changed_at'])
                )[0];

            internship.value.status = {
                name: latest?.status ?? internship.value.status?.name ?? null,
                changed_at:
                    latest?.['status_changed_at'] ??
                    internship.value.status?.changed_at ??
                    null,
            };
        }

        // Keep list/store in sync with newes-t version of the internship
        internshipStore.upsertInternship(internship.value);
    } catch (e) {
        console.error('[StudentPraxDetail] Failed to load detail', e);

        // If we have no fallback data, show blocking error
        if (!internship.value) {
            error.value = 'Nepodarilo sa načítať detail praxe.';
        } else {
            // Otherwise show a non-blocking message (user still sees basic info)
            nonBlockingError.value =
                'Detail praxe sa nepodarilo načítať. Zobrazujú sa údaje zo zoznamu.';
        }
    } finally {
        isLoading.value = false;
    }
}

function reload() {
    loadDetail();
}

async function downloadPdf() {
    if (!numericId.value) return;

    isDownloading.value = true;
    pdfError.value = '';

    try {
        const response =
            await downloadStudentInternshipAgreementPdf(numericId.value);
        const blob = new Blob([response.data], { type: 'application/pdf' });
        const url = URL.createObjectURL(blob);

        const a = document.createElement('a');
        a.href = url;
        a.download = `dohoda-praxe-${numericId.value}.pdf`;
        a.click();

        URL.revokeObjectURL(url);
    } catch (e) {
        console.error('[StudentPraxDetail] Failed to download PDF', e);

        const status = e.response?.status;
        const message = e.response?.data?.message;

        if (status === 403) {
            pdfError.value = 'Tento dokument nie je dostupný pre váš účet.';
        } else if (status === 409) {
            pdfError.value = message || 'PDF dohody nie je momentálne dostupné.';
        } else {
            pdfError.value = 'Pri sťahovaní PDF došlo k chybe. Skúste to znova.';
        }
    } finally {
        isDownloading.value = false;
    }
}

function formatDate(value) {
    if (!value) return '';
    const datePart = String(value).split('T')[0];
    const [year, month, day] = datePart.split('-').map(Number);
    if (!year || !month || !day) return value;
    return `${day}.${month}.${year}`;
}

function mapSeasonLabel(seasonRaw) {
    if (!seasonRaw) return '';
    const s = String(seasonRaw).toLowerCase();

    if (['winter', 'zimny', 'zimný', 'zima'].includes(s)) {
        return 'Zimný semester';
    }
    if (['summer', 'letny', 'letný', 'leto'].includes(s)) {
        return 'Letný semester';
    }
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
    return [range, seasonLabel].filter(Boolean).join(' – ');
}

onMounted(loadDetail);
watch(
    () => props.internshipId,
    () => {
        loadDetail();
    }
);
</script>
