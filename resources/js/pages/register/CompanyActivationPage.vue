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
                <p>
                    Overenie neprebehlo úspešne. Môže ísť o neplatný alebo expirovaný aktivačný link.
                    Skontrolujte, či používate aktuálny odkaz z najnovšieho e-mailu.
                </p>

                <div class="button-group">
                    <router-link :to="loginUrl" class="btn-primary">Prihlásenie</router-link>
                    <button class="btn-secondary" @click="resendLink" :disabled="sending">
                        Nový aktivačný link
                    </button>
                </div>

                <p v-if="resentSuccess" class="resent-feedback">
                    Odoslali sme vám nový aktivačný link!
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

const status = ref(null)
const loginUrl = '/login'
const resentSuccess = ref(false)
const sending = ref(false)

onMounted(async () => {
    const token = route.query.token
    const email = route.query.email
    if (!token || !email) {
        status.value = 'failed'
        return
    }

    try {
        await axios.get('/api/company/activate', { params: { token, email } })
        status.value = 'success'
    } catch {
        status.value = 'failed'
    }
})

const resendLink = async () => {
    sending.value = true
    try {
        // Placeholder – BE endpoint to be added when ready
        await new Promise((r) => setTimeout(r, 1000))
        resentSuccess.value = true
    } finally {
        sending.value = false
    }
}
</script>

<style lang="scss" scoped>
.activation-main {
    min-height: calc(100vh - 160px);
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(to bottom, #eaf2ff 50%, #ffffff 50%);
}
.activation-card {
    background: #e3edff;
    padding: 3rem;
    border-radius: 1rem;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
    text-align: center;
    max-width: 550px;
    width: 90%;
    h1 { font-size: 1.8rem; color: #003c96; margin-bottom: 1rem; }
    p { font-size: 1.1rem; margin-bottom: 1.5rem; }
    .btn-primary, .btn-secondary {
        display: inline-block; margin: 0.3rem; padding: 0.8rem 1.5rem;
        border-radius: 10px; font-weight: 600; font-size: 1rem; text-decoration: none; cursor: pointer;
    }
    .btn-primary { background: #1868db; color: #fff; border: none; &:hover { background: #1257b7; } }
    .btn-secondary { background: #fff; color: #1868db; border: 2px solid #1868db; &:hover { background: #f2f6ff; } }
    .resent-feedback { color: #00b35c; font-weight: 600; margin-top: 1rem; }
}
</style>
