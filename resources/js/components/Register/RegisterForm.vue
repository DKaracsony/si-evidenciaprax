<template>
    <!-- Card container -->
    <article class="register-card">
        <!-- Tabs -->
        <div class="lp-second__tabs register-tabs" role="tablist" aria-label="Prepínač registrácie">
            <button
                ref="btn1"
                class="lp-second__btn register-tab"
                :style="tabStyle"
                :class="{ 'is-active': activeTab === 'student' }"
                role="tab"
                :aria-selected="activeTab === 'student'"
                aria-controls="reg-panel-student"
                @click="activeTab = 'student'"
            >
                Študent
            </button>
            <button
                ref="btn2"
                class="lp-second__btn register-tab"
                :style="tabStyle"
                :class="{ 'is-active': activeTab === 'company' }"
                role="tab"
                :aria-selected="activeTab === 'company'"
                aria-controls="reg-panel-company"
                @click="activeTab = 'company'"
            >
                Firma
            </button>
        </div>

        <!-- BODY (two upper sections + divider + footer CTA) -->
        <section
            id="reg-panel-student"
            role="tabpanel"
            :aria-hidden="activeTab !== 'student'"
            v-show="activeTab === 'student'"
            class="register-panel"
        >
            <div class="register-body">
                <!-- UPPER: left + right, centered both ways -->
                <div class="register-sides">
                    <div class="register-left">
                        <form ref="studentForm" class="register-form register-form--bare" @submit.prevent>
                            <!-- ========= Sekcia: Študent ========= -->
                            <div class="form-section">
                                <h3 class="form-section__title">Študent</h3>
                                <div class="form-section__divider" aria-hidden="true"></div>

                                <div class="form-grid form-grid--3">
                                    <!-- Titul (not required) -->
                                    <div class="field">
                                        <label for="titul" class="label">Titul</label>
                                        <input
                                            id="titul"
                                            v-model.trim="student.titul"
                                            class="input"
                                            type="text"
                                            autocomplete="honorific-prefix"
                                            placeholder="Titul"
                                        />
                                        <!-- not required, so no error -->
                                    </div>

                                    <!-- Meno (required, non-empty) -->
                                    <div class="field">
                                        <label for="meno" class="label label--required">Meno</label>
                                        <input
                                            id="meno"
                                            v-model.trim="student.meno"
                                            class="input"
                                            type="text"
                                            autocomplete="given-name"
                                            placeholder="Meno"
                                            required
                                            :aria-invalid="!!errors.meno"
                                        />
                                        <p v-if="errors.meno" class="error">{{ errors.meno }}</p>
                                    </div>

                                    <!-- Priezvisko (required, non-empty) -->
                                    <div class="field">
                                        <label for="priezvisko" class="label label--required">Priezvisko</label>
                                        <input
                                            id="priezvisko"
                                            v-model.trim="student.priezvisko"
                                            class="input"
                                            type="text"
                                            autocomplete="family-name"
                                            placeholder="Priezvisko"
                                            required
                                            :aria-invalid="!!errors.priezvisko"
                                        />
                                        <p v-if="errors.priezvisko" class="error">{{ errors.priezvisko }}</p>
                                    </div>
                                </div>

                                <div class="form-grid form-grid--2">
                                    <!-- Tel. č. (numbers only OR starts with +421 and then numbers) -->
                                    <div class="field">
                                        <label for="tel" class="label label--required">Tel. č.</label>
                                        <input
                                            id="tel"
                                            v-model.trim="student.tel"
                                            class="input"
                                            type="tel"
                                            inputmode="tel"
                                            autocomplete="tel"
                                            placeholder="Tel. č."
                                            required
                                            :aria-invalid="!!errors.tel"
                                        />
                                        <p v-if="errors.tel" class="error">{{ errors.tel }}</p>
                                    </div>

                                    <!-- Osobný email (must include @) -->
                                    <div class="field">
                                        <label for="osobnyEmail" class="label label--required">Osobný email</label>
                                        <input
                                            id="osobnyEmail"
                                            v-model.trim="student.osobnyEmail"
                                            class="input"
                                            type="email"
                                            autocomplete="email"
                                            placeholder="Osobný email"
                                            required
                                            :aria-invalid="!!errors.osobnyEmail"
                                        />
                                        <p v-if="errors.osobnyEmail" class="error">{{ errors.osobnyEmail }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- ========= Sekcia: Školské informácie ========= -->
                            <div class="form-section">
                                <h3 class="form-section__title">Školské informácie</h3>
                                <div class="form-section__divider" aria-hidden="true"></div>

                                <div class="form-grid form-grid--2">
                                    <!-- Študentský mail (must end with @student.ukf.sk) -->
                                    <div class="field">
                                        <label for="skolskyEmail" class="label label--required">Študentský mail</label>
                                        <input
                                            id="skolskyEmail"
                                            v-model.trim="student.skolskyEmail"
                                            class="input"
                                            type="email"
                                            placeholder="Študentský mail"
                                            required
                                            :aria-invalid="!!errors.skolskyEmail"
                                        />
                                        <p v-if="errors.skolskyEmail" class="error">{{ errors.skolskyEmail }}</p>
                                    </div>

                                    <!-- Študijný odbor (must choose non-placeholder) -->
                                    <div class="field">
                                        <label for="odbor" class="label label--required">Študijný odbor</label>
                                        <select
                                            id="odbor"
                                            v-model="student.odborId"
                                            class="input"
                                            required
                                            :aria-invalid="!!errors.odborId"
                                        >
                                            <option value="" disabled>
                                                {{ facultiesLoading ? 'Načítavam…' : (facultiesError ? 'Nedostupné' : 'Študijný odbor') }}
                                            </option>
                                            <option
                                                v-for="f in faculties"
                                                :key="f.id"
                                                :value="String(f.id)"
                                            >
                                                {{ f.name }}
                                            </option>
                                        </select>
                                        <p v-if="errors.odborId" class="error">{{ errors.odborId }}</p>

                                        <p v-if="facultiesError" class="error" style="margin-top:6px;">
                                            {{ facultiesError }}
                                            <button type="button" @click="loadFaculties()" style="all:unset; text-decoration:underline; cursor:pointer;">Skúsiť znova</button>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- ========= Sekcia: Adresa ========= -->
                            <div class="form-section">
                                <h3 class="form-section__title">Adresa</h3>
                                <div class="form-section__divider" aria-hidden="true"></div>

                                <div class="form-grid form-grid--2">
                                    <!-- Mesto (required, non-empty) -->
                                    <div class="field">
                                        <label for="mesto" class="label label--required">Mesto</label>
                                        <input
                                            id="mesto"
                                            v-model.trim="student.mesto"
                                            class="input"
                                            type="text"
                                            placeholder="Mesto"
                                            required
                                            :aria-invalid="!!errors.mesto"
                                        />
                                        <p v-if="errors.mesto" class="error">{{ errors.mesto }}</p>
                                    </div>

                                    <!-- PSČ (only numbers) -->
                                    <div class="field">
                                        <label for="psc" class="label label--required">PSČ</label>
                                        <input
                                            id="psc"
                                            v-model.trim="student.psc"
                                            class="input"
                                            type="text"
                                            inputmode="numeric"
                                            placeholder="PSČ"
                                            required
                                            :aria-invalid="!!errors.psc"
                                        />
                                        <p v-if="errors.psc" class="error">{{ errors.psc }}</p>
                                    </div>
                                </div>

                                <div class="form-grid form-grid--2">
                                    <!-- Ulica (required, non-empty) -->
                                    <div class="field">
                                        <label for="ulica" class="label label--required">Ulica</label>
                                        <input
                                            id="ulica"
                                            v-model.trim="student.ulica"
                                            class="input"
                                            type="text"
                                            placeholder="Ulica"
                                            required
                                            :aria-invalid="!!errors.ulica"
                                        />
                                        <p v-if="errors.ulica" class="error">{{ errors.ulica }}</p>
                                    </div>

                                    <!-- č. domu (only numbers) -->
                                    <div class="field">
                                        <label for="cisloDomu" class="label label--required">č. domu</label>
                                        <input
                                            id="cisloDomu"
                                            v-model.trim="student.cisloDomu"
                                            class="input"
                                            type="text"
                                            inputmode="numeric"
                                            placeholder="č. domu"
                                            required
                                            :aria-invalid="!!errors.cisloDomu"
                                        />
                                        <p v-if="errors.cisloDomu" class="error">{{ errors.cisloDomu }}</p>
                                    </div>
                                </div>

                                <div class="form-grid form-grid--1">
                                    <!-- Krajina (must choose non-placeholder) -->
                                    <div class="field">
                                        <label for="krajina" class="label label--required">Krajina</label>
                                        <select
                                            id="krajina"
                                            v-model="student.krajina"
                                            class="input"
                                            required
                                            :aria-invalid="!!errors.krajina"
                                        >
                                            <option value="" disabled>Krajina</option>
                                            <option
                                                v-for="c in COUNTRIES_STATIC"
                                                :key="c.code"
                                                :value="c.code"
                                            >
                                                {{ c.label }}
                                            </option>
                                        </select>
                                        <p v-if="errors.krajina" class="error">{{ errors.krajina }}</p>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- RIGHT: headline + illustration -->
                    <div class="register-right">
                        <h2 class="register-panel__title">
                            Ste našim študentom?<br />Zaregistrujte teraz!
                        </h2>
                        <div class="right-section__divider" aria-hidden="true"></div>

                        <img
                            class="register-illustration-img"
                            :src="logoUrl"
                            alt="Študent s batohom (ilustrácia)"
                            loading="eager"
                            decoding="async"
                            fetchpriority="high"
                        />
                    </div>
                </div>

                <!-- LOWER: CTA + helper text spanning full width -->
                <div class="register-footer">
                    <button
                        type="button"
                        class="btn btn--primary"
                        :disabled="!isFormValid"
                    >
                        Registrácia
                    </button>
                    <p class="hint footer-hint">
                        Už máte účet? <a href="/login">Prihláste sa!</a>
                    </p>
                </div>
            </div>
        </section>

        <!-- Company panel stays as placeholder for now -->
        <section
            id="reg-panel-company"
            role="tabpanel"
            :aria-hidden="activeTab !== 'company'"
            v-show="activeTab === 'company'"
            class="register-panel"
        >
            <div class="register-body">
                <div class="register-sides">
                    <!-- LEFT: headline + divider + image (reversed) -->
                    <div class="register-right">
                        <h2 class="register-panel__title">Ste z&nbsp;firmy? Zaregistrujte sa.</h2>
                        <div class="right-section__divider" aria-hidden="true"></div>

                        <img
                            class="register-illustration-img"
                            :src="logoUrl2"
                            alt="Zástupca firmy (ilustrácia)"
                            loading="eager"
                            decoding="async"
                            fetchpriority="high"
                        />
                    </div>

                    <!-- RIGHT: company form -->
                    <div class="register-left">
                        <form class="register-form register-form--bare" @submit.prevent>
                            <!-- ========= Sekcia: Kontaktná osoba ========= -->
                            <div class="form-section">
                                <h3 class="form-section__title">Kontaktná osoba</h3>
                                <div class="form-section__divider" aria-hidden="true"></div>

                                <div class="form-grid form-grid--3">
                                    <!-- Titul (not required) -->
                                    <div class="field">
                                        <label for="c_titul" class="label">Titul</label>
                                        <input
                                            id="c_titul"
                                            v-model.trim="company.titul"
                                            class="input"
                                            type="text"
                                            autocomplete="honorific-prefix"
                                            placeholder="Titul"
                                        />
                                    </div>

                                    <!-- Meno -->
                                    <div class="field">
                                        <label for="c_meno" class="label label--required">Meno</label>
                                        <input
                                            id="c_meno"
                                            v-model.trim="company.meno"
                                            class="input"
                                            type="text"
                                            autocomplete="given-name"
                                            placeholder="Meno"
                                            required
                                        />
                                    </div>

                                    <!-- Priezvisko -->
                                    <div class="field">
                                        <label for="c_priezvisko" class="label label--required">Priezvisko</label>
                                        <input
                                            id="c_priezvisko"
                                            v-model.trim="company.priezvisko"
                                            class="input"
                                            type="text"
                                            autocomplete="family-name"
                                            placeholder="Priezvisko"
                                            required
                                        />
                                    </div>
                                </div>

                                <div class="form-grid form-grid--2">
                                    <!-- Tel. č. -->
                                    <div class="field">
                                        <label for="c_tel" class="label label--required">Tel. č.</label>
                                        <input
                                            id="c_tel"
                                            v-model.trim="company.tel"
                                            class="input"
                                            type="tel"
                                            inputmode="tel"
                                            autocomplete="tel"
                                            placeholder="Tel. č."
                                            required
                                        />
                                    </div>

                                    <!-- Osobný email -->
                                    <div class="field">
                                        <label for="c_osobnyEmail" class="label label--required">Osobný email</label>
                                        <input
                                            id="c_osobnyEmail"
                                            v-model.trim="company.osobnyEmail"
                                            class="input"
                                            type="email"
                                            autocomplete="email"
                                            placeholder="Osobný email"
                                            required
                                        />
                                    </div>
                                </div>
                            </div>

                            <!-- ========= Sekcia: Firemné informácie ========= -->
                            <div class="form-section">
                                <h3 class="form-section__title">Firemné informácie</h3>
                                <div class="form-section__divider" aria-hidden="true"></div>

                                <div class="form-grid form-grid--2">
                                    <!-- Názov firmy -->
                                    <div class="field">
                                        <label for="c_nazovFirmy" class="label label--required">Názov firmy</label>
                                        <input
                                            id="c_nazovFirmy"
                                            v-model.trim="company.nazovFirmy"
                                            class="input"
                                            type="text"
                                            placeholder="Názov firmy"
                                            required
                                        />
                                    </div>

                                    <!-- Rola vo firme -->
                                    <div class="field">
                                        <label for="c_rolaVoFirme" class="label label--required">Rola vo firme</label>
                                        <input
                                            id="c_rolaVoFirme"
                                            v-model.trim="company.rolaVoFirme"
                                            class="input"
                                            type="text"
                                            placeholder="Rola vo firme"
                                            required
                                        />
                                    </div>
                                </div>

                                <!-- Popis (full width row) -->
                                <div class="form-grid form-grid--1" style="margin-top: 12px;">
                                    <div class="field">
                                        <label for="c_popis" class="label">Popis</label>
                                        <textarea
                                            id="c_popis"
                                            v-model.trim="company.popis"
                                            class="input"
                                            placeholder="Stručný popis firmy, čomu sa venujete…"
                                            rows="4"
                                        ></textarea>
                                    </div>
                                </div>

                                <!-- Web (full width row) -->
                                <div class="form-grid form-grid--1">
                                    <div class="field">
                                        <label for="c_web" class="label">Web</label>
                                        <input
                                            id="c_web"
                                            v-model.trim="company.web"
                                            class="input"
                                            type="url"
                                            inputmode="url"
                                            placeholder="https://www.priklad.sk"
                                        />
                                    </div>
                                </div>
                            </div>

                            <!-- ========= Sekcia: Adresa ========= -->
                            <div class="form-section">
                                <h3 class="form-section__title">Adresa</h3>
                                <div class="form-section__divider" aria-hidden="true"></div>

                                <div class="form-grid form-grid--2">
                                    <!-- Mesto -->
                                    <div class="field">
                                        <label for="c_mesto" class="label label--required">Mesto</label>
                                        <input
                                            id="c_mesto"
                                            v-model.trim="company.mesto"
                                            class="input"
                                            type="text"
                                            placeholder="Mesto"
                                            required
                                        />
                                    </div>

                                    <!-- PSČ -->
                                    <div class="field">
                                        <label for="c_psc" class="label label--required">PSČ</label>
                                        <input
                                            id="c_psc"
                                            v-model.trim="company.psc"
                                            class="input"
                                            type="text"
                                            inputmode="numeric"
                                            placeholder="PSČ"
                                            required
                                        />
                                    </div>
                                </div>

                                <div class="form-grid form-grid--2">
                                    <!-- Ulica -->
                                    <div class="field">
                                        <label for="c_ulica" class="label label--required">Ulica</label>
                                        <input
                                            id="c_ulica"
                                            v-model.trim="company.ulica"
                                            class="input"
                                            type="text"
                                            placeholder="Ulica"
                                            required
                                        />
                                    </div>

                                    <!-- č. domu -->
                                    <div class="field">
                                        <label for="c_cisloDomu" class="label label--required">č. domu</label>
                                        <input
                                            id="c_cisloDomu"
                                            v-model.trim="company.cisloDomu"
                                            class="input"
                                            type="text"
                                            inputmode="numeric"
                                            placeholder="č. domu"
                                            required
                                        />
                                    </div>
                                </div>

                                <div class="form-grid form-grid--1">
                                    <!-- Krajina -->
                                    <div class="field">
                                        <label for="c_krajina" class="label label--required">Krajina</label>
                                        <select id="c_krajina" v-model="company.krajina" class="input" required>
                                            <option value="" disabled>Krajina</option>
                                            <option
                                                v-for="c in COUNTRIES_STATIC"
                                                :key="c.code"
                                                :value="c.code"
                                            >
                                                {{ c.label }}
                                            </option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- LOWER: CTA + helper text -->
                <div class="register-footer">
                    <button type="button" class="btn btn--primary" disabled>
                        Registrácia
                    </button>
                    <p class="hint footer-hint">Už máte účet? <a href="/login">Prihláste sa!</a></p>
                </div>
            </div>
        </section>

    </article>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onBeforeUnmount, nextTick, reactive } from 'vue'
