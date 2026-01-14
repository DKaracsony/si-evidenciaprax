<template>
    <div class="garant-prax-edit-modal__overlay">
        <div class="garant-prax-edit-modal">
            <!-- HEADER -->
            <header class="garant-prax-edit-modal__header">
                <h2 class="garant-prax-edit-modal__title">
                    Úprava odbornej praxe
                </h2>

                <button
                    type="button"
                    class="garant-prax-edit-modal__close"
                    aria-label="Zavrieť úpravu"
                    @click="emit('close')"
                >
                    ✕
                </button>
            </header>

            <div class="garant-prax-list__divider"></div>

            <!-- LOADING -->
            <div v-if="loading" class="garant-prax-edit-modal__state">
                Načítavam údaje…
            </div>

            <!-- ERROR -->
            <div
                v-else-if="error"
                class="garant-prax-edit-modal__state garant-prax-edit-modal__state--error"
            >
                {{ error }}
            </div>

            <!-- FORM -->
            <form
                v-else
                class="garant-prax-edit-modal__content"
                @submit.prevent="submit"
            >
                <!-- STUDENT -->
                <section class="garant-prax-edit-modal__section">
                    <h3 class="garant-prax-edit-modal__section-title">
                        Študent
                    </h3>

                    <StudentAutocomplete
                        v-model="form.student"
                        label="Študent"
                        placeholder="Vyhľadať študenta…"
                        :error="errors.student_profile_id"
                    />

                    <p
                        v-if="form.student"
                        class="garant-prax-edit-modal__selected"
                    >
                        Vybraný študent: {{ form.student.label }}
                    </p>


                    <p v-if="errors.student_profile_id" class="garant-prax-edit-modal__error">
                        {{ errors.student_profile_id }}
                    </p>
                </section>

                <!-- COMPANY -->
                <section class="garant-prax-edit-modal__section">
                    <h3 class="garant-prax-edit-modal__section-title">
                        Firma
                    </h3>

                    <CompanyAutocomplete
                        v-model="form.company"
                        label="Firma"
                        placeholder="Vyhľadať firmu…"
                        :error="errors.company_id"
                    />

                    <p
                        v-if="form.company"
                        class="garant-prax-edit-modal__selected"
                    >
                        Vybraná firma: {{ form.company.name }}
                    </p>


                    <p v-if="errors.company_id" class="garant-prax-edit-modal__error">
                        {{ errors.company_id }}
                    </p>
                </section>

                <!-- SEMESTER -->
                <section class="garant-prax-edit-modal__section">
                    <h3 class="garant-prax-edit-modal__section-title">
                        Semester
                    </h3>

                    <select
                        class="garant-prax-edit-modal__select"
                        v-model="form.academic_year_id"
                    >
                        <option disabled value="">Vyberte semester</option>
                        <option
                            v-for="y in academicYears"
                            :key="y.id"
                            :value="y.id"
                        >
                            {{ formatAcademicYear(y) }}
                        </option>
                    </select>

                    <p v-if="errors.academic_year_id" class="garant-prax-edit-modal__error">
                        {{ errors.academic_year_id }}
                    </p>
                </section>

                <!-- DATES -->
                <section class="garant-prax-edit-modal__section">
                    <h3 class="garant-prax-edit-modal__section-title">
                        Termín praxe
                    </h3>

                    <div class="garant-prax-edit-modal__dates">
                        <input
                            type="date"
                            class="garant-prax-edit-modal__input"
                            v-model="form.start_date"
                        />

                        <input
                            type="date"
                            class="garant-prax-edit-modal__input"
                            v-model="form.date_to"
                        />
                    </div>

                    <p v-if="errors.start_date" class="garant-prax-edit-modal__error">
                        {{ errors.start_date }}
                    </p>
                    <p v-if="errors.date_to" class="garant-prax-edit-modal__error">
                        {{ errors.date_to }}
                    </p>
                </section>

                <!-- DESCRIPTION -->
                <section class="garant-prax-edit-modal__section">
                    <h3 class="garant-prax-edit-modal__section-title">
                        Popis
                    </h3>

                    <textarea
                        class="garant-prax-edit-modal__textarea"
                        v-model="form.description"
                        rows="4"
                    />

                    <p v-if="errors.description" class="garant-prax-edit-modal__error">
                        {{ errors.description }}
                    </p>
                </section>

                <!-- ACTIONS -->
                <section class="garant-prax-edit-modal__actions">
                    <button
                        type="button"
                        class="garant-prax-edit-modal__button"
                        @click="emit('close')"
                    >
                        Zavrieť
                    </button>

                    <button
                        type="submit"
                        class="garant-prax-edit-modal__button"
                        :disabled="saving"
                    >
                        Uložiť zmeny
                    </button>
                </section>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import axios from 'axios';

