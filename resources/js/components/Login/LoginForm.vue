<template>
    <section class="login-card">
        <div class="login-form">
            <h1 class="login-form__title">Prihlásenie</h1>
            <div class="login-form__divider"></div>

            <form
                class="login-form__body"
                @submit.prevent="onSubmit"
                novalidate
            >
                <!-- Email -->
                <div class="login-form__field">
                    <label for="login-email" class="login-form__label">
                        Email
                    </label>
                    <input
                        id="login-email"
                        type="email"
                        class="login-form__input"
                        :class="{ 'login-form__input--error': fieldErrors.email }"
                        v-model.trim="form.email"
                        autocomplete="email"
                        placeholder="Email"
                    />
                    <p
                        v-if="fieldErrors.email"
                        class="login-form__error"
                    >
                        {{ fieldErrors.email }}
                    </p>
                </div>

                <!-- Password -->
                <div class="login-form__field">
                    <label for="login-password" class="login-form__label">
                        Heslo
                    </label>
                    <input
                        id="login-password"
                        type="password"
                        class="login-form__input"
                        :class="{ 'login-form__input--error': fieldErrors.password }"
                        v-model.trim="form.password"
                        autocomplete="current-password"
                        placeholder="Heslo"
                    />
                    <p
                        v-if="fieldErrors.password"
                        class="login-form__error"
                    >
                        {{ fieldErrors.password }}
                    </p>
                </div>

                <!-- System / backend response message (above button) -->
                <p
                    v-if="systemError"
                    class="login-form__system-error"
                >
                    {{ systemError }}
                </p>

                <!-- Actions -->
                <div class="login-form__actions">
                    <button
                        type="submit"
                        class="lp-first__btn-register login-form__submit"
                        :class="{ 'login-form__submit--loading': isSubmitting }"
                        :disabled="isSubmitDisabled"
                    >
                        {{ isSubmitting ? 'Skontrolujem...' : 'Prihlásenie' }}
                    </button>
                </div>

                <!-- Helper text + forgot password -->
                <p class="login-form__helper">
                    <router-link
                        to="/password/forgot"
                        class="login-form__forgot"
                    >
                        Zabudli ste heslo?
                    </router-link>
                </p>
            </form>
        </div>
    </section>
</template>

<script>
import { login } from '@/services/auth.js';

export default {
    name: 'LoginForm',
    data() {
        return {
            form: {
                email: '',
                password: '',
            },
            fieldErrors: {
                email: '',
                password: '',
            },
            systemError: '',
            isSubmitting: false,
        };
    },
    computed: {
        isSubmitDisabled() {
            return (
                this.isSubmitting ||
                !this.form.email ||
                !this.form.password
            );
        },
    },
    methods: {
        resetErrors() {
            this.fieldErrors.email = '';
            this.fieldErrors.password = '';
            this.systemError = '';
        },
        isEmailValid(value) {
            const pattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return pattern.test(value);
        },
        validate() {
            let valid = true;

            if (!this.form.email) {
                this.fieldErrors.email = 'Zadajte email.';
                valid = false;
            } else if (!this.isEmailValid(this.form.email)) {
                this.fieldErrors.email = 'Zadali ste nesprávny mail!';
                valid = false;
            }

            if (!this.form.password) {
                this.fieldErrors.password = 'Zadajte heslo.';
                valid = false;
            }

            return valid;
        },
        async onSubmit() {
            this.resetErrors();

            if (!this.validate()) return;

            this.isSubmitting = true;

            try {
                const profile = await login({
                    email: this.form.email,
                    password: this.form.password,
                });

                // 1) Ak si musí povinne zmeniť heslo, pošli ho na onboarding screen
                if (profile && profile['password_reset_needed']) {
                    this.$router.push({ name: 'FirstLoginPage' });
                    return;
                }

                // 2) Inak ho pusť na redirect alebo dashboard
                const redirect = this.$route.query.redirect;
                if (redirect) {
                    this.$router.push(redirect);
                } else {
                    this.$router.push({ name: 'DashboardPage' });
                }
            } catch (error) {
                if (
                    error.response &&
                    (error.response.status === 400 ||
                        error.response.status === 401)
                ) {
                    this.systemError =
                        'Prihlásenie zlyhalo. Skontrolujte email a heslo.';
                    this.fieldErrors.password =
                        'Zadali ste nesprávne heslo!';
                } else {
                    console.error('Login failed', error);
                    this.systemError =
                        'Prihlásenie zlyhalo. Skúste to znova neskôr.';
                }
            } finally {
                this.isSubmitting = false;
            }
        },
    },
};
</script>
