<template>
    <LandingHeader />

    <main class="sent">
        <div class="sent__container">
            <article class="sent__card" :data-variant="resolvedType">
                <header class="sent__header">
                    <h1 class="sent__title">
                        Úspešná registrácia {{ resolvedType === 'student' ? 'študenta' : 'firmy' }}
                        <span class="sent__badge" aria-hidden="true">✔</span>
                    </h1>
                    <div class="sent__divider" role="presentation"></div>
                </header>

                <p v-if="resolvedType === 'student'" class="sent__text">
                    Vaše dočasné heslo sme vám poslali na váš študentský e-mail, pomocou neho sa viete prihlásiť do systému.
                    Po prvom prihlásení budete vyzvaní na zmenu hesla!
                </p>

                <p v-else class="sent__text">
                    E-mail s odkazom na aktiváciu vášho účtu sme vám poslali do vašej e-mailovej schránky.
                    Riaďte sa inštrukciami v e-maile a následne sa prihláste do systému.
                    Po prvom prihlásení budete vyzvaní na zmenu hesla!
                </p>

                <div class="sent__actions" role="group" aria-label="Akcie po registrácii">
                    <RouterLink to="/login" class="sent__btn-primary">Prihlásenie</RouterLink>
                </div>
            </article>
        </div>
    </main>

    <LandingFooter />
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useRoute } from 'vue-router'

import LandingHeader from '../../components/LandingHeader.vue'
import LandingFooter from '../../components/LandingFooter.vue'

const route = useRoute()

const resolvedType = computed<'student' | 'company'>(() => {
    const t = String(route.query.type || '').toLowerCase()
    return t === 'company' ? 'company' : 'student'
})
</script>

<style scoped>
/* Two-tone background exactly 50/50; card perfectly centered */
.sent {
    --bg-split: 50%;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background:
        linear-gradient(#ffffff 0 var(--bg-split), #CDE0FF var(--bg-split) 100%) no-repeat;
}

.sent__container {
    --lp-gutter: clamp(16px, 3vw, 40px);
    max-width: 1520px;
    width: 100%;
    margin-inline: auto;
    padding: 24px var(--lp-gutter) 40px;
    display: grid;
    place-items: center;
}

/* Card (replicated from LandingSecond .lp-second__card) */
.sent__card {
    position: relative;
    margin-inline: auto;
    width: 100%;
    max-width: 1500px;

    min-height: clamp(380px, 44vw, 600px);
    padding: clamp(22px, 3.6vw, 36px) clamp(20px, 3.6vw, 36px) clamp(30px, 3.8vw, 44px);

    background: #7CADFA;
    border: 7px solid #a9c9ff;
    border-radius: 16px;
    box-shadow: none;

    overflow: hidden;
    display: grid;
    grid-template-columns: 1fr;
    gap: clamp(18px, 3vw, 32px);
    text-align: center;
    color: #0a1b3d;
}

@media (max-width: 520px) {
    .sent__card { min-height: clamp(320px, 64vh, 520px); }
}

/* Title / badge / divider */
.sent__title {
    margin: 0;
    font-weight: 800;
    font-size: clamp(22px, 2.6vw, 30px);
    letter-spacing: 0.2px;
    color: #0a1b3d;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}
.sent__badge {
    display: inline-grid;
    place-items: center;
    width: 24px; height: 24px;
    border-radius: 999px;
    background: #0b4fc9;
    color: #fff;
    font-size: 16px; line-height: 1;
}
.sent__divider {
    height: 3px;
    width: min(92%, 920px);
    background: #0a2c5c;
    margin: 14px auto 0;
    border-radius: 9999px;
}

/* Main text — larger */
.sent__text {
    margin: 12px auto 20px;
    max-width: 68ch;
    line-height: 1.6;
    color: #081d3f;
    font-size: clamp(18px, 2.8vw, 26px);
    font-weight: 600;
}

/* Actions */
.sent__actions {
    display: flex;
    justify-content: center;
    gap: 12px;
    flex-wrap: wrap;
    margin-top: 8px;
}

/* Unified primary button (no underline on hover) */
.sent__btn-primary {
    --btn-h: 54px;
    --btn-w: 280px;                /* fixed width for the single CTA */
    min-width: var(--btn-w);

    display: inline-flex;
    align-items: center;
    justify-content: center;
    white-space: nowrap;

    border: 4px solid #a9c9ff;
    background: #1868DB !important;
    color: var(--text-inverse, #fff) !important;
    box-shadow: 0 8px 18px rgba(0, 0, 0, 0.30);
    text-decoration: none !important;
    padding: 0 40px;
    line-height: 1;
    block-size: var(--btn-h);
    min-block-size: var(--btn-h);
    max-block-size: var(--btn-h);

    font-size: 16px;
    font-weight: 700;
    border-radius: 12px;

    will-change: transform;
    backface-visibility: hidden;
    transition:
        background-color 200ms ease,
        border-color 200ms ease,
        box-shadow 200ms ease,
        transform 120ms ease;
}
.sent__btn-primary:hover {
    background: #155CC4 !important;
    color: var(--text-inverse, #fff) !important;
    border-color: #0b4fc9;
    box-shadow: 0 10px 22px rgba(0, 0, 0, 0.32);
    transform: scale(1.03);
    text-decoration: none !important;
}
.sent__btn-primary:active { background: #134FAA !important; transform: translateY(0.5px); }
.sent__btn-primary:focus-visible { outline: 3px solid #a9c9ff; outline-offset: 2px; }

/* Responsive button sizing */
@media (min-width: 768px) {
    .sent__btn-primary { --btn-h: 48px; padding: 0 48px; font-size: 17px; }
}
@media (min-width: 1280px) {
    .sent__btn-primary { --btn-h: 64px; padding: 0 64px; font-size: 20px; }
}
</style>
