<template>
    <div class="forgot-page">
        <!-- HEADER -->
        <AppHeader />

        <!-- MAIN CONTENT -->
        <main class="forgot-page__main">
            <div class="container forgot-page__content">
                <ForgotPasswordFirst />
            </div>
        </main>

        <!-- FOOTER -->
        <Footer />
    </div>
</template>

<script setup>
import AppHeader from "../../components/Navbar/Navbar.vue";
import Footer from "../../components/Footer/Footer.vue";
import ForgotPasswordFirst from '../../components/PasswordReset/ForgotPasswordFirst.vue';
</script>
