<template>
    <div class="forgot-page-second-wrapper">
        <!-- HEADER -->
        <LandingHeader />

        <!-- MAIN CONTENT -->
        <main class="forgot-page-second">
            <section class="forgot-page-second__inner container">
                <ForgotPasswordSecond />
            </section>
        </main>

        <!-- FOOTER -->
        <LandingFooter />
    </div>
</template>

<script setup>
import LandingHeader from "../../components/LandingHeader.vue";
import LandingFooter from "../../components/LandingFooter.vue";
import ForgotPasswordSecond from "../../components/PasswordReset/ForgotPasswordSecond.vue";
</script>
