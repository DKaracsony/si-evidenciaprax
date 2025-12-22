<template>
    <div class="forgot-page-wrapper">
        <!-- HEADER -->
        <AppHeader />

        <!-- MAIN CONTENT -->
        <main class="forgot-page-first">
            <section class="forgot-page-first__inner container">
                <ForgotPasswordFirst />
            </section>
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
