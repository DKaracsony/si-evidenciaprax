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

        <!-- BODY: Student -->
        <section
            id="reg-panel-student"
            role="tabpanel"
            :aria-hidden="activeTab !== 'student'"
            v-show="activeTab === 'student'"
            class="register-panel"
        >
            <div class="register-body">
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
                                    </div>

                                    <!-- Meno -->
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
                                            :aria-invalid="studentTouched.meno && !!errors.meno"
                                            @focus="onSTouch('meno')"
                                            @blur="onSTouch('meno', true)"
                                        />
                                        <p v-if="studentTouched.meno && errors.meno" class="error">{{ errors.meno }}</p>
                                    </div>

                                    <!-- Priezvisko -->
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
                                            :aria-invalid="studentTouched.priezvisko && !!errors.priezvisko"
                                            @focus="onSTouch('priezvisko')"
                                            @blur="onSTouch('priezvisko', true)"
                                        />
                                        <p v-if="studentTouched.priezvisko && errors.priezvisko" class="error">{{ errors.priezvisko }}</p>
                                    </div>
                                </div>

                                <div class="form-grid form-grid--2">
                                    <!-- Tel. č. -->
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
                                            :aria-invalid="studentTouched.tel && !!errors.tel"
                                            @focus="onSTouch('tel')"
                                            @blur="onSTouch('tel', true)"
                                        />
                                        <p v-if="studentTouched.tel && errors.tel" class="error">{{ errors.tel }}</p>
                                    </div>

                                    <!-- Osobný email -->
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
                                            :aria-invalid="studentTouched.osobnyEmail && !!errors.osobnyEmail"
                                            @focus="onSTouch('osobnyEmail')"
                                            @blur="onSTouch('osobnyEmail', true)"
                                        />
                                        <p v-if="studentTouched.osobnyEmail && errors.osobnyEmail" class="error">{{ errors.osobnyEmail }}</p>
                                        <p v-if="backendErrors.email" class="error">{{ backendErrors.email }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- ========= Sekcia: Školské informácie ========= -->
                            <div class="form-section">
                                <h3 class="form-section__title">Školské informácie</h3>
                                <div class="form-section__divider" aria-hidden="true"></div>

                                <div class="form-grid form-grid--2">
                                    <!-- Študentský mail -->
                                    <div class="field">
                                        <label for="skolskyEmail" class="label label--required">Študentský mail</label>
                                        <input
                                            id="skolskyEmail"
                                            v-model.trim="student.skolskyEmail"
                                            class="input"
                                            type="email"
                                            placeholder="Študentský mail"
                                            required
                                            :aria-invalid="studentTouched.skolskyEmail && !!errors.skolskyEmail"
                                            @focus="onSTouch('skolskyEmail')"
                                            @blur="onSTouch('skolskyEmail', true)"
                                        />
                                        <p v-if="studentTouched.skolskyEmail && errors.skolskyEmail" class="error">{{ errors.skolskyEmail }}</p>
                                        <p v-if="backendErrors.student_email" class="error">{{ backendErrors.student_email }}</p>
                                    </div>

                                    <!-- Študijný odbor -->
                                    <div class="field">
                                        <label for="odbor" class="label label--required">Študijný odbor</label>
                                        <select
                                            id="odbor"
                                            v-model="student.odborId"
                                            class="input"
                                            required
                                            :aria-invalid="studentTouched.odborId && !!errors.odborId"
                                            @focus="onSTouch('odborId')"
                                            @blur="onSTouch('odborId', true)"
                                            @change="onSTouch('odborId', true)"
                                        >
                                            <option value="" disabled>
                                                {{ facultiesLoading ? 'Načítavam…' : (facultiesError ? 'Nedostupné' : 'Vyberte odbor') }}
                                            </option>
                                            <option
                                                v-for="f in faculties"
                                                :key="f.id"
                                                :value="f.id"
                                            >
                                                {{ f.name }}
                                            </option>
                                        </select>

                                        <p v-if="facultiesError" class="error" style="margin-top:6px;">
                                            {{ facultiesError }}
                                            <button
                                                type="button"
                                                @click="loadFaculties()"
                                                style="all:unset; text-decoration:underline; cursor:pointer;"
                                            >
                                                Skúsiť znova
                                            </button>
                                        </p>

                                        <p v-if="studentTouched.odborId && errors.odborId" class="error">{{ errors.odborId }}</p>
                                        <p v-if="backendErrors.faculty" class="error">{{ backendErrors.faculty }}</p>
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
                                        <label for="mesto" class="label label--required">Mesto</label>
                                        <input
                                            id="mesto"
                                            v-model.trim="student.mesto"
                                            class="input"
                                            type="text"
                                            placeholder="Mesto"
                                            required
                                            :aria-invalid="studentTouched.mesto && !!errors.mesto"
                                            @focus="onSTouch('mesto')"
                                            @blur="onSTouch('mesto', true)"
                                        />
                                        <p v-if="studentTouched.mesto && errors.mesto" class="error">{{ errors.mesto }}</p>
                                    </div>

                                    <!-- PSČ -->
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
                                            :aria-invalid="studentTouched.psc && !!errors.psc"
                                            @focus="onSTouch('psc')"
                                            @blur="onSTouch('psc', true)"
                                        />
                                        <p v-if="studentTouched.psc && errors.psc" class="error">{{ errors.psc }}</p>
                                    </div>
                                </div>

                                <div class="form-grid form-grid--2">
                                    <!-- Ulica -->
                                    <div class="field">
                                        <label for="ulica" class="label label--required">Ulica</label>
                                        <input
                                            id="ulica"
                                            v-model.trim="student.ulica"
                                            class="input"
                                            type="text"
                                            placeholder="Ulica"
                                            required
                                            :aria-invalid="studentTouched.ulica && !!errors.ulica"
                                            @focus="onSTouch('ulica')"
                                            @blur="onSTouch('ulica', true)"
                                        />
                                        <p v-if="studentTouched.ulica && errors.ulica" class="error">{{ errors.ulica }}</p>
                                    </div>

                                    <!-- č. domu -->
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
                                            :aria-invalid="studentTouched.cisloDomu && !!errors.cisloDomu"
                                            @focus="onSTouch('cisloDomu')"
                                            @blur="onSTouch('cisloDomu', true)"
                                        />
                                        <p v-if="studentTouched.cisloDomu && errors.cisloDomu" class="error">{{ errors.cisloDomu }}</p>
                                    </div>
                                </div>

                                <div class="form-grid form-grid--1">
                                    <!-- Krajina -->
                                    <div class="field">
                                        <label for="krajina" class="label label--required">Krajina</label>
                                        <select
                                            id="krajina"
                                            v-model="student.krajina"
                                            class="input"
                                            required
                                            :aria-invalid="studentTouched.krajina && !!errors.krajina"
                                            @focus="onSTouch('krajina')"
                                            @blur="onSTouch('krajina', true)"
                                            @change="onSTouch('krajina', true)"
                                        >
                                            <option value="" disabled>
                                                {{ countriesLoading ? 'Načítavam…' : (countriesError ? 'Nedostupné' : 'Krajina') }}
                                            </option>
                                            <option
                                                v-for="c in countries"
                                                :key="c.id"
                                                :value="c.id"
                                            >
                                                {{ c.icon ? c.icon + ' ' : '' }}{{ c.name }}
                                            </option>
                                        </select>

                                        <p v-if="countriesError" class="error" style="margin-top:6px;">
                                            {{ countriesError }}
                                            <button
                                                type="button"
                                                @click="loadCountries()"
                                                style="all:unset; text-decoration:underline; cursor:pointer;"
                                            >
                                                Skúsiť znova
                                            </button>
                                        </p>

                                        <p v-if="studentTouched.krajina && errors.krajina" class="error">{{ errors.krajina }}</p>
                                        <p v-if="backendErrors.country" class="error">{{ backendErrors.country }}</p>
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

                <!-- LOWER: CTA -->
                <div class="register-footer">
                    <button
                        type="button"
                        class="btn btn--primary"
                        :disabled="!isFormValid || isSubmittingStudent"
                        @click="submitStudent"
                    >
                        {{ isSubmittingStudent ? 'Odosielam…' : 'Registrácia' }}
                    </button>
                    <p class="hint footer-hint">
                        Už máte účet? <a href="/login">Prihláste sa!</a>
                    </p>
                </div>
            </div>
        </section>

        <!-- BODY: Company (reversed) -->
        <section
            id="reg-panel-company"
            role="tabpanel"
            :aria-hidden="activeTab !== 'company'"
            v-show="activeTab === 'company'"
            class="register-panel"
        >
            <div class="register-body">
                <div class="register-sides">
                    <!-- LEFT: text + divider + image -->
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
                                            @focus="onCTouch('titul')"
                                            @blur="onCTouch('titul', true)"
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
                                            :aria-invalid="companyTouched.meno && !!companyErrors.meno"
                                            @focus="onCTouch('meno')"
                                            @blur="onCTouch('meno', true)"
                                        />
                                        <p v-if="companyTouched.meno && companyErrors.meno" class="error">{{ companyErrors.meno }}</p>
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
                                            :aria-invalid="companyTouched.priezvisko && !!companyErrors.priezvisko"
                                            @focus="onCTouch('priezvisko')"
                                            @blur="onCTouch('priezvisko', true)"
                                        />
                                        <p v-if="companyTouched.priezvisko && companyErrors.priezvisko" class="error">{{ companyErrors.priezvisko }}</p>
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
                                            :aria-invalid="companyTouched.tel && !!companyErrors.tel"
                                            @focus="onCTouch('tel')"
                                            @blur="onCTouch('tel', true)"
                                        />
                                        <p v-if="companyTouched.tel && companyErrors.tel" class="error">{{ companyErrors.tel }}</p>
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
                                            :aria-invalid="companyTouched.osobnyEmail && !!companyErrors.osobnyEmail"
                                            @focus="onCTouch('osobnyEmail')"
                                            @blur="onCTouch('osobnyEmail', true)"
                                        />
                                        <p v-if="companyTouched.osobnyEmail && companyErrors.osobnyEmail" class="error">{{ companyErrors.osobnyEmail }}</p>
                                        <p v-if="companyBackendErrors.email" class="error">{{ companyBackendErrors.email }}</p>
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
                                            :aria-invalid="companyTouched.nazovFirmy && !!companyErrors.nazovFirmy"
                                            @focus="onCTouch('nazovFirmy')"
                                            @blur="onCTouch('nazovFirmy', true)"
                                        />
                                        <p v-if="companyTouched.nazovFirmy && companyErrors.nazovFirmy" class="error">{{ companyErrors.nazovFirmy }}</p>
                                        <p v-if="companyBackendErrors.company_name" class="error">{{ companyBackendErrors.company_name }}</p>
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
                                            :aria-invalid="companyTouched.rolaVoFirme && !!companyErrors.rolaVoFirme"
                                            @focus="onCTouch('rolaVoFirme')"
                                            @blur="onCTouch('rolaVoFirme', true)"
                                        />
                                        <p v-if="companyTouched.rolaVoFirme && companyErrors.rolaVoFirme" class="error">{{ companyErrors.rolaVoFirme }}</p>
                                    </div>
                                </div>

                                <!-- Popis -->
                                <div class="form-grid form-grid--1" style="margin-top: 12px;">
                                    <div class="field">
                                        <label for="c_popis" class="label">Popis</label>
                                        <textarea
                                            id="c_popis"
                                            v-model.trim="company.popis"
                                            class="input"
                                            placeholder="Stručný popis firmy, čomu sa venujete…"
                                            rows="4"
                                            @focus="onCTouch('popis')"
                                            @blur="onCTouch('popis', true)"
                                        ></textarea>
                                    </div>
                                </div>

                                <!-- Web -->
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
                                            :aria-invalid="companyTouched.web && !!companyErrors.web"
                                            @focus="onCTouch('web')"
                                            @blur="onCTouch('web', true)"
                                        />
                                        <p v-if="companyTouched.web && companyErrors.web" class="error">{{ companyErrors.web }}</p>
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
                                            :aria-invalid="companyTouched.mesto && !!companyErrors.mesto"
                                            @focus="onCTouch('mesto')"
                                            @blur="onCTouch('mesto', true)"
                                        />
                                        <p v-if="companyTouched.mesto && companyErrors.mesto" class="error">{{ companyErrors.mesto }}</p>
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
                                            :aria-invalid="companyTouched.psc && !!companyErrors.psc"
                                            @focus="onCTouch('psc')"
                                            @blur="onCTouch('psc', true)"
                                        />
                                        <p v-if="companyTouched.psc && companyErrors.psc" class="error">{{ companyErrors.psc }}</p>
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
                                            :aria-invalid="companyTouched.ulica && !!companyErrors.ulica"
                                            @focus="onCTouch('ulica')"
                                            @blur="onCTouch('ulica', true)"
                                        />
                                        <p v-if="companyTouched.ulica && companyErrors.ulica" class="error">{{ companyErrors.ulica }}</p>
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
                                            :aria-invalid="companyTouched.cisloDomu && !!companyErrors.cisloDomu"
                                            @focus="onCTouch('cisloDomu')"
                                            @blur="onCTouch('cisloDomu', true)"
                                        />
                                        <p v-if="companyTouched.cisloDomu && companyErrors.cisloDomu" class="error">{{ companyErrors.cisloDomu }}</p>
                                    </div>
                                </div>

                                <div class="form-grid form-grid--1">
                                    <!-- Krajina -->
                                    <div class="field">
                                        <label for="c_krajina" class="label label--required">Krajina</label>
                                        <select
                                            id="c_krajina"
                                            v-model="company.krajina"
                                            class="input"
                                            required
                                            :aria-invalid="companyTouched.krajina && !!companyErrors.krajina"
                                            @focus="onCTouch('krajina')"
                                            @blur="onCTouch('krajina', true)"
                                            @change="onCTouch('krajina', true)"
                                        >
                                            <option value="" disabled>
                                                {{ countriesLoading ? 'Načítavam…' : (countriesError ? 'Nedostupné' : 'Krajina') }}
                                            </option>
                                            <option
                                                v-for="c in countries"
                                                :key="c.id"
                                                :value="c.id"
                                            >
                                                {{ c.icon ? c.icon + ' ' : '' }}{{ c.name }}
                                            </option>
                                        </select>

                                        <p v-if="countriesError" class="error" style="margin-top:6px;">
                                            {{ countriesError }}
                                            <button
                                                type="button"
                                                @click="loadCountries()"
                                                style="all:unset; text-decoration:underline; cursor:pointer;"
                                            >
                                                Skúsiť znova
                                            </button>
                                        </p>

                                        <p v-if="companyTouched.krajina && companyErrors.krajina" class="error">{{ companyErrors.krajina }}</p>
                                        <p v-if="companyBackendErrors.country" class="error">{{ companyBackendErrors.country }}</p>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- LOWER: CTA -->
                <div class="register-footer">
                    <button
                        type="button"
                        class="btn btn--primary"
                        :disabled="!companyIsFormValid || isSubmittingCompany"
                        @click="submitCompany"
                    >
                        {{ isSubmittingCompany ? 'Odosielam…' : 'Registrácia' }}
                    </button>
                    <p class="hint footer-hint">Už máte účet? <a href="/login">Prihláste sa!</a></p>
                </div>
            </div>
        </section>
    </article>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onBeforeUnmount, nextTick, reactive } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

