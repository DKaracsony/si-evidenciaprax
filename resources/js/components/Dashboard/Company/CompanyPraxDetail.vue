<template>
    <section class="company-prax-detail">
        <header class="company-prax-detail__header">
            <h2 class="company-prax-detail__title">
                Detail odbornej praxe
            </h2>

            <div class="student-prax-list__divider company-prax-detail__divider"></div>
        </header>

        <!-- LOADING -->
        <div v-if="isLoading" class="company-prax-detail__state">
            Načítavam detail praxe…
        </div>

        <!-- ERROR -->
        <div
            v-else-if="error"
            class="company-prax-detail__state company-prax-detail__state--error"
        >
            <p>{{ error }}</p>
            <button
                type="button"
                class="company-prax-detail__button company-prax-detail__button--outline"
                @click="reload"
            >
                Skúsiť znova
            </button>
        </div>

        <!-- CONTENT -->
        <div v-else-if="internship" class="company-prax-detail__content">
            <!-- Firma -->
            <section class="company-prax-detail__section">
                <h3 class="company-prax-detail__section-title">
                    Firma
                </h3>

                <p class="company-prax-detail__company-name">
                    {{ internship.company?.name ?? 'Neznáma firma' }}
                </p>

                <p
                    v-if="internship.company?.description"
                    class="company-prax-detail__text-muted"
                >
                    {{ internship.company.description }}
                </p>

                <p
                    v-if="internship.company?.website"
                    class="company-prax-detail__row"
                >
                    <span class="company-prax-detail__row-label">Web:</span>
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
                    class="company-prax-detail__row"
                >
                    <span class="company-prax-detail__row-label">Adresa:</span>
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

                <!-- FALLBACK WARNING -->
                <p
                    v-if="nonBlockingError"
                    class="company-prax-detail__text-muted"
                >
                    {{ nonBlockingError }}
                </p>
            </section>

            <!-- Termín + semester -->
            <section class="company-prax-detail__section">
                <h3 class="company-prax-detail__section-title">
                    Termín a semester
                </h3>

                <p class="company-prax-detail__row">
                    <span class="company-prax-detail__row-label">Začiatok:</span>
                    <span>{{ formatDate(internship.start_date) }}</span>
                </p>

                <p class="company-prax-detail__row">
                    <span class="company-prax-detail__row-label">Koniec:</span>
                    <span>{{ formatDate(internship.date_to) }}</span>
                </p>

                <p
                    v-if="internship.semester"
                    class="company-prax-detail__row"
                >
                    <span class="company-prax-detail__row-label">
                        Akademický rok / semester:
                    </span>
                    <span>{{ formatSemester(internship.semester) }}</span>
                </p>
            </section>

            <!-- Popis -->
            <section class="company-prax-detail__section">
                <h3 class="company-prax-detail__section-title">
                    Popis praxe
                </h3>

                <p class="company-prax-detail__description">
                    {{ internship.description || 'Bez popisu.' }}
                </p>
            </section>

            <!-- Stav -->
            <section class="company-prax-detail__section company-prax-detail__section--status">
                <InternshipStatusBadge
                    :is-draft="false"
                    :status-name="internship.status?.name ?? null"
                />

                <p
                    v-if="internship.status?.changed_at"
                    class="company-prax-detail__status-meta"
                >
                    Stav aktualizovaný:
                    {{ formatDate(internship.status.changed_at) }}
                </p>
            </section>

            <!-- ACTIONS -->
            <section class="company-prax-detail__actions">
                <button
                    type="button"
                    class="company-prax-detail__button"
                    @click="emit('back-to-list')"
                >
                    Späť na zoznam
                </button>
            </section>
        </div>
    </section>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import InternshipStatusBadge from '@/components/Dashboard/General/InternshipStatusBadge.vue';
import { fetchCompanyInternships } from '@/services/companyInternships';
import { fetchCompanyDetail } from '@/services/company';

const props = defineProps({
    internshipId: {
        type: [Number, String],
        required: true,
    },
});

const emit = defineEmits(['back-to-list']);

const internship = ref(null);
const isLoading = ref(false);
const error = ref('');
const nonBlockingError = ref('');

const numericId = computed(() => {
    const n = Number(props.internshipId);
    return Number.isFinite(n) ? n : null;
});

async function loadDetail() {
    isLoading.value = true;
    error.value = '';
    nonBlockingError.value = '';

    if (!numericId.value) {
        error.value = 'Neplatné ID praxe.';
        isLoading.value = false;
        return;
    }

    try {
        const list = await fetchCompanyInternships();
        const raw = list.find(i => String(i.id) === String(numericId.value));

        if (!raw) {
            error.value = 'Detail praxe sa nepodarilo načítať.';
            return;
        }

        const latestHistory = raw.internship_status_histories
            ?.slice()
            .sort(
                (a, b) =>
                    new Date(b.status_changed_at) -
                    new Date(a.status_changed_at)
            )[0] ?? null;

        let company = raw.company ?? null;

        // 🔑 CONDITIONAL enrichment (fallback only)
        if (company?.id) {
            try {
                const fullCompany = await fetchCompanyDetail(company.id);
                company = {
                    ...company,
                    website: fullCompany.website ?? null,
                    address: fullCompany.address ?? null,
                };
            } catch {
                /* silent fallback */
            }
        }

        internship.value = {
            id: raw.id,
            start_date: raw.start_date,
            date_to: raw.date_to,
            description: raw.description ?? null,
            company,
            semester: raw.academic_year
                ? {
                    id: raw.academic_year.id,
                    season: raw.academic_year.season,
                    start_date: raw.academic_year.start_date,
                    end_date: raw.academic_year.end_date,
                }
                : null,
            status: latestHistory
                ? {
                    name: latestHistory.status?.name ?? null,
                    changed_at: latestHistory.status_changed_at,
                }
                : null,
        };

        nonBlockingError.value =
            'Detail praxe sa nepodarilo načítať. Zobrazujú sa údaje zo zoznamu.';
    } catch (e) {
        console.error('[CompanyPraxDetail] Failed to load detail', e);
        error.value = 'Nepodarilo sa načítať detail praxe.';
    } finally {
        isLoading.value = false;
    }
}

function reload() {
    loadDetail();
}

function formatDate(value) {
    if (!value) return '';
    const [y, m, d] = String(value).split('T')[0].split('-').map(Number);
    return y && m && d ? `${d}.${m}.${y}` : value;
}

function mapSeasonLabel(seasonRaw) {
    if (!seasonRaw) return '';
    const s = seasonRaw.toLowerCase();
    if (['winter', 'zimny', 'zimný', 'zima'].includes(s)) return 'Zimný semester';
    if (['summer', 'letny', 'letný', 'leto'].includes(s)) return 'Letný semester';
    return seasonRaw;
}

function formatSemester(semester) {
    const sy = new Date(semester.start_date).getFullYear();
    const ey = new Date(semester.end_date).getFullYear();
    const range =
        sy && ey && sy !== ey ? `${sy}/${String(ey).slice(-2)}` : sy || '';
    return [range, mapSeasonLabel(semester.season)].filter(Boolean).join(' – ');
}

onMounted(loadDetail);
watch(() => props.internshipId, loadDetail);
</script>
