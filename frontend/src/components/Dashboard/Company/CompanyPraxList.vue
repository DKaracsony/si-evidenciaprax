<!-- resources/js/components/internships/CompanyPraxList.vue -->
<template>
    <section class="student-prax-list">
        <!-- ===================== DETAIL VIEW ===================== -->
        <CompanyPraxDetail
            v-if="showDetail && selectedInternshipId"
            :internship-id="selectedInternshipId"
            @back-to-list="closeDetail"
        />

        <!-- ===================== LIST VIEW ======================= -->
        <template v-else>
            <header class="student-prax-list__header">
                <div class="student-prax-list__header-row">
                    <h2 class="student-prax-list__title">
                        Zoznam pridelených odborných praxí
                    </h2>
                </div>

                <div class="student-prax-list__divider"></div>
            </header>

            <!-- LOADING -->
            <div v-if="isLoading && !items.length" class="student-prax-list__state">
                Načítavam praxe firmy...
            </div>

            <!-- ERROR -->
            <div
                v-else-if="error"
                class="student-prax-list__state student-prax-list__state--error"
            >
                Nepodarilo sa načítať praxe firmy.
            </div>

            <!-- EMPTY -->
            <div v-else-if="!items.length" class="student-prax-list__state">
                Firma nemá žiadne praxe.
            </div>

            <!-- LIST -->
            <ul v-else class="student-prax-list__items">
                <li
                    v-for="internship in items"
                    :key="internship.id"
                    class="student-prax-item"
                >
                    <!-- MAIN -->
                    <div class="student-prax-item__main">
                        <h3 class="student-prax-item__company">
                            {{ internship.company?.name ?? 'Neznáma firma' }}
                            <span
                                v-if="internship.company_city"
                                class="student-prax-item__company-city"
                            >
                                ({{ internship.company_city }})
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

                    <!-- SIDE -->
                    <div class="student-prax-item__side">
                        <InternshipStatusBadge
                            :is-draft="false"
                            :status-name="internship.status?.name ?? null"
                        />

                        <p
                            v-if="internship.status?.changed_at"
                            class="student-prax-item__status-meta"
                        >
                            Stav aktualizovaný:
                            {{ formatDate(internship.status.changed_at) }}
                        </p>

                        <!-- ACTIONS -->
                        <div class="student-prax-item__actions-row">
                            <!-- VYTVORENÁ -->
                            <template v-if="internship.status?.name === 'Vytvorená'">
                                <button
                                    type="button"
                                    class="student-prax-item__cta-button student-prax-item__cta-button--approve"
                                    :disabled="approvingId === internship.id"
                                    @click="handleApprove(internship.id)"
                                >
                                    <span v-if="approvingId === internship.id">
                                        Potvrdzujem…
                                    </span>
                                    <span v-else>Potvrdiť</span>
                                </button>

                                <button
                                    type="button"
                                    class="student-prax-item__cta-button student-prax-item__cta-button--reject"
                                    @click="openRejectModal(internship.id)"
                                >
                                    Zamietnuť
                                </button>

                                <button
                                    type="button"
                                    class="student-prax-item__cta-button"
                                    @click="openDetail(internship.id)"
                                >
                                    Zobraziť detail
                                </button>
                            </template>

                            <!-- OSTATNÉ STAVY -->
                            <template v-else>
                                <button
                                    type="button"
                                    class="student-prax-item__cta-button"
                                    @click="openDetail(internship.id)"
                                >
                                    Zobraziť detail
                                </button>
                            </template>
                        </div>
                    </div>
                </li>
            </ul>

            <!-- REJECT MODAL -->
            <RejectInternshipModal
                v-if="isRejectModalOpen"
                :loading="isRejecting"
                @close="closeRejectModal"
                @submit="handleRejectSubmit"
            />
        </template>
    </section>
</template>

<script setup>
import { ref, onMounted } from 'vue';

