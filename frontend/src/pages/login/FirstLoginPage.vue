<template>
    <div class="page page--first-login">
        <AppHeader />

        <main class="first-login">
            <section class="first-login__inner container">
                <FirstLoginForm />
            </section>
        </main>

        <Footer />
    </div>
</template>

<script setup>
import AppHeader from "../../components/Navbar/Navbar.vue";
import Footer from "../../components/Footer/Footer.vue";
import FirstLoginForm from "../../components/Login/FirstLoginForm.vue";
</script>
