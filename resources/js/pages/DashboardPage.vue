<template>
    <div class="dashboard-page">
        <LandingHeader />

        <main class="dashboard-page__main">
            <div class="container">
                <section class="dashboard-page__inner">
                    <section class="dashboard-page__content">
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
                    </section>
                </section>
            </div>
        </main>

        <LandingFooter />
    </div>
</template>




<script setup>
import { ref } from 'vue';
import LandingHeader from '../components/LandingHeader.vue';
import LandingFooter from '../components/LandingFooter.vue';
import StudentPraxList from '../components/Dashboard/StudentPraxList.vue';
import NewPraxForm from '../components/Dashboard/NewPraxForm.vue';
import EditPraxForm from '../components/Dashboard/EditPraxForm.vue'; // ⬅️ NEW
import NewPraxSuccess from '../components/Dashboard/NewPraxSuccess.vue';
import StudentPraxDetail from '../components/Dashboard/StudentPraxDetail.vue';
import { useInternshipStore } from '../stores/internship';

const view = ref('list'); // 'list' | 'new' | 'success' | 'detail' | 'edit'
const selectedInternshipId = ref(null);
const editingInternshipId = ref(null);

const internshipStore = useInternshipStore();

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
    // final submit → success screen
    view.value = isDraft ? 'list' : 'success';

    try {
        await internshipStore.loadList({ force: true });
    } catch (error) {
        console.error('[DashboardPage] Nepodarilo sa reloadnúť zoznam praxí', error);
    }
}
</script>



