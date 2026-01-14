<template>
    <AppHeader />

    <main>
        <LandingFirst />
        <LandingSecond />
    </main>


    <Footer />
</template>

<script setup>
import AppHeader from '../../components/Navbar/Navbar.vue'
import LandingFirst from "../../components/LandingPage/LandingFirst.vue";
import LandingSecond from '../../components/LandingPage/LandingSecond.vue'
import Footer from '../../components/Footer/Footer.vue'
</script>
