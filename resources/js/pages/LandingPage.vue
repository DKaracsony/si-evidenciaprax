<template>
    <LandingHeader />

    <main>
        <LandingFirst />
        <LandingSecond />
    </main>


    <LandingFooter />
</template>

<script setup>
import LandingHeader from '../components/LandingHeader.vue'
import LandingFirst from "../components/LandingFirst.vue";
import LandingSecond from '../components/LandingSecond.vue'
import LandingFooter from '../components/LandingFooter.vue'
</script>
