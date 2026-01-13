<template>
    <AppHeader />

    <main class="activation-main">
        <section class="activation-card">
            <!-- SUCCESS -->
            <template v-if="status === 'success'">
                <h1>Úspešná aktivácia účtu firmy!</h1>
                <p>
                    Účet bol aktivovaný. Dočasné heslo vám poslali e-mailom. Pri prvom prihlásení si ho zmeňte.
                </p>

                <router-link :to="loginUrl" class="btn-primary">
                    Prihlásenie
                </router-link>
            </template>

            <!-- FAILED -->
            <template v-else-if="status === 'failed'">
                <h1>Aktivácia účtu firmy zlyhala</h1>

                <!-- Contextual + honest reason -->
                <p v-if="failCode === 'ALREADY_ACTIVATED'">
                    Tento aktivačný odkaz už bol použitý a účet je
                    <strong>úspešne aktivovaný</strong>.
                    Pokračujte prihlásením do systému.
                </p>
                <p v-else-if="failCode === 'TOKEN_EXPIRED'">
                    Platnosť aktivačného odkazu <strong>vypršala</strong>.
                    Nižšie si môžete nechať poslať nový.
                </p>
                <p v-else-if="failCode === 'TOKEN_INVALID'">
                    Aktivačný odkaz je <strong>neplatný</strong>. Skontrolujte, či používate odkaz
                    z najnovšieho e-mailu, alebo si nižšie nechajte poslať nový.
                </p>
                <p v-else>
                    Overenie neprebehlo úspešne. Môže ísť o neplatný alebo expirovaný aktivačný link.
                    Skontrolujte, či používate aktuálny odkaz z najnovšieho e-mailu.
                </p>

                <!-- Email input for resend (only when we don't have email from link) -->
                <div
                    v-if="failCode !== 'ALREADY_ACTIVATED' && !hasLinkEmail"
                    class="activation-resend"
                >
                    <label for="activation-resend-email" class="activation-resend__label">
                        Zadajte e-mail, ktorý ste zadali pri registrácii firmy
                    </label>

                    <input
                        id="activation-resend-email"
                        type="email"
                        v-model.trim="resendEmail"
                        class="activation-resend__input"
                        autocomplete="email"
                        placeholder="napr. firma@example.com"
                    />
                </div>


                <div class="button-group">
                    <router-link :to="loginUrl" class="btn-primary">
                        Prihlásenie
                    </router-link>

                    <!-- Show resend only when it makes sense -->
                    <button
                        v-if="failCode !== 'ALREADY_ACTIVATED'"
                        class="btn-secondary"
                        @click="resendLink"
                        :disabled="!canResend"
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

    <Footer />
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import axios from 'axios'

import AppHeader from '../../components/Navbar/Navbar.vue'
import Footer from '../../components/Footer/Footer.vue'

const route = useRoute()

const status = ref(null) // 'success' | 'failed' | null
const failCode = ref('') // 'TOKEN_INVALID' | 'TOKEN_EXPIRED' | 'ALREADY_ACTIVATED' | 'MISSING_PARAMS' | 'UNKNOWN'
const loginUrl = '/login'

const resentSuccess = ref(false)
const resendError = ref('')
const sending = ref(false)

/** email either from URL query or manual input */
const resendEmail = ref(String(route.query.email ?? '').trim())

const hasLinkEmail = computed(() => {
    return Boolean(String(route.query.email ?? '').trim())
})

/** Button is enabled only when we are not sending AND we have some email source */
const canResend = computed(() => {
    if (sending.value) return false

    if (hasLinkEmail.value) {
        return true
    }

    return !!resendEmail.value.trim()
})

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
    if (!canResend.value) return

    resendError.value = ''
    resentSuccess.value = false
    sending.value = true

    try {
        // 1) Prefer e-mail z aktivačného linku
        let email = String(route.query.email ?? '').trim()

        // 2) Ak v linku nebol, použijeme ten z inputu
        if (!email) {
            email = resendEmail.value.trim()
        }

        if (!email) {
            resendError.value = 'Zadajte e-mail, na ktorý bol poslaný aktivačný odkaz.'
            sending.value = false
            return
        }

        const { data } = await axios.post('/api/company/activate/resend', { email })

        if (data.sent) {
            // Reálne odišiel nový aktivačný e-mail
            resentSuccess.value = true
            resendError.value = ''
        } else {
            // Neodišiel → zobraz úprimnú správu podľa reason
            switch (data.reason) {
                case 'ALREADY_ACTIVATED':
                    resendError.value = 'Účet už bol aktivovaný. Skúste sa prihlásiť.'
                    break
                case 'NOT_FOUND':
                    resendError.value = 'Účet s daným e-mailom neexistuje.'
                    break
                case 'SEND_FAILED':
                    resendError.value = 'Nepodarilo sa odoslať aktivačný e-mail. Skúste to prosím znova neskôr.'
                    break
                default:
                    resendError.value = data.message || 'Nepodarilo sa odoslať žiadosť. Skúste znova.'
            }
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
