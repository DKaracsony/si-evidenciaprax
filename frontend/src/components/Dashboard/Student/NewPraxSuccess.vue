<!-- resources/js/components/Dashboard/Student/NewPraxSuccess.vue -->
<template>
    <section class="new-prax-success">
        <header class="new-prax-success__header">
            <h2 class="new-prax-success__title">
                Nová odborná prax
            </h2>

            <div class="new-prax-success__divider"></div>
        </header>

        <div class="new-prax-success__content">
            <p class="new-prax-success__text">
                Úspešne ste vytvorili záznam o odbornej praxi, čakajte na potvrdenie od firmy.
            </p>

            <button
                type="button"
                class="new-prax-success__button"
                @click="$emit('back-to-list')"
            >
                Späť na zoznam
            </button>
        </div>
    </section>
</template>

<script setup>
</script>
