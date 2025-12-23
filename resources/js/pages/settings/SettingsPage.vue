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
                    </ul>
                </nav>
            </aside>

            <!-- MAIN CONTENT -->
            <section class="settings-content">
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
                            placeholder="Napíšte aktuálne heslo"
                        />
                        <p v-if="errors.current" class="error">
                            {{ errors.current }}
                        </p>

                        <label class="label">Nové heslo</label>
                        <input
                            type="password"
                            v-model="newPassword"
                            class="input"
                            placeholder="Napíšte nové heslo"
                        />
                        <p v-if="errors.new" class="error">
                            {{ errors.new }}
                        </p>

                        <label class="label">Potvrďte nové heslo</label>
                        <input
                            type="password"
                            v-model="newPasswordConfirm"
                            class="input"
                            placeholder="Potvrďte nové heslo"
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
            </section>
        </main>

        <Footer />
    </div>
</template>



<script setup>
import { ref } from 'vue';
import AppHeader from '../../components/Navbar/Navbar.vue';
import Footer from '../../components/Footer/Footer.vue';
import { changePassword } from '../../services/auth.js';

// sidebar state
const isCollapsed = ref(false);
const activeSection = ref('general');

// form fields
const currentPassword = ref('');
const newPassword = ref('');
const newPasswordConfirm = ref('');

// flags
const loading = ref(false);
const success = ref(false);

// error messages
const errors = ref({
    current: '',
    new: '',
    confirm: '',
});

// FE validation
function validate() {
    errors.value = { current: '', new: '', confirm: '' };
    let valid = true;

    if (!currentPassword.value) {
        errors.value.current = 'Prosím zadajte aktuálne heslo';
        valid = false;
    }
    if (!newPassword.value) {
        errors.value.new = 'Napíšte prosím nové heslo';
        valid = false;
    } else if (newPassword.value.length < 8) {
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
    } catch (err) {
        if (err.response?.status === 422) {
            const msg = err.response.data.message || '';

            if (msg.includes('nesprávne')) {
                errors.value.current =
                    'Heslo, ktoré ste zadali, nie je správne.';
            } else {
                errors.value.new = 'Neplatné vstupy.';
            }
        } else {
            alert('Nepodarilo sa zmeniť heslo, skúste znova.');
        }
    } finally {
        loading.value = false;
    }
}
</script>
