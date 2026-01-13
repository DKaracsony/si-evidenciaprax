<template>
    <section class="edit-prax-form">
        <header class="edit-prax-form__header">
            <h2 class="edit-prax-form__title">
                Editácia návrhu odbornej praxe
            </h2>
            <div class="student-prax-list__divider edit-prax-form__divider"></div>
        </header>

        <form @submit.prevent="submit(false)" novalidate>
            <!-- Firma -->
            <div class="edit-prax-form__field">
                <CompanyAutocomplete
                    v-model="form.company"
                    label="Firma"
                    :required="false"
                    :error="errors.company"
                />

                <div
                    v-if="isLoadingCompanyDetail"
                    class="company-summary-card company-summary-card--loading"
                >
                    Načítavam informácie o firme...
                </div>

                <div
                    v-else-if="selectedCompanyDetail"
                    class="company-summary-card"
                >
                    <h3 class="company-summary-card__name">
                        {{ selectedCompanyDetail.name }}
                    </h3>

                    <p
                        v-if="selectedCompanyDetail.description"
                        class="company-summary-card__description"
                    >
                        {{ selectedCompanyDetail.description }}
                    </p>

                    <p
                        v-if="selectedCompanyDetail.website"
                        class="company-summary-card__website"
                    >
                        Web:
                        <a
                            :href="selectedCompanyDetail.website"
                            target="_blank"
                            rel="noopener noreferrer"
                        >
                            {{ selectedCompanyDetail.website }}
                        </a>
                    </p>

                    <p
                        v-if="selectedCompanyDetail.address"
                        class="company-summary-card__address"
                    >
                        {{ selectedCompanyDetail.address.street }}
                        {{ selectedCompanyDetail.address.house_number }},
                        {{ selectedCompanyDetail.address.postal_code }}
                        {{ selectedCompanyDetail.address.city }}
                        <span v-if="selectedCompanyDetail.address.country">
                            , {{ selectedCompanyDetail.address.country.name }}
                        </span>
                    </p>

                    <p
                        v-if="contactPerson"
                        class="company-summary-card__contact"
                    >
                        Kontakt:
                        {{ contactPerson.title_before }}
                        {{ contactPerson.first_name }}
                        {{ contactPerson.last_name }}
                        {{ contactPerson.title_after }}
                        <span v-if="contactPerson.email">
                            &nbsp;– {{ contactPerson.email }}
                        </span>
                    </p>
                </div>
            </div>

            <!-- Dátumy -->
            <div class="edit-prax-form__field edit-prax-form__field--row">
                <div class="edit-prax-form__field-col">
                    <label class="edit-prax-form__label">
                        Začiatok praxe <span class="edit-prax-form__required">*</span>
                    </label>
                    <input
                        type="date"
                        v-model="form.startDate"
                        class="edit-prax-form__input"
                        :class="{ 'edit-prax-form__input--error': errors.startDate }"
                    />
                    <p v-if="errors.startDate" class="edit-prax-form__error">
                        {{ errors.startDate }}
                    </p>
                </div>

                <div class="edit-prax-form__field-col">
                    <label class="edit-prax-form__label">
                        Koniec praxe <span class="edit-prax-form__required">*</span>
                    </label>
                    <input
                        type="date"
                        v-model="form.endDate"
                        class="edit-prax-form__input"
                        :class="{ 'edit-prax-form__input--error': errors.endDate }"
                    />
                    <p v-if="errors.endDate" class="edit-prax-form__error">
                        {{ errors.endDate }}
                    </p>
                </div>
            </div>

            <!-- Akademický rok -->
            <div class="edit-prax-form__field">
                <label class="edit-prax-form__label">
                    Akademický rok / semester <span class="edit-prax-form__required">*</span>
                </label>
                <select
                    v-model="form.academicYearId"
                    class="edit-prax-form__select"
                    :class="{ 'edit-prax-form__input--error': errors.academicYearId }"
                >
                    <option value="">Vyberte možnosť</option>
                    <option
                        v-for="year in academicYears"
                        :key="year.id"
                        :value="year.id"
                    >
                        {{ formatAcademicYearOption(year) }}
                    </option>
                </select>
                <p v-if="errors.academicYearId" class="edit-prax-form__error">
                    {{ errors.academicYearId }}
                </p>
            </div>

            <!-- Popis -->
            <div class="edit-prax-form__field">
                <label class="edit-prax-form__label">
                    Popis praxe <span class="edit-prax-form__required">*</span>
                </label>
                <textarea
                    v-model="form.description"
                    rows="5"
                    class="edit-prax-form__textarea"
                    :class="{ 'edit-prax-form__input--error': errors.description }"
                ></textarea>
                <p v-if="errors.description" class="edit-prax-form__error">
                    {{ errors.description }}
                </p>
            </div>

            <!-- Global error -->
            <p v-if="globalError" class="edit-prax-form__error edit-prax-form__error--global">
                {{ globalError }}
            </p>

            <!-- Actions -->
            <div class="edit-prax-form__actions">
                <button
                    type="button"
                    class="edit-prax-form__button"
                    :disabled="isSubmitting"
                    @click="submit(true)"
                >
                    <span v-if="isSubmitting && submitMode === 'draft'">Ukladám...</span>
                    <span v-else>Uložiť návrh</span>
                </button>

                <button
                    type="submit"
                    class="edit-prax-form__button"
                    :disabled="isSubmitting"
                >
                    <span v-if="isSubmitting && submitMode === 'final'">Odosielam...</span>
                    <span v-else>Odoslať prax</span>
                </button>

                <button
                    type="button"
                    class="edit-prax-form__button"
                    :disabled="isSubmitting"
                    @click="$emit('cancel')"
                >
                    Zrušiť
                </button>
            </div>
        </form>
    </section>
