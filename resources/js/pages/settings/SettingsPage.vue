<template>
    <div class="page page--settings">
        <LandingHeader />

        <main class="settings-layout">

            <!-- SIDEBAR -->
            <aside
                class="settings-sidebar"
                :class="{ collapsed: isCollapsed }"
            >
                <!-- Collapse / Expand button -->
                <button
                    class="sidebar-toggle"
                    @click="isCollapsed = !isCollapsed"
                >
                    <span v-if="!isCollapsed">«</span>
                    <span v-else>»</span>
                </button>

                <!-- Sidebar content (only visible when open) -->
                <nav v-if="!isCollapsed" class="sidebar-menu">
                    <p class="menu-title">Nastavenia</p>

                    <ul>
                        <li
                            :class="{ active: activeSection === 'general' }"
                            @click="activeSection = 'general'"
                        >
                            Zmena hesla účtu
                        </li>
                        <li class="placeholder">Jazyk</li>
                        <li class="placeholder">Zobrazenie</li>
                        <li class="placeholder">Účet</li>
                    </ul>
                </nav>
            </aside>

            <!-- MAIN CONTENT -->
            <section class="settings-content">
                <!-- DEFAULT SECTION -->
                <div v-if="activeSection === 'general'" class="password-section">

                    <h1 class="settings-title">Zmeniť heslo účtu</h1>

                    <form class="password-form" @submit.prevent="submitChange">
                        <!-- current password -->
                        <label class="label">Aktuálne heslo</label>
                        <input
                            type="password"
                            v-model="currentPassword"
                            class="input"
                            placeholder="Napíšte aktuálne heslo"
                        />
                        <p v-if="errors.current" class="error">{{ errors.current }}</p>

                        <!-- new password -->
                        <label class="label">Nové heslo</label>
                        <input
                            type="password"
                            v-model="newPassword"
                            class="input"
                            placeholder="Napíšte nové heslo"
                        />
                        <p v-if="errors.new" class="error">{{ errors.new }}</p>

                        <!-- confirm new password -->
                        <label class="label">Potvrďte nové heslo</label>
                        <input
                            type="password"
                            v-model="newPasswordConfirm"
                            class="input"
                            placeholder="Potvrďte nové heslo"
                        />
                        <p v-if="errors.confirm" class="error">{{ errors.confirm }}</p>

                        <!-- success message -->
                        <p v-if="success" class="success">Vaše heslo bolo úspešne zmenené</p>

                        <!-- submit button -->
                        <button
                            type="submit"
                            class="btn-primary submit-btn"
                            :disabled="loading"
                        >
                            {{ loading ? "Nastavujem..." : "Zmeniť heslo" }}
                        </button>
                    </form>
                </div>
            </section>

        </main>

        <LandingFooter />
    </div>
</template>

<script setup>
import { ref } from "vue";
import axios from "axios";
import LandingHeader from "../../components/LandingHeader.vue";
import LandingFooter from "../../components/LandingFooter.vue";

// sidebar state
const isCollapsed = ref(false);

// active section in the menu
const activeSection = ref("general");

// form fields
const currentPassword = ref("");
const newPassword = ref("");
const newPasswordConfirm = ref("");

// flags
const loading = ref(false);
const success = ref(false);

// error messages
const errors = ref({
    current: null,
    new: null,
    confirm: null,
});

// FE validation
function validate() {
    errors.value = { current: null, new: null, confirm: null };
    let valid = true;

    if (!currentPassword.value) {
        errors.value.current = "Prosím zadajte aktuálne heslo";
        valid = false;
    }
    if (!newPassword.value) {
        errors.value.new = "Napíšte prosím nové heslo";
        valid = false;
    }
    if (newPassword.value.length < 8) {
        errors.value.new = "Heslo musí mať aspoň 8 znakov";
        valid = false;
    }
    if (newPasswordConfirm.value !== newPassword.value) {
        errors.value.confirm = "Napísané heslá sa nezhodujú";
        valid = false;
    }

    return valid;
}

async function submitChange() {
    if (!validate()) return;

    loading.value = true;
    success.value = false;

    try {
        const payload = {
            current_password: currentPassword.value,
            new_password: newPassword.value,
            new_password_confirmation: newPasswordConfirm.value,
        };

        await axios.patch("/api/account/password", payload);

        // success UI
        success.value = true;

        // reset fields
        currentPassword.value = "";
        newPassword.value = "";
        newPasswordConfirm.value = "";
    } catch (err) {
        if (err.response?.status === 422) {
            const msg = err.response.data.message;

            if (msg.includes("nesprávne")) {
                errors.value.current = "Heslo čo ste napísali není správne";
            } else {
                errors.value.new = "Neplatné vstupy";
            }
        } else {
            alert("Nepodarilo sa zmeniť heslo, skúste znova.");
        }
    } finally {
        loading.value = false;
    }
}
</script>