const router = useRouter()

const logoUrl = '/storage/registerform-student2.png'
const logoUrl2 = '/storage/registerformcompany.png'

/* Tabs */
const activeTab = ref<'student' | 'company'>('student')

/* Equalize tab widths on desktop */
const btn1 = ref<HTMLElement | null>(null)
const btn2 = ref<HTMLElement | null>(null)
const equalBtnWidth = ref('auto')
const tabStyle = computed(() => ({ width: equalBtnWidth.value }))
let raf = 0
const smallMQL = window.matchMedia('(max-width: 520px)')

async function setEqualBtnWidth() {
    if (!btn1.value || !btn2.value) return
    equalBtnWidth.value = 'auto'
    await nextTick()
    const max = Math.max(btn1.value?.offsetWidth ?? 0, btn2.value?.offsetWidth ?? 0)
    equalBtnWidth.value = `${max}px`
}
async function applyEqualize() {
    if (smallMQL.matches) equalBtnWidth.value = 'auto'
    else await setEqualBtnWidth()
}
function onResize() {
    cancelAnimationFrame(raf)
    raf = requestAnimationFrame(() => { void applyEqualize() })
}
function onMQChange() { void applyEqualize() }

/* Faculties (Študijný odbor) */
type Faculty = { id: number; name: string; active: number }
const faculties = ref<Faculty[]>([])
const facultiesLoading = ref(true)
const facultiesError = ref('')

