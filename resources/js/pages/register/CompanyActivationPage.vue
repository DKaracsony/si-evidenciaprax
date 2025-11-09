<template>
    <LandingHeader />

    <main class="activation-main">
        <section class="activation-card">
            <!-- SUCCESS -->
            <template v-if="status === 'success'">
                <h1>Úspešná aktivácia účtu firmy! <span class="icon">✔️</span></h1>
                <p>
                    Účet bol aktivovaný. Dočasné heslo vám poslali e-mailom. Pri prvom prihlásení si ho zmeňte.
                </p>

                <router-link :to="loginUrl" class="btn-primary">Prihlásenie</router-link>
            </template>

            <!-- FAILED -->
            <template v-else-if="status === 'failed'">
                <h1>Aktivácia účtu firmy zlyhala <span class="icon">❌</span></h1>

                <!-- Contextual + honest reason -->
                <p v-if="failCode === 'ALREADY_ACTIVATED'">
                    Tento aktivačný odkaz už bol použitý a účet je <strong>úspešne aktivovaný</strong>.
                    Pokračujte prihlásením do systému.
                </p>
                <p v-else-if="failCode === 'TOKEN_EXPIRED'">
                    Platnosť aktivačného odkazu <strong>vypršala</strong>. Nižšie si môžete nechať poslať nový.
                </p>
                <p v-else-if="failCode === 'TOKEN_INVALID'">
                    Aktivačný odkaz je <strong>neplatný</strong>. Skontrolujte, či používate odkaz z najnovšieho e-mailu,
                    alebo si nižšie nechajte poslať nový.
                </p>
                <p v-else>
                    Overenie neprebehlo úspešne. Môže ísť o neplatný alebo expirovaný aktivačný link.
                    Skontrolujte, či používate aktuálny odkaz z najnovšieho e-mailu.
                </p>

                <div class="button-group">
                    <router-link :to="loginUrl" class="btn-primary">Prihlásenie</router-link>

                    <!-- Show resend only when it makes sense -->
                    <button
                        v-if="failCode !== 'ALREADY_ACTIVATED'"
                        class="btn-secondary"
                        @click="resendLink"
                        :disabled="sending"
                    >
                        {{ sending ? 'Odosielam…' : 'Nový aktivačný link' }}
                    </button>
                </div>

                <p v-if="resentSuccess" class="resent-feedback">
                    Odoslali sme vám nový aktivačný link!
                </p>
                <p v-if="resendError" class="resent-feedback error">
                    {{ resendError }}
                </p>
            </template>

            <!-- LOADING -->
            <template v-else>
                <p>Overujeme aktivačný link…</p>
            </template>
        </section>
    </main>

    <LandingFooter />
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import axios from 'axios'
import LandingHeader from '../../components/LandingHeader.vue'
import LandingFooter from '../../components/LandingFooter.vue'

const route = useRoute()

const status = ref(null)           // 'success' | 'failed' | null
const failCode = ref('')           // 'TOKEN_INVALID' | 'TOKEN_EXPIRED' | 'ALREADY_ACTIVATED' | 'MISSING_PARAMS' | 'UNKNOWN'
const loginUrl = '/login'
const resentSuccess = ref(false)
const resendError = ref('')
const sending = ref(false)

onMounted(async () => {
    const token = route.query.token
    const email = route.query.email
    if (!token || !email) {
        status.value = 'failed'
        failCode.value = 'MISSING_PARAMS'
        return
    }

    try {
        await axios.get('/api/company/activate', { params: { token, email } })
        status.value = 'success'
    } catch (err) {
        status.value = 'failed'
        // Try to read BE error code for honest messaging
        failCode.value = err?.response?.data?.code || 'UNKNOWN'
    }
})

const resendLink = async () => {
    if (sending.value) return
    resendError.value = ''
    resentSuccess.value = false
    sending.value = true

    try {
        // Prefer email from the activation link; otherwise prompt.
        let email = String(route.query.email ?? '').trim()
        if (!email) {
            const input = window.prompt('Zadajte e-mail, na ktorý bol poslaný aktivačný odkaz:')
            if (!input) { sending.value = false; return }
            email = String(input).trim()
        }

        const res = await axios.post('/api/company/activate/resend', { email })

        // Note: BE always returns 200 + generic message, even if user not found / already active.
        // So we only show a generic success toast. If you want stricter honesty,
        // ask BE to add { sent: true|false } and check it here.
        if (res.status === 200) {
            resentSuccess.value = true
        }
    } catch (err) {
        const data = err?.response?.data
        const emailMsg = data?.errors?.email?.[0]
        resendError.value = emailMsg || 'Nepodarilo sa odoslať žiadosť. Skúste znova.'
    } finally {
        sending.value = false
    }
}
</script>

<style lang="scss" scoped>
/* Activation — desktop-first responsive styles */

