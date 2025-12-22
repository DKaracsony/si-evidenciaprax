<template>
    <AppHeader />

    <main class="sent">
        <div class="sent__container">
            <article class="sent__card" :data-variant="resolvedType">
                <header class="sent__header">
                    <h1 class="sent__title">
                        Úspešná registrácia {{ resolvedType === 'student' ? 'študenta' : 'firmy' }}
                    </h1>
                    <div class="sent__divider" role="presentation"></div>
                </header>

                <p v-if="resolvedType === 'student'" class="sent__text">
                    Vaše dočasné heslo sme vám poslali na váš študentský e-mail. Po prihláseni prosím používajte svoj osobný mail.
                    Po prvom prihlásení budete vyzvaní na zmenu hesla!
                </p>

                <p v-else class="sent__text">
                    E-mail s odkazom na aktiváciu vášho účtu sme vám poslali do vašej e-mailovej schránky.
                    Riaďte sa inštrukciami v e-maile a následne sa prihláste do systému.
                    Po prvom prihlásení budete vyzvaní na zmenu hesla!
                </p>

                <div class="sent__actions" role="group" aria-label="Akcie po registrácii">
                    <RouterLink to="/login" class="sent__btn-primary">
                        Prihlásenie
                    </RouterLink>
                </div>
            </article>
        </div>
    </main>

    <Footer />
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useRoute } from 'vue-router'

import AppHeader from '../../components/Navbar/Navbar.vue'
import Footer from '../../components/Footer/Footer.vue'

const route = useRoute()

const resolvedType = computed<'student' | 'company'>(() => {
    const t = String(route.query.type || '').toLowerCase()
    return t === 'company' ? 'company' : 'student'
})
</script>