import CompanyAutocomplete from '@/components/Dashboard/General/CompanyAutoComplete.vue';
import StudentAutocomplete from '@/components/Dashboard/General/StudentAutocomplete.vue';

const props = defineProps({
    internship: { type: Object, required: true },
});

const emit = defineEmits(['close', 'updated']);

const loading = ref(true);
const saving = ref(false);
const error = ref('');
const errors = reactive({});

/* FORM (OBJECT-BASED, like NewPraxForm) */
const form = reactive({
    company: null,
    student: null,
    academic_year_id: '',
    start_date: '',
    date_to: '',
    description: '',
});

/* ACADEMIC YEARS */
const academicYears = ref([]);

function formatAcademicYear(y) {
  const s = new Date(y.start_date).getFullYear();
  const e = new Date(y.end_date).getFullYear();

  const seasonRaw = (y.season || '').toLowerCase().trim();

  let season;
  if (seasonRaw === 'winter' || seasonRaw === 'zimny') {
    season = 'Zimný';
  } else if (seasonRaw === 'summer' || seasonRaw === 'letny') {
    season = 'Letný';
  } else {
    season = seasonRaw;
  }

  return `${s}/${e} – ${season}`;
}


/* SUBMIT */
async function submit() {
    saving.value = true;
    Object.keys(errors).forEach(k => (errors[k] = ''));
    error.value = '';

    try {
        const payload = {
            company_id: form.company?.id ?? null,
            student_profile_id: form.student?.id ?? null,
            academic_year_id: form.academic_year_id || null,
            start_date: form.start_date || null,
            date_to: form.date_to || null,
            description: form.description || null,
        };

        const res = await axios.put(
            `/api/internship/${props.internship.id}`,
            payload
        );

        emit('updated', res.data.internship);
        emit('close');
    } catch (e) {
        if (e.response?.status === 422) {
            Object.assign(errors, e.response.data.errors ?? {});
        } else {
            error.value = 'Uloženie zmien zlyhalo.';
        }
    } finally {
        saving.value = false;
    }
}

/* LOAD + PRELOAD */
onMounted(async () => {
    const res = await axios.get('/api/academic-years');
    academicYears.value = res.data.data ?? res.data ?? [];

    /* ✅ PRELOAD COMPANY */
    if (props.internship.company) {
        form.company = {
            id: props.internship.company.id,
            name: props.internship.company.name,
        };
    }

    /* ✅ PRELOAD STUDENT */
    if (props.internship.student) {
        form.student = {
            id: props.internship.student.id,
            label: [
                props.internship.student.title_before,
                props.internship.student.first_name,
                props.internship.student.last_name,
            ].filter(Boolean).join(' '),
        };
    }

    /* ✅ FETCH FULL INTERNSHIP DETAIL (for description) */
    try {
        const detail = await axios.get(
            `/api/internship/${props.internship.id}`
        );

        const data = detail.data?.internship ?? detail.data ?? {};

        form.description = props.internship.description ?? '';
        form.start_date = data.start_date ?? props.internship.start_date ?? '';
        form.date_to = data.date_to ?? props.internship.end_date ?? '';
        form.academic_year_id =
            data.semester?.id ??
            props.internship.semester?.id ??
            '';
    } catch (e) {
        console.error('[GarantPraxEditModal] Failed to load detail', e);
        // non-blocking: modal still opens, just without description
    }

    loading.value = false;
});

</script>