import axios from 'axios'

const logoUrl = '/storage/registerform-student2.png'
const logoUrl2 = '/storage/registerformcompany.png'

/* --- No magic strings: countries defined once here --- */
type CountryOption = { code: 'SK' | 'CZ'; label: string }
const COUNTRIES_STATIC: CountryOption[] = [
    { code: 'SK', label: 'Slovenská republika' },
    { code: 'CZ', label: 'Česká republika' },
]

/* Tabs */
const activeTab = ref<'student' | 'company'>('student')

/* Equalize tab widths on desktop */
const btn1 = ref<HTMLElement | null>(null)
const btn2 = ref<HTMLElement | null>(null)
const equalBtnWidth = ref('auto')
const tabStyle = computed(() => ({ width: equalBtnWidth.value }))
let raf = 0
const smallMQL = window.matchMedia('(max-width: 520px)')

async function setEqualBtnWidth () {
    if (!btn1.value || !btn2.value) return
    equalBtnWidth.value = 'auto'
    await nextTick()
    const max = Math.max(btn1.value?.offsetWidth ?? 0, btn2.value?.offsetWidth ?? 0)
    equalBtnWidth.value = `${max}px`
}
async function applyEqualize () {
    if (smallMQL.matches) equalBtnWidth.value = 'auto'
    else await setEqualBtnWidth()
}
function onResize () {
    cancelAnimationFrame(raf)
    raf = requestAnimationFrame(() => { void applyEqualize() })
}
function onMQChange () { void applyEqualize() }

