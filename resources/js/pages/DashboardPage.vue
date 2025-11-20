<template>
    <div class="dashboard-page">
        <!-- Top navigation -->
        <LandingHeader />

        <main class="dashboard-page__main">
            <div class="container">
                <section
                    class="dashboard-page__inner"
                    aria-labelledby="dashboard-title"
                >
                    <header class="dashboard-page__header">
                        <p class="dashboard-page__eyebrow">
                            Prehľad
                        </p>
                        <h1 id="dashboard-title" class="dashboard-page__title">
                            Dashboard
                        </h1>
                        <p class="dashboard-page__subtitle">
                            Vitaj v systéme. Toto je úvodná obrazovka po prihlásení. Obsah dashboardu doplníme v ďalších
                            epikoch (prehľad praxí, notifikácie a podobne).
                        </p>
                    </header>

                    <section class="dashboard-page__content">
                        <article class="dashboard-empty-card">
                            <h2 class="dashboard-empty-card__title">
                                Zatiaľ tu nič nie je
                            </h2>
                            <p class="dashboard-empty-card__text">
                                Dashboard je momentálne prázdny. Neskôr tu uvidíš dôležité informácie o svojich praxiach a
                                stave procesu.
                            </p>
                        </article>
                    </section>
                </section>
            </div>
        </main>

        <!-- Footer -->
        <LandingFooter />
    </div>
</template>

<script setup>
import LandingHeader from '../components/LandingHeader.vue';
import LandingFooter from '../components/LandingFooter.vue';

// Logika dashboardu (fetch profil, štatistiky, atď.) príde v ďalších epikoch.
// Teraz je to len prázdna landing stránka po prihlásení.
</script>
