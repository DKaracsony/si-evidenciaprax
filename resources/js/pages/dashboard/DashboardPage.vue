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

                        <!-- 🏢 COMPANY DASHBOARD – will be implemented later -->
                        <template v-else-if="role === ROLE_COMPANY">
                            <!--
                              TODO: sem neskôr príde firemný dashboard
                              (napr. zoznam praxí firmy, prehľady atď.)
                            -->
                        </template>

                        <!-- 👨‍🏫 GARANT DASHBOARD – will be implemented later -->
                        <template v-else-if="role === ROLE_GARANT">
                            <!--
                              TODO: sem neskôr príde garant dashboard
                            -->
                        </template>

                        <!-- Fallback – neznáma rola alebo chýbajúce dáta -->
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
import { ref } from 'vue';
import { storeToRefs } from 'pinia';

import AppHeader from '../../components/Navbar/Navbar.vue';
import Footer from '../../components/Footer/Footer.vue';

import StudentPraxList from '../../components/Dashboard/Student/StudentPraxList.vue';
import NewPraxForm from '../../components/Dashboard/Student/NewPraxForm.vue';
import EditPraxForm from '../../components/Dashboard/Student/EditPraxForm.vue';
import NewPraxSuccess from '../../components/Dashboard/Student/NewPraxSuccess.vue';
import StudentPraxDetail from '../../components/Dashboard/Student/StudentPraxDetail.vue';

import { useInternshipStore } from '@/stores/internship.js';
import { useAuthStore } from '@/stores/auth.js';
import {
    ROLE_STUDENT,
    ROLE_COMPANY,
    ROLE_GARANT,
} from '@/constants/roles.js';

const view = ref('list'); // 'list' | 'new' | 'success' | 'detail' | 'edit'
const selectedInternshipId = ref(null);
const editingInternshipId = ref(null);

const internshipStore = useInternshipStore();

// 🔐 Auth – rola z Pinie (reactive)
const authStore = useAuthStore();
const { role } = storeToRefs(authStore);

// Handlery view-u
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
    console.log('Prax vytvorená/uložená', { internship, isDraft });

    // draft (nový alebo update draftu) → späť na list
    // finálne odoslanie → success screen
    view.value = isDraft ? 'list' : 'success';

    try {
        await internshipStore.loadList({ force: true });
    } catch (error) {
        console.error('[DashboardPage] Nepodarilo sa reloadnúť zoznam praxí', error);
    }
}
</script>
