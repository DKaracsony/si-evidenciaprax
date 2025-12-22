<template>
    <article class="forgot-second-card">
        <header class="forgot-second-card__header">
            <h1 id="reset-password-title" class="forgot-second-card__title">
                Nastavenie nového hesla
            </h1>
            <div class="forgot-second-card__divider" aria-hidden="true"></div>
            <p class="forgot-second-card__subtitle">
                Zadajte nové heslo pre svoj účet. Odkaz z e-mailu je platný
                len obmedzený čas. Po úspešnom nastavení hesla budete
                presmerovaní na stránku prihlásenia.
            </p>
        </header>

        <!-- Neplatný link – chýba token/email v URL -->
        <section
            v-if="!hasValidLink"
            class="forgot-second-card__invalid-link"
            aria-live="assertive"
        >
            <p class="forgot-second-card__form-message forgot-second-card__form-message--error">
                Odkaz na obnovenie hesla je neplatný alebo neúplný.
            </p>
            <div class="forgot-second-card__actions forgot-second-card__actions--center">
                <router-link :to="{ name: 'ForgotPasswordPageFirst' }" class="btn-secondary">
                    Požiadať znova o obnovenie hesla
                </router-link>
                <router-link :to="{ name: 'LoginPage' }" class="btn-primary">
                    Prejsť na prihlásenie
                </router-link>
            </div>
        </section>

        <!-- Platný link – zobraz formulár -->
        <form
            v-else
            class="forgot-second-card__form"
            @submit.prevent="handleSubmit"
            novalidate
            aria-labelledby="reset-password-title"
        >
            <div class="forgot-second-card__field-group">
                <label class="forgot-second-card__field">
                    <span class="forgot-second-card__label">Nové heslo</span>
                    <input
                        v-model="password"
                        type="password"
                        name="password"
                        autocomplete="new-password"
                        class="forgot-second-card__input"
                        :class="{ 'is-invalid': passwordError }"
                        @blur="touchedPassword = true"
                    />
                </label>
                <p v-if="passwordError" class="forgot-second-card__error">
                    {{ passwordError }}
                </p>
            </div>

            <div class="forgot-second-card__field-group">
                <label class="forgot-second-card__field">
                    <span class="forgot-second-card__label">
                        Potvrdenie nového hesla
                    </span>
                    <input
                        v-model="passwordConfirmation"
                        type="password"
                        name="password_confirmation"
                        autocomplete="new-password"
                        class="forgot-second-card__input"
                        :class="{ 'is-invalid': passwordConfirmationError }"
                        @blur="touchedPasswordConfirmation = true"
                    />
                </label>
                <p
                    v-if="passwordConfirmationError"
                    class="forgot-second-card__error"
                >
                    {{ passwordConfirmationError }}
                </p>
            </div>

            <!-- form-level feedback -->
            <p
                v-if="formError"
                class="forgot-second-card__form-message forgot-second-card__form-message--error"
            >
                {{ formError }}
            </p>
            <p
                v-if="formSuccess"
                class="forgot-second-card__form-message forgot-second-card__form-message--success"
            >
                {{ formSuccess }}
            </p>

            <div class="forgot-second-card__actions">
                <button
                    type="submit"
                    class="forgot-second-card__btn"
                    :disabled="isSubmitting || !canSubmit"
                >
                    {{ isSubmitting ? 'Ukladám nové heslo…' : 'Nastaviť nové heslo' }}
                </button>
            </div>
        </form>
    </article>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';

const route = useRoute();
const router = useRouter();

// query params z URL
const token = ref('');
const email = ref('');

// stav linku
const hasValidLink = ref(true);

// heslá
const password = ref('');
const passwordConfirmation = ref('');

const touchedPassword = ref(false);
const touchedPasswordConfirmation = ref(false);

const isSubmitting = ref(false);
const formError = ref('');
const formSuccess = ref('');

// načítanie query parametrov pri mountnutí komponentu
onMounted(() => {
    const qToken = route.query.token;
    const qEmail = route.query.email;

    if (typeof qToken === 'string') token.value = qToken;
    if (typeof qEmail === 'string') email.value = qEmail;

    if (!token.value || !email.value) {
        hasValidLink.value = false;
    }
});

// VALIDÁCIE
const passwordError = computed(() => {
    if (!touchedPassword.value && !isSubmitting.value) return '';

    if (!password.value) {
        return 'Zadajte nové heslo.';
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
        return 'Heslá sa musia zhodovať.';
    }

    return '';
});

const canSubmit = computed(() => {
    return (
        hasValidLink.value &&
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
        const { data } = await axios.post('/api/password/reset', {
            email: email.value,
            token: token.value,
            password: password.value,
            password_confirmation: passwordConfirmation.value,
        });

        const backendMessage =
            data?.message ?? 'Heslo bolo zmenené. Môžete sa prihlásiť novým heslom.';

        formSuccess.value = backendMessage;

        // jednoduchý "toast" + redirect na login
        alert(backendMessage);
        await router.push({ name: 'LoginPage' });
    } catch (error) {
        const status = error.response?.status;

        if (status === 422) {
            // validácia z backendu – napr. prázdne/krátke heslo (fallback)
            formError.value =
                'Neplatné údaje. Skontrolujte, či heslo spĺňa požiadavky.';
        } else {
            // token expirovaný, neplatný, alebo iný problém – backend vracia generickú odpoveď,
            // takže tu len zobrazíme všeobecnú chybu
            formError.value =
                'Nepodarilo sa nastaviť nové heslo. Skúste to prosím neskôr alebo požiadajte o nový odkaz.';
        }
    } finally {
        isSubmitting.value = false;
    }
}
</script>
