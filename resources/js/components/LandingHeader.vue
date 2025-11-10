<template>
    <!--
      Landing Header
      - Minimal, sticky nav bar with centered logo on wide viewports.
      - On smaller viewports the logo shifts left (see SCSS for breakpoint).
      - Keep markup lean; layout is entirely controlled via lp-nav__wrap grid.
    -->
    <header class="lp-nav" aria-label="Primárna navigácia">
        <div class="container lp-nav__wrap">
            <!-- Left spacer column (balances the centered logo in the grid). -->
            <div aria-hidden="true"></div>

            <!-- Logo links home -->
            <RouterLink to="/" class="lp-nav__logo" aria-label="Domov">
                <img :src="logoUrl" alt="PraxSI logo" />
            </RouterLink>

            <!-- Right-side group: Úvod (link) + Prihlásenie (button) -->
            <div class="lp-nav__right">
                <RouterLink to="/" class="lp-nav__link">Úvod</RouterLink>
                <a href="/login" class="btn-primary lp-nav__btn">Prihlásenie</a>
            </div>
        </div>
    </header>
</template>

<script setup>
// Header logo path (served from public/storage). Using a plain string keeps it out of Vite’s import graph.
const logoUrl = '/storage/lp-nav-1.png'
</script>
