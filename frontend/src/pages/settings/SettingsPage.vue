<template>
    <div class="page page--settings">
        <AppHeader />

        <main class="settings-layout">
            <!-- SIDEBAR -->
            <aside
                class="settings-sidebar"
                :class="{ collapsed: isCollapsed }"
            >
                <button
                    class="sidebar-toggle"
                    @click="isCollapsed = !isCollapsed"
                >
                    <span v-if="!isCollapsed">«</span>
                    <span v-else>»</span>
                </button>

                <nav v-if="!isCollapsed" class="sidebar-menu">
                    <p class="menu-title">Nastavenia</p>

                    <ul>
                        <li
                            :class="{ active: activeSection === 'general' }"
                            @click="activeSection = 'general'"
                        >
                            Zmena hesla účtu
                        </li>

                        <!-- GARANT ONLY -->
                        <li
                            v-if="role === ROLE_GARANT"
                            :class="{ active: activeSection === 'garant-faculties' }"
                            @click="activeSection = 'garant-faculties'"
                        >
                            Predvolené odbory
                        </li>
                    </ul>
                </nav>
            </aside>

            <!-- MAIN CONTENT -->
            <section class="settings-content">
                <!-- ================= PASSWORD ================= -->
                <div v-if="activeSection === 'general'" class="password-section">
                    <div class="settings-title-wrap">
                        <h1 class="settings-title">
                            Zmeniť heslo účtu
                        </h1>
                        <div class="settings-title-divider"></div>
                    </div>

                    <form class="password-form" @submit.prevent="submitChange">
                        <label class="label">Aktuálne heslo</label>
                        <input
                            type="password"
                            v-model="currentPassword"
                            class="input"
                        />
                        <p v-if="errors.current" class="error">
                            {{ errors.current }}
                        </p>

                        <label class="label">Nové heslo</label>
                        <input
                            type="password"
                            v-model="newPassword"
                            class="input"
                        />
                        <p v-if="errors.new" class="error">
                            {{ errors.new }}
                        </p>

                        <label class="label">Potvrďte nové heslo</label>
                        <input
                            type="password"
                            v-model="newPasswordConfirm"
                            class="input"
                        />
                        <p v-if="errors.confirm" class="error">
                            {{ errors.confirm }}
                        </p>

                        <p v-if="success" class="success">
                            Vaše heslo bolo úspešne zmenené
                        </p>

                        <button
                            type="submit"
                            class="lp-first__btn-register submit-btn"
                            :disabled="loading"
                        >
                            {{ loading ? 'Nastavujem...' : 'Zmeniť heslo' }}
                        </button>
                    </form>
                </div>

                <!-- ================= GARANT FACULTIES ================= -->
                <div
                    v-if="activeSection === 'garant-faculties' && role === ROLE_GARANT"
                    class="garant-faculties-section"
                >
                    <div class="settings-title-wrap">
                        <h1 class="settings-title">
                            Predvolené študijné odbory
                        </h1>
                        <div class="settings-title-divider"></div>
                    </div>

                    <p class="garant-faculties-note">
                        Vyberte jeden alebo viac študijných odborov, ktoré budú
                        predvolene použité pri zobrazovaní praxí.
                    </p>

                    <div class="garant-faculties-list">
                        <label
                            v-for="faculty in faculties"
                            :key="faculty.id"
                            class="faculty-checkbox"
                        >
                            <input
                                type="checkbox"
                                :value="faculty.id"
                                v-model="selectedFacultyIds"
                            />
                            {{ faculty.name }}
                        </label>
                    </div>

                    <p v-if="facultiesError" class="error">
                        {{ facultiesError }}
                    </p>

                    <p v-if="facultiesSuccess" class="success">
                        Predvolené odbory boli uložené.
                    </p>

                    <button
                        type="button"
                        class="lp-first__btn-register submit-btn"
                        :disabled="facultiesLoading"
                        @click="saveFaculties"
                    >
                        {{ facultiesLoading ? 'Ukladám...' : 'Uložiť zmeny' }}
                    </button>
                </div>
            </section>
        </main>

        <Footer />
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { storeToRefs } from 'pinia';

import AppHeader from '../../components/Navbar/Navbar.vue';
import Footer from '../../components/Footer/Footer.vue';

import { changePassword, fetchAndStoreUser } from '../../services/auth.js';
import { fetchMyFaculties, saveMyFaculties } from '../../services/garantFaculties.js';

import { useAuthStore } from '@/stores/auth.js';
import { ROLE_GARANT } from '@/constants/roles.js';

// ---------------- AUTH / ROLE ----------------
const authStore = useAuthStore();
const { role } = storeToRefs(authStore);

// ---------------- SIDEBAR ----------------
const isCollapsed = ref(false);
const activeSection = ref('general');

// ---------------- PASSWORD ----------------
const currentPassword = ref('');
const newPassword = ref('');
const newPasswordConfirm = ref('');
const loading = ref(false);
const success = ref(false);

const errors = ref({
    current: '',
    new: '',
    confirm: '',
});

function validate() {
    errors.value = { current: '', new: '', confirm: '' };
    let valid = true;

    if (!currentPassword.value) {
        errors.value.current = 'Prosím zadajte aktuálne heslo';
        valid = false;
    }
    if (!newPassword.value || newPassword.value.length < 8) {
        errors.value.new = 'Heslo musí mať aspoň 8 znakov';
        valid = false;
    }
    if (newPasswordConfirm.value !== newPassword.value) {
        errors.value.confirm = 'Napísané heslá sa nezhodujú';
        valid = false;
    }

    return valid;
}

async function submitChange() {
  if (!validate()) return;

  loading.value = true;
  success.value = false;

  // reset backend errors
  errors.value.current = '';

  try {
    await changePassword({
      currentPassword: currentPassword.value,
      newPassword: newPassword.value,
      newPasswordConfirmation: newPasswordConfirm.value,
    });

    success.value = true;
    currentPassword.value = '';
    newPassword.value = '';
    newPasswordConfirm.value = '';
  } catch (e) {
    const response = e?.response;

    // backend: wrong current password
    if (response?.status === 422 && response.data?.message) {
      errors.value.current = response.data.message;
    } else {
      errors.value.current = 'Nastala chyba pri zmene hesla.';
    }
  } finally {
    loading.value = false;
  }
}


// ---------------- GARANT FACULTIES ----------------
const faculties = ref([]);
const selectedFacultyIds = ref([]);
const facultiesLoading = ref(false);
const facultiesError = ref('');
const facultiesSuccess = ref(false);

onMounted(async () => {
    if (role.value !== ROLE_GARANT) return;

    try {
        faculties.value = await fetchMyFaculties();
        selectedFacultyIds.value = faculties.value
            .filter(f => f.selected)
            .map(f => f.id);
    } catch {
        facultiesError.value = 'Nepodarilo sa načítať odbory.';
    }
});

async function saveFaculties() {
    facultiesLoading.value = true;
    facultiesError.value = '';
    facultiesSuccess.value = false;

    try {
        await saveMyFaculties(selectedFacultyIds.value);
        await fetchAndStoreUser(); // CRITICAL – keep Pinia in sync
        facultiesSuccess.value = true;
    } catch {
        facultiesError.value = 'Nepodarilo sa uložiť odbory.';
    } finally {
        facultiesLoading.value = false;
    }
}
</script>
