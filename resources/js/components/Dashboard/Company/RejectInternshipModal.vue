<template>
    <div class="reject-modal-overlay">
        <div class="login-card reject-modal">
            <h2 class="login-form__title">
                Odôvodnenie odmietnutia praxe
            </h2>

            <div class="login-form__divider"></div>

            <div class="login-form__body">
                <div class="login-form__field">
                    <textarea
                        v-model.trim="note"
                        class="login-form__input reject-modal__textarea"
                        :class="{ 'login-form__input--error': showError }"
                        :disabled="loading"
                        rows="5"
                        placeholder="Zadajte dôvod odmietnutia praxe..."
                    ></textarea>
                </div>

                <div class="login-form__actions">
                    <button
                        class="student-prax-item__cta-button student-prax-item__cta-button--reject"
                        :disabled="loading"
                        @click="submit"
                    >
                        <span v-if="loading">Odosielam…</span>
                        <span v-else>Zamietnuť a odoslať správu</span>
                    </button>

                    <button
                        class="student-prax-item__cta-button"
                        :disabled="loading"
                        @click="$emit('close')"
                    >
                        Zrušiť
                    </button>
                </div>

                <p
                    v-if="showError"
                    class="login-form__error reject-modal__error"
                >
                    Vyplňte prosím požadované pole
                </p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
    loading: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['submit', 'close']);

const note = ref('');
const touched = ref(false);

const showError = computed(() => touched.value && !note.value);

function submit() {
    touched.value = true;
    if (!note.value || props.loading) return;
    emit('submit', note.value);
}
</script>
