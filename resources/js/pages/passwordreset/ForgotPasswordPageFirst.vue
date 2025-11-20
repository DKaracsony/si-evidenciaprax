<template>
    <div class="forgot-page-wrapper">
        <!-- HEADER -->
        <LandingHeader />

        <!-- MAIN CONTENT -->
        <main class="forgot-page-first">
            <section class="forgot-page-first__inner container">
                <ForgotPasswordFirst />
            </section>
        </main>

        <!-- FOOTER -->
        <LandingFooter />
    </div>
</template>

<script setup>
import LandingHeader from "../../components/LandingHeader.vue";
import LandingFooter from "../../components/LandingFooter.vue";
import ForgotPasswordFirst from '../../components/PasswordReset/ForgotPasswordFirst.vue';
</script>
