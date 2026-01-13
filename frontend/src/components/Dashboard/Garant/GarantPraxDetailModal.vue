<template>
    <div class="garant-prax-detail-modal__overlay">
        <div class="garant-prax-detail-modal">
            <!-- HEADER -->
            <header class="garant-prax-detail-modal__header">
                <h2 class="garant-prax-detail-modal__title">
                    Detail odbornej praxe
                </h2>

                <button
                    type="button"
                    class="garant-prax-detail-modal__close"
                    aria-label="Zavrieť detail"
                    @click="emit('close')"
                >
                    ✕
                </button>
            </header>

            <div class="garant-prax-list__divider"></div>

            <!-- LOADING -->
            <div
                v-if="isLoading"
                class="garant-prax-detail-modal__state"
            >
                Načítavam detail praxe…
            </div>

            <!-- ERROR -->
            <div
                v-else-if="error"
                class="garant-prax-detail-modal__state garant-prax-detail-modal__state--error"
            >
                <p>{{ error }}</p>

                <button
                    type="button"
                    class="garant-prax-detail-modal__button garant-prax-detail-modal__button--outline"
                    @click="loadDetail"
                >
                    Skúsiť znova
                </button>
            </div>

            <!-- CONTENT -->
            <div
                v-else-if="internship"
                class="garant-prax-detail-modal__content"
            >
                <!-- STUDENT -->
                <section class="garant-prax-detail-modal__section">
                    <h3 class="garant-prax-detail-modal__section-title">
                        Študent
                    </h3>

                    <p class="garant-prax-detail-modal__row">
                        {{ formatStudent(internship.student) }}
                    </p>

                    <p
                        v-if="internship.faculty"
                        class="garant-prax-detail-modal__row"
                    >
                        <span class="garant-prax-detail-modal__row-label">
                            Fakulta:
                        </span>
                        <span>{{ internship.faculty.name }}</span>
                    </p>
                </section>

                <!-- COMPANY -->
                <section class="garant-prax-detail-modal__section">
                    <h3 class="garant-prax-detail-modal__section-title">
                        Firma
                    </h3>

                    <p class="garant-prax-detail-modal__company-name">
                        {{ company?.name ?? 'Neznáma firma' }}
                    </p>

                    <p
                        v-if="company?.description"
                        class="garant-prax-detail-modal__text-muted"
                    >
                        {{ company.description }}
                    </p>

                    <p
                        v-if="company?.website"
                        class="garant-prax-detail-modal__row"
                    >
                        <span class="garant-prax-detail-modal__row-label">Web:</span>
                        <a
                            :href="company.website"
                            target="_blank"
                            rel="noopener noreferrer"
                        >
                            {{ company.website }}
                        </a>
                    </p>

                    <p
                        v-if="company?.address"
                        class="garant-prax-detail-modal__row"
                    >
                        <span class="garant-prax-detail-modal__row-label">
                            Adresa:
                        </span>
                        <span>
                            {{ company.address.street }}
                            {{ company.address.house_number }},
                            {{ company.address.postal_code }}
                            {{ company.address.city }}
                            <span v-if="company.address.country">
                                , {{ company.address.country.name }}
                            </span>
                        </span>
                    </p>

                    <p
                        v-if="company?.contact_person"
                        class="garant-prax-detail-modal__row"
                    >
                        <span class="garant-prax-detail-modal__row-label">
                            Kontakt:
                        </span>
                        <span>
                            {{ company.contact_person.title_before }}
                            {{ company.contact_person.first_name }}
                            {{ company.contact_person.last_name }}
                            {{ company.contact_person.title_after }}
                            <span v-if="company.contact_person.email">
                                – {{ company.contact_person.email }}
                            </span>
                            <span v-if="company.contact_person.phone_number">
                                · Tel: {{ company.contact_person.phone_number }}
                            </span>
                        </span>
                    </p>
                </section>

                <!-- DATES + SEMESTER -->
                <section class="garant-prax-detail-modal__section">
                    <h3 class="garant-prax-detail-modal__section-title">
                        Termín a semester
                    </h3>

                    <p class="garant-prax-detail-modal__row">
                        <span class="garant-prax-detail-modal__row-label">
                            Začiatok:
                        </span>
                        <span>{{ formatDate(internship.start_date) }}</span>
                    </p>

                    <p class="garant-prax-detail-modal__row">
                        <span class="garant-prax-detail-modal__row-label">
                            Koniec:
                        </span>
                        <span>{{ formatDate(internship.end_date) }}</span>
                    </p>

                    <p
                        v-if="internship.semester"
                        class="garant-prax-detail-modal__row"
                    >
                        <span class="garant-prax-detail-modal__row-label">
                            Akademický rok / semester:
                        </span>
                        <span>{{ formatSemester(internship.semester) }}</span>
                    </p>
                </section>

                <!-- STATUS -->
                <section class="garant-prax-detail-modal__section garant-prax-detail-modal__section--status">
                    <InternshipStatusBadge
                        :is-draft="false"
                        :status-name="internship.status?.name ?? null"
                    />

                    <p
                        v-if="internship.status?.changed_at"
                        class="garant-prax-detail-modal__status-meta"
                    >
                        Stav aktualizovaný:
                        {{ formatDate(internship.status.changed_at) }}
                    </p>
                </section>


                <!-- ACTIONS -->
                <section class="garant-prax-detail-modal__actions">
                    <button
                        type="button"
                        class="garant-prax-detail-modal__button"
                        @click="emit('close')"
                    >
                        Zavrieť detail
                    </button>
                </section>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import axios from 'axios';
import InternshipStatusBadge from '@/components/Dashboard/General/InternshipStatusBadge.vue';

const props = defineProps({
        internship: {
            type: Object,
                required: true,
            },
});

const emit = defineEmits(['close']);

const internship = computed(() => props.internship);
const company = ref(null);
const isLoading = ref(false);
const error = ref('');

const numericId = computed(() => {
    const n = Number(props.internshipId);
    return Number.isFinite(n) ? n : null;
});

async function loadDetail() {
    isLoading.value = true;
    error.value = '';
    company.value = null;

    try {
        if (props.internship.company?.id) {
            const companyRes = await axios.get(
                `/api/companies/${props.internship.company.id}`
            );
            company.value = companyRes.data ?? props.internship.company;
        }
    } catch (e) {
        console.warn('[GarantPraxDetailModal] Company load failed', e);
        company.value = props.internship.company ?? null;
    } finally {
        isLoading.value = false;
    }
}

function formatStudent(s) {
    if (!s) return '—';
    return [s.title_before, s.first_name, s.last_name]
        .filter(Boolean)
        .join(' ');
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
    return [range, mapSeasonLabel(semester.season)]
        .filter(Boolean)
        .join(' – ');
}

onMounted(loadDetail);
watch(() => props.internship, loadDetail);
</script>
