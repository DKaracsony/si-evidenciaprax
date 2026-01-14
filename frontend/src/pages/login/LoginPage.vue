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
import AppHeader from '../../components/Navbar/Navbar.vue';
import Footer from '../../components/Footer/Footer.vue';
import LoginForm from '../../components/Login/LoginForm.vue';

export default {
    name: 'LoginPage',
    components: {
        LandingHeader: AppHeader,
        LandingFooter: Footer,
        LoginForm,
    },
};
</script>
