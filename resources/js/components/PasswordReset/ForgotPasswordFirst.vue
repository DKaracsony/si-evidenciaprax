<template>
    <article class="forgot-card">
        <header class="forgot-card__header">
            <h1 id="forgot-password-title" class="forgot-card__title">
                Obnova zabudnutého hesla
            </h1>
            <div class="forgot-card__divider" aria-hidden="true"></div>
            <p class="forgot-card__subtitle">
                Na obnovu vášho zabudnutého hesla prosím zadajte vašu
                e-mailovú adresu. Pošleme vám mail s inštrukciami
                na resetovanie hesla.
            </p>
        </header>

        <form
            class="forgot-card__form"
            @submit.prevent="handleSubmit"
            novalidate
            aria-labelledby="forgot-password-title"
        >
            <div class="forgot-card__field">
                <label class="forgot-card__label">
                    Váš email
                </label>
                <input
                    v-model.trim="email"
                    type="email"
                    name="email"
                    autocomplete="email"
                    class="forgot-card__input"
                    :class="{ 'is-invalid': emailError }"
                    @blur="touchEmail"
                />
                <p v-if="emailError" class="forgot-card__error">
                    {{ emailError }}
                </p>
            </div>

            <!-- form-level feedback (generic, no account existence leak) -->
            <p
                v-if="formSuccess"
                class="forgot-card__form-message forgot-card__form-message--success"
            >
                {{ formSuccess }}
            </p>
            <p
                v-if="formError"
                class="forgot-card__form-message forgot-card__form-message--error"
            >
                {{ formError }}
            </p>

            <div class="forgot-card__actions">
                <button
                    type="submit"
                    class="lp-first__btn-register forgot-card__btn"
                    :disabled="isSubmitting || !canSubmit"
                >
                    {{ isSubmitting ? 'Skontrolujem…' : 'Pokračovať' }}
                </button>
            </div>
        </form>
    </article>
</template>

<script setup>
import { ref, computed } from 'vue';
import axios from 'axios';

const email = ref('');
const touchedEmail = ref(false);

const isSubmitting = ref(false);
const formSuccess = ref('');
const formError = ref('');

const emailError = computed(() => {
    if (!touchedEmail.value && !isSubmitting.value) return '';

    if (!email.value) {
        return 'Zadajte prosím e-mailovú adresu.';
    }

    const pattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!pattern.test(email.value)) {
        return 'Zadajte platný e-mail.';
    }

    return '';
});

const canSubmit = computed(() => {
    return !!email.value && !emailError.value;
});

function touchEmail() {
    touchedEmail.value = true;
}

async function handleSubmit() {
    touchedEmail.value = true;
    formSuccess.value = '';
    formError.value = '';

    if (!canSubmit.value) return;

    isSubmitting.value = true;

    try {
        const response = await axios.post('/api/password/forgot', {
            email: email.value,
        });

        formSuccess.value =
            response?.data?.message ??
            'Ak existuje účet, poslali sme e-mail s ďalším postupom.';
    } catch (e) {
        formError.value =
            'Nepodarilo sa odoslať požiadavku. Skúste to prosím neskôr.';
    } finally {
        isSubmitting.value = false;
    }
}
</script>