/* Faculties (Študijný odbor) */
type Faculty = { id: number; name: string; active: number }
const faculties = ref<Faculty[]>([])
const facultiesLoading = ref(true)
const facultiesError = ref('')

async function loadFaculties () {
    facultiesLoading.value = true
    facultiesError.value = ''
    try {
        const { data } = await axios.get('/api/faculties')
        faculties.value = Array.isArray(data)
            ? data.filter((f: any) => f.active === 1 || String(f.active) === '1')
            : []
    } catch (e) {
        facultiesError.value = 'Nepodarilo sa načítať študijné odbory.'
    } finally {
        facultiesLoading.value = false
    }
}

/* Student model */
const student = reactive({
    titul: '',
    meno: '',
    priezvisko: '',
    tel: '',
    osobnyEmail: '',
    skolskyEmail: '',
    odborId: '',   // selected faculty ID (string for v-model)
    mesto: '',
    psc: '',
    ulica: '',
    cisloDomu: '',
    krajina: ''    // 'SK' | 'CZ'
})

/* ---- Validation rules (live, no submit) ---- */
const errors = computed(() => {
    const e: Record<string, string> = {}

    // Meno & Priezvisko
    if (!student.meno.trim()) e.meno = 'Zadajte meno.'
    if (!student.priezvisko.trim()) e.priezvisko = 'Zadajte priezvisko.'

    // Tel: only numbers OR +421 then numbers
    const tel = student.tel.trim()
    const telOk = /^[0-9]+$/.test(tel) || /^\+421[0-9]+$/.test(tel)
    if (!telOk) e.tel = 'Zadajte len čísla alebo tvar +421…'

    // Osobný email: must include '@'
    if (!student.osobnyEmail.trim().includes('@')) e.osobnyEmail = 'E-mail musí obsahovať @'

    // Študentský mail: local@student.ukf.sk
    const se = student.skolskyEmail.trim()
    const studentMailOk = /^[^@\s]+@student\.ukf\.sk$/i.test(se)
    if (!studentMailOk) e.skolskyEmail = 'E-mail musí končiť @student.ukf.sk'

    // Odbor: not placeholder
    if (!String(student.odborId || '').trim()) e.odborId = 'Vyberte študijný odbor.'

    // Adresa
    if (!student.mesto.trim()) e.mesto = 'Zadajte mesto.'
    if (!/^\d+$/.test(student.psc.trim())) e.psc = 'PSČ môže obsahovať len čísla.'
    if (!student.ulica.trim()) e.ulica = 'Zadajte ulicu.'
    if (!/^\d+$/.test(student.cisloDomu.trim())) e.cisloDomu = 'Číslo domu môže obsahovať len čísla.'

    // Krajina: not placeholder
    if (!student.krajina) e.krajina = 'Vyberte krajinu.'

    return e
})

