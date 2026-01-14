<template>
    <section class="first-login-card">
        <header class="first-login-card__header">
            <h1 id="first-login-title" class="first-login-card__title">
                Predtým ako pokračujete…
            </h1>
            <div class="first-login-card__divider" aria-hidden="true"></div>
            <p class="first-login-card__subtitle">
                Keďže sa prihlasujete prvýkrát do nášho systému, vyzývame vás,
                aby ste najprv zmenili svoje heslo, čo ste použili pri prihlásení.
                Tento krok je nevyhnutný, aby sme vám mohli zabezpečiť bezpečnosť
                vašich údajov.
            </p>
        </header>

        <form
            class="first-login-card__form"
            @submit.prevent="handleSubmit"
            novalidate
        >
            <!-- nové heslo -->
            <div class="first-login-card__field-group">
                <label
                    for="first-login-password"
                    class="first-login-card__label"
                >
                    Nové heslo
                </label>
                <input
                    id="first-login-password"
                    v-model="password"
                    type="password"
                    name="new_password"
                    autocomplete="new-password"
                    placeholder="Zadajte nové heslo"
                    class="first-login-card__input"
                    :class="{ 'first-login-card__input--error': passwordError }"
                    @blur="touchedPassword = true"
                />
                <p
                    v-if="passwordError"
                    class="first-login-card__error"
                >
                    {{ passwordError }}
                </p>
            </div>

            <!-- potvrdenie hesla -->
            <div class="first-login-card__field-group">
                <label
                    for="first-login-password-confirmation"
                    class="first-login-card__label"
                >
                    Potvrďte nové heslo
                </label>
                <input
                    id="first-login-password-confirmation"
                    v-model="passwordConfirmation"
                    type="password"
                    name="new_password_confirmation"
                    autocomplete="new-password"
                    placeholder="Zopakujte nové heslo"
                    class="first-login-card__input"
                    :class="{ 'first-login-card__input--error': passwordConfirmationError }"
                    @blur="touchedPasswordConfirmation = true"
                />
                <p
                    v-if="passwordConfirmationError"
                    class="first-login-card__error"
                >
                    {{ passwordConfirmationError }}
                </p>
            </div>

            <!-- form-level feedback -->
            <p
                v-if="formError"
                class="first-login-card__form-message first-login-card__form-message--error"
            >
                {{ formError }}
            </p>
            <p
                v-if="formSuccess"
                class="first-login-card__form-message first-login-card__form-message--success"
            >
                {{ formSuccess }}
            </p>

            <div class="first-login-card__actions">
                <button
                    type="submit"
                    class="lp-first__btn-register first-login-card__btn"
                    :disabled="isSubmitting || !canSubmit"
                >
                    {{ isSubmitting ? 'Skontrolujem…' : 'Pokračovať' }}
                </button>
            </div>
        </form>
    </section>
</template>


<script setup>
import { ref, computed } from 'vue';
import { useRouter } from 'vue-router';
import { fetchAndStoreUser, changePassword } from '@/services/auth.js';

const router = useRouter();

// heslá
const password = ref('');
const passwordConfirmation = ref('');

const touchedPassword = ref(false);
const touchedPasswordConfirmation = ref(false);

const isSubmitting = ref(false);
const formError = ref('');
const formSuccess = ref('');

// VALIDÁCIE (rovnaká logika ako pri resete / v nastaveniach)
const passwordError = computed(() => {
    if (!touchedPassword.value && !isSubmitting.value) return '';

    if (!password.value) {
        return 'Prosím napíšte heslo.';
    }

    if (password.value.length < 8) {
        return 'Heslo musí mať aspoň 8 znakov.';
    }

    return '';
});

const passwordConfirmationError = computed(() => {
    if (!touchedPasswordConfirmation.value && !isSubmitting.value) return '';

    if (!passwordConfirmation.value) {
        return 'Zopakujte nové heslo.';
    }

    if (passwordConfirmation.value !== password.value) {
        return 'Heslá sa nezhodujú.';
    }

    return '';
});

const canSubmit = computed(() => {
    return (
        !passwordError.value &&
        !passwordConfirmationError.value &&
        !!password.value &&
        !!passwordConfirmation.value
    );
});

async function handleSubmit() {
    touchedPassword.value = true;
    touchedPasswordConfirmation.value = true;
    formError.value = '';
    formSuccess.value = '';

    if (!canSubmit.value) return;

    isSubmitting.value = true;

    try {
        // password_reset_needed = true → backend akceptuje zmenu aj bez current_password
        const data = await changePassword({
            currentPassword: '', // neschválne neposielame current_password
            newPassword: password.value,
            newPasswordConfirmation: passwordConfirmation.value,
        });

        formSuccess.value =
            data?.message ?? 'Heslo bolo úspešne zmenené.';

        // Re-fetch user, aby sa v profile zmenilo password_reset_needed na false
        await fetchAndStoreUser();

        // Po úspechu pustíme usera do appky (napr. dashboard)
        await router.push({ name: 'DashboardPage' });
    } catch (error) {
        const status = error.response?.status;

        if (status === 422) {
            formError.value =
                'Neplatné údaje. Skontrolujte, či heslo spĺňa požiadavky.';
        } else {
            formError.value =
                'Nepodarilo sa nastaviť nové heslo. Skúste to prosím neskôr.';
        }
    } finally {
        isSubmitting.value = false;
    }
}
</script>
