<template>
    <div class="register-layout">
        <AppHeader />

        <main class="register-main">
            <div class="register-inner">
                <RegisterForm />
            </div>
        </main>

        <Footer />
    </div>
</template>

<script setup>
import AppHeader from '../../components/Navbar/Navbar.vue'
import Footer from '../../components/Footer/Footer.vue'
import RegisterForm from '../../components/Register/RegisterForm.vue'
</script>