const isFormValid = computed(() => Object.keys(errors.value).length === 0)

onMounted(async () => {
    await nextTick()
    await applyEqualize()
    window.addEventListener('resize', onResize)
    smallMQL.addEventListener('change', onMQChange)

    await loadFaculties()
})
onBeforeUnmount(() => {
    window.removeEventListener('resize', onResize)
    smallMQL.removeEventListener('change', onMQChange)
})

const company = reactive({
    // Kontaktná osoba
    titul: '',
    meno: '',
    priezvisko: '',
    tel: '',
    osobnyEmail: '',
    // Firemné informácie
    nazovFirmy: '',
    rolaVoFirme: '',
    popis: '',
    web: '',
    // Adresa
    mesto: '',
    psc: '',
    ulica: '',
    cisloDomu: '',
    krajina: '' // 'SK' | 'CZ'
})

</script>

<style scoped>
/* -------- layout scaffolding for the new structure -------- */

/* Space below tabs */
.register-panel {padding-top: clamp(64px, 7vw, 96px);}

/* Container for: divider + sides + footer */
.register-body {
    position: relative;
    display: grid;
    grid-template-rows: auto auto; /* sides + footer */
    row-gap: clamp(18px, 2.4vw, 28px);
}

/* Two upper sections; centered both ways */
.register-sides {
    position: relative; /* needed for the pseudo divider */
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: clamp(16px, 2.4vw, 28px);
    align-items: center;
    justify-items: center;
    min-height: clamp(330px, 33vw, 520px); /* was 290px, 29vw, 440px */
}

