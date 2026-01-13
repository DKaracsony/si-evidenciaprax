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

        <!-- INVALID LINK -->
        <section
            v-if="!hasValidLink"
            class="forgot-second-card__invalid-link"
            aria-live="assertive"
        >
            <p class="forgot-second-card__form-message forgot-second-card__form-message--error">
                Odkaz na obnovenie hesla je neplatný alebo neúplný.
            </p>

            <div class="forgot-second-card__actions forgot-second-card__actions--center">
                <router-link
                    :to="{ name: 'ForgotPasswordPageFirst' }"
                    class="lp-first__btn-register"
                >
                    Požiadať znova
                </router-link>

                <router-link
                    :to="{ name: 'LoginPage' }"
                    class="lp-first__btn-register"
                >
                    Prihlásenie
                </router-link>
            </div>
        </section>

        <!-- VALID LINK -->
        <form
            v-else
            class="forgot-second-card__form"
            @submit.prevent="handleSubmit"
            novalidate
            aria-labelledby="reset-password-title"
        >
            <div class="forgot-second-card__field">
                <label class="forgot-second-card__label">
                    Nové heslo
                </label>
                <input
                    v-model="password"
                    type="password"
                    autocomplete="new-password"
                    class="forgot-second-card__input"
                    :class="{ 'forgot-second-card__input--error': passwordError }"
                    @blur="touchedPassword = true"
                />
                <p v-if="passwordError" class="forgot-second-card__error">
                    {{ passwordError }}
                </p>
            </div>

            <div class="forgot-second-card__field">
                <label class="forgot-second-card__label">
                    Potvrdenie nového hesla
                </label>
                <input
                    v-model="passwordConfirmation"
                    type="password"
                    autocomplete="new-password"
                    class="forgot-second-card__input"
                    :class="{ 'forgot-second-card__input--error': passwordConfirmationError }"
                    @blur="touchedPasswordConfirmation = true"
                />
                <p
                    v-if="passwordConfirmationError"
                    class="forgot-second-card__error"
                >
                    {{ passwordConfirmationError }}
                </p>
            </div>

            <!-- FORM FEEDBACK -->
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
                    class="lp-first__btn-register forgot-second-card__submit"
                    :disabled="isSubmitting || !canSubmit"
                >
                    {{ isSubmitting ? 'Ukladám…' : 'Nastaviť nové heslo' }}
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

const token = ref('');
const email = ref('');
const hasValidLink = ref(true);

const password = ref('');
const passwordConfirmation = ref('');

const touchedPassword = ref(false);
const touchedPasswordConfirmation = ref(false);

const isSubmitting = ref(false);
const formError = ref('');
const formSuccess = ref('');

onMounted(() => {
    if (typeof route.query.token === 'string') token.value = route.query.token;
    if (typeof route.query.email === 'string') email.value = route.query.email;

    if (!token.value || !email.value) {
        hasValidLink.value = false;
    }
});

const passwordError = computed(() => {
    if (!touchedPassword.value && !isSubmitting.value) return '';
    if (!password.value) return 'Zadajte nové heslo.';
    if (password.value.length < 8) return 'Heslo musí mať aspoň 8 znakov.';
    return '';
});

const passwordConfirmationError = computed(() => {
    if (!touchedPasswordConfirmation.value && !isSubmitting.value) return '';
    if (!passwordConfirmation.value) return 'Zopakujte nové heslo.';
    if (passwordConfirmation.value !== password.value) return 'Heslá sa musia zhodovať.';
    return '';
});

const canSubmit = computed(() =>
    hasValidLink.value &&
    !passwordError.value &&
    !passwordConfirmationError.value &&
    password.value &&
    passwordConfirmation.value
);

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

        const msg = data?.message ?? 'Heslo bolo zmenené.';
        formSuccess.value = msg;
        alert(msg);
        await router.push({ name: 'LoginPage' });
    } catch {
        formError.value =
            'Nepodarilo sa nastaviť nové heslo. Skúste to znova alebo požiadajte o nový odkaz.';
    } finally {
        isSubmitting.value = false;
    }
}
</script>