async function loadFaculties() {
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

/* Countries (Krajiny) */
type Country = { id: number; name: string; icon?: string }
const countries = ref<Country[]>([])
const countriesLoading = ref(true)
const countriesError = ref('')

async function loadCountries() {
    countriesLoading.value = true
    countriesError.value = ''
    try {
        const { data } = await axios.get('/api/countries')
        countries.value = Array.isArray(data) ? data : []
    } catch (e) {
        countriesError.value = 'Nepodarilo sa načítať krajiny.'
    } finally {
        countriesLoading.value = false
    }
}

/* ---------------- Student model + validation ---------------- */
const student = reactive({
    titul: '',
    meno: '',
    priezvisko: '',
    tel: '',
    osobnyEmail: '',
    skolskyEmail: '',
    odborId: '' as any, // will hold number after select
    mesto: '',
    psc: '',
    ulica: '',
    cisloDomu: '',
    krajina: '' as any // will hold number after select
})

type StudentKeys =
    | 'meno' | 'priezvisko' | 'tel' | 'osobnyEmail' | 'skolskyEmail'
    | 'odborId' | 'mesto' | 'psc' | 'ulica' | 'cisloDomu' | 'krajina'

const studentTouched = reactive<Record<StudentKeys, boolean>>({
    meno: false,
    priezvisko: false,
    tel: false,
    osobnyEmail: false,
    skolskyEmail: false,
    odborId: false,
    mesto: false,
    psc: false,
    ulica: false,
    cisloDomu: false,
    krajina: false
})

function onSTouch<K extends StudentKeys>(key: K, blured = false) {
    if (!studentTouched[key]) studentTouched[key] = !!blured || studentTouched[key]
    if (blured) studentTouched[key] = true
}

const errors = computed(() => {
    const e: Record<string, string> = {}

    if (!student.meno.trim()) e.meno = 'Zadajte meno.'
    if (!student.priezvisko.trim()) e.priezvisko = 'Zadajte priezvisko.'

    const tel = student.tel.trim()
    const telOk = /^[0-9]+$/.test(tel) || /^\+421[0-9]+$/.test(tel)
    if (!telOk) e.tel = 'Zadajte len čísla alebo tvar +421…'

    if (!student.osobnyEmail.trim().includes('@')) e.osobnyEmail = 'E-mail musí obsahovať @'

    const se = student.skolskyEmail.trim()
    const studentMailOk = /^[^@\s]+@student\.ukf\.sk$/i.test(se)
    if (!studentMailOk) e.skolskyEmail = 'E-mail musí končiť @student.ukf.sk'

    if (student.odborId === '' || student.odborId === null || student.odborId === undefined) e.odborId = 'Vyberte študijný odbor.'

    if (!student.mesto.trim()) e.mesto = 'Zadajte mesto.'
    if (!/^\d+$/.test(student.psc.trim())) e.psc = 'PSČ môže obsahovať len čísla.'
    if (!student.ulica.trim()) e.ulica = 'Zadajte ulicu.'
    if (!/^\d+$/.test(student.cisloDomu.trim())) e.cisloDomu = 'Číslo domu môže obsahovať len čísla.'

    if (student.krajina === '' || student.krajina === null || student.krajina === undefined) e.krajina = 'Vyberte krajinu.'

    return e
})
const isFormValid = computed(() => Object.keys(errors.value).length === 0)

/* ---------------- Company model + blur-based validation ---------------- */
const company = reactive({
    titul: '',
    meno: '',
    priezvisko: '',
    tel: '',
    osobnyEmail: '',
    nazovFirmy: '',
    rolaVoFirme: '',
    popis: '',
    web: '',
    mesto: '',
    psc: '',
    ulica: '',
    cisloDomu: '',
    krajina: '' as any // number after select
})

type CompanyKeys =
    | 'titul' | 'meno' | 'priezvisko' | 'tel' | 'osobnyEmail'
    | 'nazovFirmy' | 'rolaVoFirme' | 'popis' | 'web'
    | 'mesto' | 'psc' | 'ulica' | 'cisloDomu' | 'krajina'

const companyTouched = reactive<Record<CompanyKeys, boolean>>({
    titul: false,
    meno: false,
    priezvisko: false,
    tel: false,
    osobnyEmail: false,
    nazovFirmy: false,
    rolaVoFirme: false,
    popis: false,
    web: false,
    mesto: false,
    psc: false,
    ulica: false,
    cisloDomu: false,
    krajina: false
})

function onCTouch<K extends CompanyKeys>(key: K, blured = false) {
    if (!companyTouched[key]) companyTouched[key] = !!blured || companyTouched[key]
    if (blured) companyTouched[key] = true
}

const companyErrors = computed(() => {
    const e: Partial<Record<CompanyKeys, string>> = {}

    if (!company.meno.trim()) e.meno = 'Zadajte meno.'
    if (!company.priezvisko.trim()) e.priezvisko = 'Zadajte priezvisko.'

    const tel = company.tel.trim()
    const telOk = /^[0-9]+$/.test(tel) || /^\+421[0-9]+$/.test(tel)
    if (!telOk) e.tel = 'Zadajte len čísla alebo tvar +421…'

    if (!company.osobnyEmail.trim().includes('@')) e.osobnyEmail = 'E-mail musí obsahovať @'

    if (!company.nazovFirmy.trim()) e.nazovFirmy = 'Zadajte názov firmy.'
    if (!company.rolaVoFirme.trim()) e.rolaVoFirme = 'Zadajte rolu vo firme.'

    if (company.web.trim() && !/www\./i.test(company.web.trim())) e.web = 'Adresa webu musí obsahovať "www".'

    if (!company.mesto.trim()) e.mesto = 'Zadajte mesto.'
    if (!/^\d+$/.test(company.psc.trim())) e.psc = 'PSČ môže obsahovať len čísla.'
    if (!company.ulica.trim()) e.ulica = 'Zadajte ulicu.'
    if (!/^\d+$/.test(company.cisloDomu.trim())) e.cisloDomu = 'Číslo domu môže obsahovať len čísla.'

    if (company.krajina === '' || company.krajina === null || company.krajina === undefined) e.krajina = 'Vyberte krajinu.'

    return e
})

const companyIsFormValid = computed(() => Object.keys(companyErrors.value).length === 0)

/* -------- Backend error holders & submission state -------- */
const backendErrors = reactive<{ [key: string]: string }>({})
const companyBackendErrors = reactive<{ [key: string]: string }>({})

const isSubmittingStudent = ref(false)
const isSubmittingCompany = ref(false)

/* ---------------- Student registration ---------------- */
async function submitStudent() {
    Object.keys(backendErrors).forEach(k => delete backendErrors[k])
    if (!isFormValid.value) return

    isSubmittingStudent.value = true
    try {
        const payload = {
            form_type: 'student_form',
            first_name: student.meno,
            last_name: student.priezvisko,
            title_before: student.titul || null,
            email: student.osobnyEmail,
            phone_number: student.tel,
            city: student.mesto,
            street: student.ulica,
            house_number: Number(student.cisloDomu),
            postal_code: student.psc,
            country: Number(student.krajina),
            student_email: student.skolskyEmail,
            faculty: Number(student.odborId)
        }

        const res = await axios.post('/api/register', payload)
        if (res.status === 201) {
            await router.push({ path: '/register/sent', query: { type: 'student' } })
        }
    } catch (err: any) {
        if (err.response?.status === 422 && err.response.data.errors) {
            Object.entries(err.response.data.errors).forEach(([key, messages]) => {
                backendErrors[key] = (messages as string[])[0]
            })
        }
    } finally {
        isSubmittingStudent.value = false
    }
}

/* ---------------- Company registration ---------------- */
async function submitCompany() {
    Object.keys(companyBackendErrors).forEach(k => delete companyBackendErrors[k])
    if (!companyIsFormValid.value) return

    isSubmittingCompany.value = true
    try {
        const payload = {
            form_type: 'company_form',
            first_name: company.meno,
            last_name: company.priezvisko,
            title_before: company.titul || null,
            title_after: null,
            email: company.osobnyEmail,
            phone_number: company.tel,
            city: company.mesto,
            street: company.ulica,
            house_number: Number(company.cisloDomu),
            postal_code: company.psc,
            country: Number(company.krajina),
            company_name: company.nazovFirmy,
            role_at_company: company.rolaVoFirme || null,
            description: company.popis || null,
            website: company.web || null
        }

        const res = await axios.post('/api/register', payload)
        if (res.status === 201) {
            await router.push({ path: '/register/sent', query: { type: 'company' } })
        }
    } catch (err: any) {
        if (err.response?.status === 422 && err.response.data.errors) {
            Object.entries(err.response.data.errors).forEach(([key, messages]) => {
                companyBackendErrors[key] = (messages as string[])[0]
            })
        }
    } finally {
        isSubmittingCompany.value = false
    }
}

/* Lifecycle */
onMounted(async () => {
    await nextTick()
    await applyEqualize()
    window.addEventListener('resize', onResize)
    smallMQL.addEventListener('change', onMQChange)

    await Promise.all([loadFaculties(), loadCountries()])
})
onBeforeUnmount(() => {
    window.removeEventListener('resize', onResize)
    smallMQL.removeEventListener('change', onMQChange)
})
</script>