/* Centered vertical divider ONLY through .register-sides */
.register-sides::after {
    content: "";
    position: absolute;
    top: 0;
    bottom: 0;          /* only spans upper halves */
    left: 50%;
    width: 3px;         /* thicker */
    background: #155cc4;/* blue */
    transform: translateX(-1.5px);
    pointer-events: none;
    opacity: 0.95;
}

/* ===== Right-side title divider (matches section dividers) ===== */
.right-section__divider {
    height: 2px;
    width: 100%;
    background: #155cc4;
    margin: 8px 0 14px; /* sits between title and image */
    border-radius: 2px;
}


/* Left = form area */
.register-left,
.register-right {
    width: 100%;
    max-width: 560px;
    display: grid;
    justify-items: stretch;
}

/* Right side: center content, and nudge it UP slightly to align with left divider */
.register-right {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: flex-start;
    text-align: center;
    transform: translateY(-12px);
}

/* Remove white box around the form for this screen */
.register-form--bare {
    background: transparent !important;
    border: 0 !important;
    padding: 0 !important;
}

/* Mobile/tablet */
@media (max-width: 900px) {
    .register-right { transform: none; }
    .register-illustration-img {
        width: 100%;
        max-width: 500px;  /* slight bump here too */
        margin-inline: auto;
    }
    .register-sides { min-height: unset; }
}

