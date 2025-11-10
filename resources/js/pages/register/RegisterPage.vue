<template>
    <div class="register-layout">
        <LandingHeader />

        <main class="register-main">
            <div class="register-inner">
                <RegisterForm />
            </div>
        </main>

        <LandingFooter />
    </div>
</template>

<script setup>
import LandingHeader from '../../components/LandingHeader.vue'
import LandingFooter from '../../components/LandingFooter.vue'
import RegisterForm from '../../components/Register/RegisterForm.vue'
</script>
