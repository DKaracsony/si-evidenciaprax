<template>
    <!--
      Section: Hero / Introduction (lp-first)
      Purpose:
      - Present the value prop and a quick visual of the product.
      - Three short bullet-style points.
      - Primary CTA to registration.
      Notes:
      - The image is placed inside the left column so that on phones it naturally
        stacks beneath the title divider (see SCSS layout grid).
    -->
    <section class="lp-first">
        <div class="container">
            <article class="lp-first__card">
                <div class="lp-first__body">
                    <h2 class="lp-first__title h1">
                        PraxSI - Systém na evidenciu odbornej praxi
                    </h2>

                    <!-- Visual preview; hidden if no URL is provided -->
                    <div class="lp-first__media" v-if="imgUrl">
                        <img :src="imgUrl" alt="Ukážka karty / rozhrania systému PraxSI" />
                    </div>

                    <!-- Key points (icon + short sentence) -->
                    <div class="lp-first__points">
                        <p class="lp-first__point">
                            <img :src="iconUrl" alt="" aria-hidden="true" class="lp-first__point-icon" />
                            <span>Moderný portál pre <strong>študenta, firmu a garanta</strong> v jednotnom prostredí.</span>
                        </p>
                        <p class="lp-first__point">
                            <img :src="iconUrl" alt="" aria-hidden="true" class="lp-first__point-icon" />
                            <span><strong>Jasné pravidlá</strong> a <strong>jednotné podmienky</strong> pre všetky strany – žiadne nejasnosti.</span>
                        </p>
                        <p class="lp-first__point">
                            <img :src="iconUrl" alt="" aria-hidden="true" class="lp-first__point-icon" />
                            <span><strong>Profesionalita</strong> a <strong>transparentnosť</strong>, ktoré uľahčujú spoluprácu.</span>
                        </p>
                    </div>

                    <!-- Primary CTA -->
                    <div class="lp-first__actions">
                        <a href="/register" class="btn-primary lp-first__btn-register">Registrovať</a>
                    </div>
                </div>
            </article>
        </div>
    </section>
</template>

<script setup>
// Static assets served from public/storage.
// Keeping plain string URLs avoids Vite import handling.
const imgUrl  = '/storage/lp-first-1.png'
const iconUrl = '/storage/icons/tick-box-icon.png'
</script>
