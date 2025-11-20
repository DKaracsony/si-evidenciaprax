<template>
    <div class="login-page">
        <LandingHeader />

        <main class="login-page__main">
            <div class="container login-page__content">
                <LoginForm />
            </div>
        </main>

        <LandingFooter />
    </div>
</template>

<script>
import LandingHeader from '../../components/LandingHeader.vue';
import LandingFooter from '../../components/LandingFooter.vue';
import LoginForm from '../../components/Login/LoginForm.vue';

export default {
    name: 'LoginPage',
    components: {
        LandingHeader,
        LandingFooter,
        LoginForm,
    },
};
</script>
