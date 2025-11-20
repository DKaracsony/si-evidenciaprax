<template>
    <div class="page page--first-login">
        <LandingHeader />

        <main class="first-login">
            <section class="first-login__inner container">
                <FirstLoginForm />
            </section>
        </main>

        <LandingFooter />
    </div>
</template>

<script setup>
import LandingHeader from "../components/LandingHeader.vue";
import LandingFooter from "../components/LandingFooter.vue";
import FirstLoginForm from "../components/FirstLoginForm.vue";
</script>