</template>


<script setup>
import { reactive, ref, onMounted, watch, computed } from 'vue';
import { fetchAcademicYears } from '@/services/academicYear';
import { createOrUpdateStudentInternship } from '@/services/internship';
import { useInternshipStore } from '@/stores/internship';
import { fetchCompanyDetail } from '@/services/company';
import CompanyAutocomplete from '@/components/Dashboard/General/CompanyAutoComplete.vue';

const props = defineProps({
    internship: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits(['created', 'cancel']);

const internshipStore = useInternshipStore();

/**
 * @typedef {Object} AcademicYearOption
 * @property {number|string} id
 * @property {string} [name]
 * @property {string} [season]
 * @property {string} [start_date]
 * @property {string} [end_date]
 */

/** @type {import('vue').Ref<AcademicYearOption[]>} */
const academicYears = ref([]);

const isSubmitting = ref(false);
const submitMode = ref(null); // 'draft' | 'final'
const globalError = ref('');

// detail vybratej firmy
const selectedCompanyDetail = ref(null);
const isLoadingCompanyDetail = ref(false);

const contactPerson = computed(() =>
    selectedCompanyDetail.value
        ? selectedCompanyDetail.value['contact_person'] ?? null
        : null
);

const form = reactive({
    company: null, // { id, name }
    startDate: '',
    endDate: '',
    academicYearId: '',
    description: '',
});

/**
 * @type {{
 *   company: string;
 *   startDate: string;
 *   endDate: string;
 *   academicYearId: string;
 *   description: string;
 * }}
 */
const errors = reactive({
    company: '',
    startDate: '',
    endDate: '',
    academicYearId: '',
    description: '',
});

const editingInternshipId = ref(props.internship?.id ?? null);

function hydrateFormFromInternship(internship) {
    if (!internship) return;

    form.company = internship.company
        ? { id: internship.company.id, name: internship.company.name }
        : null;

    form.startDate = internship.start_date ?? '';
    form.endDate = internship.date_to ?? '';
    form.academicYearId = internship.semester?.id ?? '';
    form.description = internship.description ?? '';
}

onMounted(async () => {
    try {
        academicYears.value = await fetchAcademicYears();
    } catch (e) {
        console.error('[EditPraxForm] Failed to load academic years', e);
        globalError.value = 'Nepodarilo sa načítať zoznam akademických rokov.';
    }

    hydrateFormFromInternship(props.internship);
});

watch(
    () => props.internship,
    (value) => {
        editingInternshipId.value = value?.id ?? null;
        hydrateFormFromInternship(value);
    }
);

// keď sa zmení vybraná firma, dotiahneme detail z /api/companies/{id}
watch(
    () => form.company,
    async (company) => {
        selectedCompanyDetail.value = null;

        if (!company || !company['id']) {
            return;
        }

        try {
            isLoadingCompanyDetail.value = true;
            selectedCompanyDetail.value = await fetchCompanyDetail(company['id']);
        } catch (e) {
            console.error('[EditPraxForm] Failed to load company detail', e);
        } finally {
            isLoadingCompanyDetail.value = false;
        }
    }
);


function resetErrors() {
    errors.company = '';
    errors.startDate = '';
    errors.endDate = '';
    errors.academicYearId = '';
    errors.description = '';
    globalError.value = '';
}

function validateForFinalSubmit() {
    resetErrors();
    let ok = true;

    if (!form.company) {
        errors.company = 'Zvoľte firmu.';
        ok = false;
    }
    if (!form.startDate) {
        errors.startDate = 'Zadajte dátum začiatku praxe.';
        ok = false;
    }
    if (!form.endDate) {
        errors.endDate = 'Zadajte dátum ukončenia praxe.';
        ok = false;
    } else if (form.startDate && form.endDate < form.startDate) {
        errors.endDate = 'Dátum ukončenia musí byť neskôr alebo rovnaký ako začiatok.';
        ok = false;
    }
    if (!form.academicYearId) {
        errors.academicYearId = 'Vyberte akademický rok / semester.';
        ok = false;
    }

    const description = (form.description ?? '').toString().trim();
    if (!description || description.length < 10) {
        errors.description = 'Popis by mal mať aspoň 10 znakov.';
        ok = false;
    }

    return ok;
}

async function submit(asDraft) {
    submitMode.value = asDraft ? 'draft' : 'final';
    globalError.value = '';

    if (!asDraft && !validateForFinalSubmit()) {
        return;
    }

    isSubmitting.value = true;

    const payload = {
        is_draft: asDraft,
        start_date: form.startDate || null,
        date_to: form.endDate || null,
        description: form.description || null,
        company_id: form.company?.id || null,
        academic_year_id: form.academicYearId || null,
        internship_id: editingInternshipId.value || null,
    };

    try {
        const data = await createOrUpdateStudentInternship(payload);
        const internship = data.internship ?? null;

        if (internship) {
            internshipStore.upsertInternship(internship);
        }

        emit('created', { internship, isDraft: asDraft, raw: data });
    } catch (error) {
        console.error('[EditPraxForm] Failed to submit internship', error);

        if (
            error.response?.status === 422 &&
            error.response.data &&
            error.response.data['errors']
        ) {
            const backendErrors = error.response.data['errors'] || {};

            if (backendErrors['start_date']?.[0]) {
                errors.startDate = backendErrors['start_date'][0];
            }
            if (backendErrors['date_to']?.[0]) {
                errors.endDate = backendErrors['date_to'][0];
            }
            if (backendErrors['description']?.[0]) {
                errors.description = backendErrors['description'][0];
            }
            if (backendErrors['company_id']?.[0]) {
                errors.company = backendErrors['company_id'][0];
            }
            if (backendErrors['academic_year_id']?.[0]) {
                errors.academicYearId = backendErrors['academic_year_id'][0];
            }
        } else {
            globalError.value = 'Pri ukladaní praxe došlo k chybe. Skúste to znova.';
        }
    } finally {
        isSubmitting.value = false;
    }
}

function formatAcademicYearOption(year) {
    const start = year['start_date']
        ? new Date(year['start_date']).getFullYear()
        : null;
    const end = year['end_date']
        ? new Date(year['end_date']).getFullYear()
        : null;

    const range =
        start && end && start !== end
            ? `${start}/${String(end).slice(-2)}`
            : start || '';

    const seasonLabel = year['season'];

    if (range && seasonLabel) {
        return `${range} – ${seasonLabel}`;
    }
    if (range) return range;
    return year.name || seasonLabel || `ID ${year['id']}`;
}
</script>