import InternshipStatusBadge from '../General/InternshipStatusBadge.vue';
import RejectInternshipModal from '../Company/RejectInternshipModal.vue';
import CompanyPraxDetail from './CompanyPraxDetail.vue';

import {
    fetchCompanyInternships,
    acceptCompanyInternship,
    rejectCompanyInternship,
} from '@/services/companyInternships.js';

import { fetchCompanyDetail } from '@/services/company.js';

/* ===================== STATE ===================== */

const items = ref([]);
const isLoading = ref(false);
const error = ref(null);
const companyCityCache = ref({});
const approvingId = ref(null);

/* DETAIL STATE */
const showDetail = ref(false);
const selectedInternshipId = ref(null);

/* REJECT STATE */
const isRejectModalOpen = ref(false);
const rejectingId = ref(null);
const isRejecting = ref(false);

/* ===================== DETAIL HANDLERS ===================== */

function openDetail(id) {
    selectedInternshipId.value = id;
    showDetail.value = true;
}

function closeDetail() {
    showDetail.value = false;
    selectedInternshipId.value = null;
}

/* ===================== DATA LOAD ===================== */

async function load() {
    isLoading.value = true;
    error.value = null;

    try {
        const raw = await fetchCompanyInternships();

        items.value = raw.map((internship) => {
            const latestHistory = internship.internship_status_histories
                ?.slice()
                .sort(
                    (a, b) =>
                        new Date(b.status_changed_at) -
                        new Date(a.status_changed_at)
                )[0] ?? null;

            return {
                id: internship.id,
                start_date: internship.start_date,
                date_to: internship.date_to,
                description: internship.description ?? null,
                company: internship.company ?? null,
                company_city: null,
                semester: internship.academic_year
                    ? {
                        id: internship.academic_year.id,
                        season: internship.academic_year.season,
                        start_date: internship.academic_year.start_date,
                        end_date: internship.academic_year.end_date,
                    }
                    : null,
                status: latestHistory
                    ? {
                        name: latestHistory.status?.name ?? null,
                        changed_at: latestHistory.status_changed_at,
                    }
                    : null,
            };
        });

        await loadCompanyCities();
    } catch (e) {
        error.value = e;
        items.value = [];
    } finally {
        isLoading.value = false;
    }
}

async function loadCompanyCities() {
    const companyIds = [
        ...new Set(items.value.map(i => i.company?.id).filter(Boolean)),
    ];

    await Promise.all(
        companyIds.map(async (companyId) => {
            if (companyCityCache.value[companyId]) return;

            try {
                const company = await fetchCompanyDetail(companyId);
                companyCityCache.value[companyId] =
                    company.address?.city ?? null;
            } catch {
                companyCityCache.value[companyId] = null;
            }
        })
    );

    items.value = items.value.map(item => ({
        ...item,
        company_city: item.company?.id
            ? companyCityCache.value[item.company.id] ?? null
            : null,
    }));
}

/* ===================== ACTIONS ===================== */

async function handleApprove(id) {
    approvingId.value = id;

    try {
        await acceptCompanyInternship(id);
        await load();
    } catch {
        alert('Nepodarilo sa potvrdiť prax.');
    } finally {
        approvingId.value = null;
    }
}

function openRejectModal(id) {
    rejectingId.value = id;
    isRejectModalOpen.value = true;
}

function closeRejectModal() {
    if (isRejecting.value) return;
    isRejectModalOpen.value = false;
    rejectingId.value = null;
}

async function handleRejectSubmit(note) {
    if (isRejecting.value) return;

    isRejecting.value = true;

    try {
        await rejectCompanyInternship(rejectingId.value, note);
        await load();
    } catch {
        alert('Nepodarilo sa zamietnuť prax.');
    } finally {
        isRejecting.value = false;
        closeRejectModal();
    }
}

onMounted(load);

/* ===================== HELPERS ===================== */

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
</script>
