<template>
    <section class="new-prax-success">
        <header class="new-prax-success__header">
            <h2 class="new-prax-success__title">
                Pridať odborný prax
            </h2>

            <!-- rovnaká čiara ako pri ostatných sekciách -->
            <div class="student-prax-list__divider new-prax-success__divider"></div>
        </header>

        <div class="new-prax-success__content">
            <p class="new-prax-success__text">
                Úspešne ste vytvorili záznam o odbornej praxe, čakajte na potvrdenie od firmy.
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
// žiadna logika, len emituje späť na zoznam
const emit = defineEmits(['back-to-list']);
</script>