/* Title spacing tuned for the centered layout */
.register-panel__title {
    font-size: clamp(20px, 2.6vw, 28px);
    margin: 0 0 14px;  /* slightly tighter under the heading before image */
}


/* Image sizing – fills the right column nicely without stretching */
.register-illustration-img {
    width: clamp(300px, 30vw, 560px);
    height: auto;
    object-fit: contain;
    display: block;
}
/* Footer with CTA */
.register-footer {
    display: grid;
    justify-items: center;
    row-gap: 8px;
    padding-top: 0; /* not needed now */
    margin-top: clamp(16px, 3.6vw, 36px); /* nudged up from previous value */
    padding-bottom: 0;
}
.footer-hint { margin: 4px 0 0; }

/* Button: keep blue, add nicer hover/focus */
.btn.btn--primary{
    display:inline-flex; align-items:center; justify-content:center;
    min-width:220px; height:44px; padding:0 16px;
    border-radius:10px;
    border:2px solid #1454B2;           /* crisper blue border */
    background:#2a75ea;
    color:#fff; font-weight:600;
    box-shadow:0 2px 0 #155cc4;
    transition: transform 120ms ease, background-color 120ms ease, box-shadow 120ms ease;
}
.btn.btn--primary:hover {
    background: #1f66e0;                  /* subtle darker blue */
    transform: translateY(-1px);
    box-shadow:0 3px 0 #155cc4;
}
.btn.btn--primary:focus-visible{
    outline: 2px solid #1454B2;
    outline-offset: 2px;
}
.btn[disabled]{ opacity:.6; cursor:not-allowed; }