/* ===== Desktop defaults (≥1280px) ===== */
.activation-main {
    min-height: calc(100vh - 160px);
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(to bottom, #eaf2ff 50%, #ffffff 50%);
}

.activation-card {
    /* Desktop baseline */
    --btn-h: 56px;

    margin-inline: auto;
    width: min(94%, 980px);
    max-width: 980px;

    background: var(--color-primary-400);
    border: 6px solid #a9c9ff;
    border-radius: var(--radius, 16px);
    overflow: hidden;
    box-shadow: 0 16px 36px rgba(0,0,0,0.12);

    padding: 60px; /* ample desktop padding */
    text-align: center;
    color: #000;

    h1 {
        margin: 0;
        font-size: 34px;
        line-height: 1.14;
        font-weight: 800;
        letter-spacing: -0.013em;

        &::after {
            content: '';
            display: block;
            height: 3px;
            width: min(92%, 860px);
            background: var(--color-primary-900);
            margin: 20px auto 0;
            border-radius: 9999px;
        }

        .icon { font-size: 1em; margin-left: 8px; vertical-align: baseline; }
    }

    p {
        font-size: 18px;
        line-height: 1.52;
        font-weight: 600;
        margin: 16px 0 22px;
    }

    .button-group {
        display: flex;
        gap: 12px;
        justify-content: center;
        flex-wrap: wrap;
        margin-top: 16px; /* extra gap from text */
        margin-bottom: 2px;
    }

    /* Primary-styled compact buttons (both blue), equal-feeling widths */
    .btn-primary,
    .btn-secondary {
        display: inline-flex;
        align-items: center;
        justify-content: center;

        inline-size: auto; /* natural width */
        min-inline-size: clamp(200px, 30ch, 360px); /* tuned to longer label */
        block-size: var(--btn-h);
        line-height: 1;

        padding: 0 32px;
        font-size: 16px;
        font-weight: 800;
        border-radius: calc(var(--radius, 16px) - 2px);
        text-decoration: none;

        border: 4px solid #a9c9ff;
        background: #1868DB !important;
        color: var(--text-inverse) !important;
        box-shadow: 0 10px 22px rgba(0, 0, 0, 0.26);

        transition:
            background-color 200ms ease,
            border-color 200ms ease,
            box-shadow 200ms ease,
            transform 120ms ease;
        will-change: transform;
        backface-visibility: hidden;

        &:hover {
            background: #155CC4 !important;
            border-color: var(--color-primary-700);
            box-shadow: 0 12px 26px rgba(0,0,0,0.30);
            transform: scale(1.02);
        }
        &:active { background: #134FAA !important; transform: translateY(0.5px); }
        &:focus-visible { outline: 3px solid #a9c9ff; outline-offset: 2px; }
        &:disabled { opacity: .6; cursor: not-allowed; transform: none; }
    }

    /* Single-button success view: keep auto width with a sensible minimum */
    .btn-primary:not(.button-group .btn-primary) {
        min-inline-size: clamp(170px, 26ch, 300px);
    }

    .resent-feedback {
        color: #00b35c;
        font-weight: 700;
        margin-top: 14px;
        font-size: 15.5px;
    }
    .resent-feedback.error {
        color: #DC2626;
    }
}

/* ===== Large / Desktop down (≤1024px) ===== */
@media (max-width: 1024px) {
    .activation-card {
        --btn-h: 54px;
        padding: 56px;

        h1 { font-size: 32px; }
        p  { font-size: 17.5px; }

        .btn-primary,
        .btn-secondary { font-size: 16px; }
    }
}

/* ===== Medium / Tablet down (≤768px) ===== */
@media (max-width: 768px) {
    .activation-card {
        --btn-h: 52px;
        width: min(96%, 980px); /* a bit more breathing room */
        padding: clamp(36px, 6.4vw, 56px);
        box-shadow: 0 14px 32px rgba(0,0,0,0.12);

        h1 {
            font-size: clamp(26px, 3.2vw, 32px);
            &::after { margin-top: 20px; }
        }
        p { font-size: clamp(15.5px, 1.8vw, 18px); }

        .btn-primary,
        .btn-secondary {
            min-inline-size: clamp(190px, 30ch, 340px);
            font-size: 15.5px;
            padding-inline: clamp(24px, 3.4vw, 32px);
            box-shadow: 0 10px 22px rgba(0,0,0,0.26);
        }
    }
}

/* ===== Small down (≤480px) ===== */
@media (max-width: 480px) {
    .activation-card {
        --btn-h: 50px;
        padding: clamp(32px, 6vw, 44px);

        h1 { font-size: clamp(24px, 4.2vw, 30px); }
        p  { font-size: clamp(15px, 2.8vw, 17px); }

        .btn-primary,
        .btn-secondary {
            min-inline-size: clamp(180px, 28ch, 310px);
            font-size: 14.5px;
            padding-inline: clamp(22px, 4vw, 28px);
        }
    }
}

/* ===== Tiny phones down (≤360px) ===== */
@media (max-width: 360px) {
    .activation-card {
        border-width: 5px;
        padding: 20px;
        --btn-h: 46px;

        .btn-primary,
        .btn-secondary {
            min-inline-size: clamp(150px, 24ch, 260px);
            padding-inline: 18px;
            font-size: 13.5px;
        }
    }
}

/* ===== Hover polish only on hover-capable devices ===== */
@media (hover: hover) and (pointer: fine) {
    .activation-card .btn-primary:hover,
    .activation-card .btn-secondary:hover { transform: scale(1.02); }
}

/* ===== Reduced motion ===== */
@media (prefers-reduced-motion: reduce) {
    .activation-card .btn-primary,
    .activation-card .btn-secondary {
        transition: none;
    }
}
</style>
