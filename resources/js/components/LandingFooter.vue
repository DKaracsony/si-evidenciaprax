<template>
    <!--
      Section: Footer (lp-footer)
      Purpose:
      - Minimal footer with centered brand group:
        [logo] | [“Softvérove Inžinierstvo project 2025”].
      Notes:
      - Background + text colors are handled in SCSS.
    -->
    <footer class="lp-footer" aria-label="Pätička">
        <div class="container">
            <div class="lp-footer__center">
                <img :src="logoUrl" alt="PraxSI logo" class="lp-footer__logo" />
                <span class="lp-footer__sep" aria-hidden="true">|</span>
                <span class="lp-footer__text">Softvérove Inžinierstvo project 2025</span>
            </div>
        </div>
    </footer>
</template>

<script setup>
// Reuse the same logo as in the header; plain URL keeps it out of Vite's import graph.
const logoUrl = '/storage/lp-nav-1.png'
</script>
