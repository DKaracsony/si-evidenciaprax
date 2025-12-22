<template>
    <div class="forgot-page-second-wrapper">
        <!-- HEADER -->
        <AppHeader />

        <!-- MAIN CONTENT -->
        <main class="forgot-page-second">
            <section class="forgot-page-second__inner container">
                <ForgotPasswordSecond />
            </section>
        </main>

        <!-- FOOTER -->
        <Footer />
    </div>
</template>

<script setup>
import AppHeader from "../../components/Navbar/Navbar.vue";
import Footer from "../../components/Footer/Footer.vue";
import ForgotPasswordSecond from "../../components/PasswordReset/ForgotPasswordSecond.vue";
</script>