/* Section headings slightly bigger than labels */
.form-section + .form-section { margin-top: clamp(12px, 2vw, 20px); }

.form-section__title {
    font-size: clamp(1.05rem, 1.6vw, 1.2rem);
    font-weight: 700;
    margin: 0 0 6px;
    color: #0e3e8a; /* darker variant of your primary */
}

.form-section__divider {
    height: 2px;
    background: #155cc4; /* your darker blue */
    margin: 0 0 12px;
    border-radius: 2px;
}

/* Grid helpers for rows */
.form-grid {
    column-gap: 16px;
    row-gap: 28px;
}
.form-grid--3 { display: grid; grid-template-columns: 1fr 1fr 1fr; }
.form-grid--2 { display: grid; grid-template-columns: 1fr 1fr; }
.form-grid--1 { display: grid; grid-template-columns: 1fr; }

@media (max-width: 900px) {
    .form-grid--3,
    .form-grid--2 { grid-template-columns: 1fr; }
    .register-right { transform: none; }
}

/* Labels with tiny red star for required */
.label {
    font-weight: 600;
    margin-bottom: 9px;
}
.label--required::after {
    content: " *";
    color: #DC2626;
    font-weight: 700;
    margin-left: 2px;
}

/* Inputs */
.input { width: 100%; }

.input[aria-invalid="true"], select.input[aria-invalid="true"] {
    border-color: #DC2626;
}

/* Tiny red error text */
.error {
    color: #DC2626;
    font-size: 0.85rem;
    margin-top: 6px;
}
</style>
