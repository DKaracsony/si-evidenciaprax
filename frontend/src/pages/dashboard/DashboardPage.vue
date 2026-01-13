<template>
    <div class="dashboard-page">
        <AppHeader />

        <main class="dashboard-page__main">
            <div class="container">
                <section class="dashboard-page__inner">
                    <section class="dashboard-page__content">
                        <!-- 👨‍🎓 STUDENT DASHBOARD -->
                        <template v-if="role === ROLE_STUDENT">
                            <StudentPraxList
                                v-if="view === 'list'"
                                @new-internship="handleNewInternship"
                                @open-detail="handleOpenDetail"
                                @edit-draft="handleEditDraft"
                            />

                            <NewPraxForm
                                v-else-if="view === 'new'"
                                @created="handleCreated"
                                @cancel="view = 'list'"
                            />

                            <NewPraxSuccess
                                v-else-if="view === 'success'"
                                @back-to-list="handleBackToList"
                            />

                            <StudentPraxDetail
                                v-else-if="view === 'detail'"
                                :internship-id="selectedInternshipId"
                                @back-to-list="handleBackToList"
                            />

                            <EditPraxForm
                                v-else-if="view === 'edit'"
                                :internship="internshipStore.internshipById(editingInternshipId)"
                                @created="handleCreated"
                                @cancel="view = 'list'"
                            />
                        </template>

                        <!-- 🏢 COMPANY DASHBOARD -->
                        <template v-else-if="role === ROLE_COMPANY">
                            <CompanyPraxList />
                        </template>

                        <!-- 👨‍🏫 GARANT DASHBOARD -->
                        <template v-else-if="role === ROLE_GARANT">
                            <GarantPraxList
                                v-if="view === 'list'"
                                @open-statistics="view = 'stats'"
                            />

                            <GarantPraxStatistics
                                v-else-if="view === 'stats'"
                                @back="view = 'list'"
                            />
                        </template>

                        <!-- Fallback -->
                        <template v-else>
                            <article class="dashboard-empty-card">
                                <h2 class="dashboard-empty-card__title">
                                    Dashboard nie je k dispozícii
                                </h2>
                                <p class="dashboard-empty-card__text">
                                    Váš účet nemá priradenú podporovanú rolu
                                    (študent, firma, garant) alebo nastala chyba pri načítaní.
                                </p>
                            </article>
                        </template>
                    </section>
                </section>
            </div>
        </main>

        <Footer />
    </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import { storeToRefs } from 'pinia';

import AppHeader from '../../components/Navbar/Navbar.vue';
import Footer from '../../components/Footer/Footer.vue';

import StudentPraxList from '../../components/Dashboard/Student/StudentPraxList.vue';
import NewPraxForm from '../../components/Dashboard/Student/NewPraxForm.vue';
import EditPraxForm from '../../components/Dashboard/Student/EditPraxForm.vue';
import NewPraxSuccess from '../../components/Dashboard/Student/NewPraxSuccess.vue';
import StudentPraxDetail from '../../components/Dashboard/Student/StudentPraxDetail.vue';

import CompanyPraxList from '../../components/Dashboard/Company/CompanyPraxList.vue';
import GarantPraxList from '../../components/Dashboard/Garant/GarantPraxList.vue';
import GarantPraxStatistics from '../../components/Dashboard/Garant/GarantPraxStatistics.vue';

import { useInternshipStore } from '@/stores/internship.js';
import { useGarantStatisticsStore } from '@/stores/garantStatistics';
import { useAuthStore } from '@/stores/auth.js';
import {
    ROLE_STUDENT,
    ROLE_COMPANY,
    ROLE_GARANT,
} from '@/constants/roles.js';

/* -------------------- STATE -------------------- */

const view = ref('list');
const selectedInternshipId = ref(null);
const editingInternshipId = ref(null);

const internshipStore = useInternshipStore();
const garantStatisticsStore = useGarantStatisticsStore();

/* -------------------- AUTH -------------------- */

const authStore = useAuthStore();
const { role } = storeToRefs(authStore);

/* -------------------- HARD SAFETY -------------------- */
/* Reset ALL role-specific cached data on role change */

watch(role, () => {
    internshipStore.reset();
    garantStatisticsStore.reset();
    view.value = 'list';
});

/* -------------------- HANDLERS -------------------- */

function handleNewInternship() {
    view.value = 'new';
}

function handleOpenDetail(id) {
    selectedInternshipId.value = id;
    view.value = 'detail';
}

function handleEditDraft(id) {
    editingInternshipId.value = id;
    view.value = 'edit';
}

function handleBackToList() {
    view.value = 'list';
}

async function handleCreated({ internship, isDraft }) {
    view.value = isDraft ? 'list' : 'success';

    try {
        await internshipStore.loadList({ force: true });
    } catch (error) {
        console.error(
            '[DashboardPage] Nepodarilo sa reloadnúť zoznam praxí',
            error
        );
    }
}
</script>
