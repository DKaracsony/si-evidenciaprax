<template>
    <section class="garant-prax-list">
        <!-- ===================== DETAIL MODAL ===================== -->
                <GarantPraxDetailModal
                        v-if="isDetailOpen && selectedInternship"
                        :internship="selectedInternship"
                    @close="closeDetail"
                />

        <!-- ===================== EDIT MODAL ===================== -->
        <GarantPraxEditModal
            v-if="isEditOpen && selectedInternship"
            :internship="selectedInternship"
            @close="closeEdit"
            @updated="onInternshipUpdated"
        />

        <!-- HEADER -->
        <header class="garant-prax-list__header">
            <h2 class="garant-prax-list__title">
                Zoznam všetkých odborných praxí
            </h2>

            <div class="garant-prax-list__header-actions">
                <button
                    type="button"
                    class="garant-prax-list__export-btn"
                    :disabled="isExporting"
                    @click="exportCsv"
                >
                    {{ isExporting ? 'Exportujem…' : 'Export do CSV' }}
                </button>

                <button
                    type="button"
                    class="garant-prax-list__stats-btn"
                    aria-label="Štatistiky praxí"
                    @click="emit('open-statistics')"
                >
                    <img
                        class="garant-prax-list__stats-icon"
                        :src="iconPath('Statistics.svg')"
                        data-name="Statistics.svg"
                        data-base-index="0"
                        alt=""
                        @error="onImgError"
                    />
                </button>
            </div>
        </header>


        <div class="garant-prax-list__divider"></div>

        <!-- TOAST (SUCCESS / ERROR) -->
        <div
            v-if="toast.visible"
            class="garant-prax-list__toast"
            :class="{
        'garant-prax-list__toast--success': toast.type === 'success',
        'garant-prax-list__toast--error': toast.type === 'error',
    }"
            role="status"
            aria-live="polite"
        >
            <span class="garant-prax-list__toast-text">{{ toast.message }}</span>

            <button
                type="button"
                class="garant-prax-list__toast-close"
                aria-label="Zavrieť hlášku"
                @click="hideToast"
            >
                ×
            </button>
        </div>


        <!-- LOADING -->
        <p v-if="loading" class="garant-prax-list__state">
            Načítavam praxe…
        </p>

        <!-- ERROR -->
        <p
            v-else-if="error"
            class="garant-prax-list__state garant-prax-list__state--error"
        >
            Nepodarilo sa načítať zoznam praxí.
        </p>

        <!-- LIST -->
        <div v-else>
            <!-- FILTER BAR -->
            <div class="garant-prax-list__filters">
                <!-- FILTER ICON -->
                <img
                    class="garant-prax-list__filters-icon"
                    :src="iconPath('filter.svg')"
                    data-name="filter.svg"
                    data-base-index="0"
                    alt=""
                    @error="onImgError"
                />

                <!-- ACADEMIC YEAR -->
                <div class="garant-prax-list__filter" ref="yearFilterRef">
                    <button
                        class="garant-prax-list__filter-btn"
                        :class="{ 'is-active': filters.academic_year_ids.length }"
                        @click.stop="toggleYear"
                    >
                        Akademický rok
                        <span v-if="filters.academic_year_ids.length">
                            ({{ filters.academic_year_ids.length }})
                        </span>
                    </button>

                    <div v-if="showYear" class="garant-prax-list__filter-dropdown">
                        <label v-for="year in academicYears" :key="year.id">
                            <input
                                type="checkbox"
                                :value="year.id"
                                v-model="filters.academic_year_ids"
                            />
                            {{ formatAcademicYearLabel(year) }}
                        </label>
                    </div>
                </div>

                <!-- SEMESTER -->
                <div class="garant-prax-list__filter" ref="semesterFilterRef">
                    <button
                        class="garant-prax-list__filter-btn"
                        :class="{ 'is-active': filters.seasons.length }"
                        @click.stop="toggleSemester"
                    >
                        Semester
                        <span v-if="filters.seasons.length">
                            ({{ filters.seasons.length }})
                        </span>
                    </button>

                    <div v-if="showSemester" class="garant-prax-list__filter-dropdown">
                        <label v-for="s in semesterOptions" :key="s.value">
                            <input
                                type="checkbox"
                                :value="s.value"
                                v-model="filters.seasons"
                            />
                            {{ s.label }}
                        </label>
                    </div>
                </div>

                <!-- FACULTY -->
                <div class="garant-prax-list__filter" ref="facultyFilterRef">
                    <button
                        class="garant-prax-list__filter-btn"
                        :class="{ 'is-active': filters.faculty_ids.length }"
                        @click.stop="toggleFaculty"
                    >
                        Fakulta
                        <span v-if="filters.faculty_ids.length">
                            ({{ filters.faculty_ids.length }})
                        </span>
                    </button>

                    <div v-if="showFaculty" class="garant-prax-list__filter-dropdown">
                        <p v-if="optionsLoaded && !faculties.length" class="garant-prax-list__filter-empty">
                            Žiadne fakulty…
                        </p>

                        <label v-for="f in faculties" :key="f.id">
                            <input
                                type="checkbox"
                                :value="f.id"
                                v-model="filters.faculty_ids"
                            />
                            {{ f.name }}
                        </label>
                    </div>
                </div>

                <!-- COMPANY -->
                <div class="garant-prax-list__filter" ref="companyFilterRef">
                    <button
                        class="garant-prax-list__filter-btn"
                        :class="{ 'is-active': filters.company_ids.length }"
                        @click.stop="toggleCompany"
                    >
                        Firma
                        <span v-if="filters.company_ids.length">
                            ({{ filters.company_ids.length }})
                        </span>
                    </button>

                    <div v-if="showCompany" class="garant-prax-list__filter-dropdown">
                        <p v-if="optionsLoaded && !companiesForFilter.length" class="garant-prax-list__filter-empty">
                            Žiadne firmy…
                        </p>

                        <label v-for="c in companiesForFilter" :key="c.id">
                            <input
                                type="checkbox"
                                :value="c.id"
                                v-model="filters.company_ids"
                            />
                            {{ c.name }}
                        </label>
                    </div>
                </div>

                <!-- STATUS -->
                <div class="garant-prax-list__filter" ref="statusFilterRef">
                    <button
                        class="garant-prax-list__filter-btn"
                        :class="{ 'is-active': filters.status_names.length }"
                        @click.stop="toggleStatus"
                    >
                        Stav
                        <span v-if="filters.status_names.length">
            ({{ filters.status_names.length }})
        </span>
                    </button>

                    <div v-if="showStatus" class="garant-prax-list__filter-dropdown">
                        <p v-if="optionsLoaded && !statusesForFilter.length" class="garant-prax-list__filter-empty">
                            Žiadne stavy…
                        </p>

                        <label v-for="s in statusesForFilter" :key="s">
                            <input
                                type="checkbox"
                                :value="s"
                                v-model="filters.status_names"
                            />
                            {{ s }}
                        </label>
                    </div>
                </div>


                <!-- STUDENT -->
                <div class="garant-prax-list__filter" ref="studentFilterRef">
                    <button
                        class="garant-prax-list__filter-btn"
                        :class="{ 'is-active': filters.student_ids.length }"
                        @click.stop="toggleStudent"
                    >
                        Študent
                        <span v-if="filters.student_ids.length">
                            ({{ filters.student_ids.length }})
                        </span>
                    </button>

                    <div v-if="showStudent" class="garant-prax-list__filter-dropdown">
                        <p v-if="optionsLoaded && !studentsForFilter.length" class="garant-prax-list__filter-empty">
                            Žiadni študenti…
                        </p>

                        <label v-for="s in studentsForFilter" :key="s.id">
                            <input
                                type="checkbox"
                                :value="s.id"
                                v-model="filters.student_ids"
                            />
                            {{ s.label }}
                        </label>
                    </div>
                </div>

                <!-- PER PAGE -->
                <label class="garant-prax-list__per-page">
                    <span>Počet záznamov:</span>
                    <select v-model.number="meta.per_page">
                        <option
                            v-for="opt in perPageOptions"
                            :key="opt"
                            :value="opt"
                        >
                            {{ opt }}
                        </option>
                    </select>
                </label>
            </div>

            <!-- BULK STATUS ACTIONS -->
            <div
                v-if="selectedIds.length"
                class="garant-prax-list__bulk-actions"
            >

    <span class="garant-prax-list__bulk-info">
        Vybrané praxe: <strong>{{ selectedIds.length }}</strong>
    </span>

                <textarea
                    v-model="statusNote"
                    placeholder="Poznámka (voliteľné)"
                />

                <p
                    v-if="hasMixedStatuses"
                    class="garant-prax-list__bulk-warning"
                >
                    Vybrané praxe majú rôzne stavy. Hromadná zmena stavu nie je možná.
                </p>


                <div class="garant-prax-list__bulk-buttons">
                    <button
                        v-if="canApprove"
                        class="garant-prax-list__cta-button"
                        :disabled="isStatusChanging"
                        @click="changeStatus('approval', true)"
                    >
                        Schváliť
                    </button>

                    <button
                        v-if="canDefend"
                        class="garant-prax-list__cta-button"
                        :disabled="isStatusChanging"
                        @click="changeStatus('defense', true)"
                    >
                        Obhájená
                    </button>

                    <button
                        v-if="canUndefend"
                        class="garant-prax-list__cta-button garant-prax-list__cta-button--danger"
                        :disabled="isStatusChanging"
                        @click="changeStatus('defense', false)"
                    >
                        Neobhájená
                    </button>

                </div>

            </div>


            <!-- TABLE -->
            <div class="garant-prax-list__table">
                <div class="garant-prax-list__row garant-prax-list__row--head">
                    <input type="checkbox" :checked="allSelected" @change="toggleSelectAll" />
                    <span>Študent</span>
                    <span>Fakulta</span>
                    <span>Firma</span>
                    <span>Stav</span>
                    <span>Akademický rok</span>
                    <span>Semester</span>
                    <span>Akcie</span>
                </div>

                <div
                    v-for="item in items"
                    :key="item.id"
                    class="garant-prax-list__row"
                >
                    <input type="checkbox" :value="item.id" v-model="selectedIds" />
                    <span>{{ formatStudent(item.student) }}</span>
                    <span>{{ item.faculty?.name ?? '—' }}</span>
                    <span>{{ item.company?.name ?? '—' }}</span>
                    <span>{{ item.status?.name ?? '—' }}</span>
                    <span>{{ formatAcademicYear(item.semester) }}</span>
                    <span>{{ formatSemesterName(item.semester) }}</span>

                       <div class="garant-prax-list__actions">
                           <button
                               type="button"
                               class="garant-prax-list__action-btn"
                               aria-label="Detail praxe"
                               @click="openDetail(item)"
                           >
                               <img
                                   :src="iconPath('eye.svg')"
                                   data-name="eye.svg"
                                   data-base-index="0"
                                   alt=""
                                   @error="onImgError"
                               />
                           </button>

                           <button
                               type="button"
                               class="garant-prax-list__action-btn"
                               aria-label="Upraviť prax"
                               @click="openEdit(item)"
                           >

                           <img
                                   :src="iconPath('edit.svg')"
                                   data-name="edit.svg"
                                   data-base-index="0"
                                   alt=""
                                   @error="onImgError"
                               />
                           </button>
                       </div>
                </div>
            </div>

            <!-- PAGINATION -->
            <div
                v-if="meta.total > meta.per_page"
                class="garant-prax-list__pagination"
            >
                <button
                    class="garant-prax-list__page-btn"
                    :disabled="meta.current_page === 1"
                    @click="changePage(meta.current_page - 1)"
                >
                    ‹
                </button>

                <button
                    v-for="page in pages"
                    :key="page"
                    class="garant-prax-list__page-btn"
                    :class="{ 'is-active': page === meta.current_page }"
                    @click="changePage(page)"
                >
                    {{ page }}
                </button>

                <button
                    class="garant-prax-list__page-btn"
                    :disabled="meta.current_page === meta.last_page"
                    @click="changePage(meta.current_page + 1)"
                >
                    ›
                </button>
            </div>
        </div>
    </section>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount, watch } from 'vue';
import axios from 'axios';
import { fetchMyFaculties } from '@/services/garantFaculties.js';
import GarantPraxDetailModal from './GarantPraxDetailModal.vue';
import GarantPraxEditModal from './GarantPraxEditModal.vue';


const emit = defineEmits([
         'open-statistics',
         'open-edit',
     ]);

/* ICONS */
const ICON_BASES = ['/storage/icons', '/storage/app/public/icons', '/icons'];
const iconPath = (n, i = 0) => `${ICON_BASES[i]}/${n}`;
const onImgError = e => {
    const el = e.target;
    const next = Number(el.dataset.baseIndex || 0) + 1;
    if (next < ICON_BASES.length) {
        el.dataset.baseIndex = next;
        el.src = iconPath(el.dataset.name, next);
    }
};

/* STATE */
const items = ref([]);
const academicYears = ref([]);
const companies = ref([]);
const students = ref([]);
const faculties = ref([]);

const loading = ref(false);
const error = ref(false);
const selectedIds = ref([]);
const statusNote = ref('');

const isStatusChanging = ref(false);

const isExporting = ref(false);


const toast = ref({
    visible: false,
    type: 'success', // 'success' | 'error'
    message: '',
});

let toastTimer = null;

function showToast(type, message) {
    toast.value = { visible: true, type, message };

    if (toastTimer) clearTimeout(toastTimer);
    toastTimer = setTimeout(() => {
        hideToast();
    }, 4500);
}

function hideToast() {
    toast.value.visible = false;
    if (toastTimer) {
        clearTimeout(toastTimer);
        toastTimer = null;
    }
}

function extractApiErrorMessage(err) {
    const fallback = 'Nepodarilo sa zmeniť stav praxe. Skúste to znova.';
    const data = err?.response?.data;

    if (!data) return fallback;

    // Laravel-ish: { message: "...", errors: { field: ["..."] } }
    if (typeof data.message === 'string' && data.message.trim()) return data.message;

    if (data.errors && typeof data.errors === 'object') {
        const msgs = Object.values(data.errors)
            .flat()
            .filter(Boolean)
            .map(String);

        if (msgs.length) return msgs.join(' • ');
    }

    // Some endpoints return string directly
    if (typeof data === 'string' && data.trim()) return data;

    return fallback;
}


/* DETAIL MODAL STATE */
    const isDetailOpen = ref(false);
const selectedInternship = ref(null);

/* EDIT MODAL STATE */
const isEditOpen = ref(false);


function openDetail(item) {
    selectedInternship.value = item;
    isDetailOpen.value = true;
    document.body.style.overflow = 'hidden';
}


function closeDetail() {
        isDetailOpen.value = false;
        selectedInternship.value = null;
        document.body.style.overflow = '';
    }

function openEdit(item) {
    selectedInternship.value = item;
    isEditOpen.value = true;
    document.body.style.overflow = 'hidden';
}

function closeEdit() {
    isEditOpen.value = false;
    selectedInternship.value = null;
    document.body.style.overflow = '';
}

function onInternshipUpdated(updated) {
    // optimistic replace in list (no refetch needed, but we still reload for safety)
    load();
}


const statuses = ref([]);


const meta = ref({ current_page: 1, last_page: 1, per_page: 20, total: 0 });
const perPageOptions = [10, 20, 50, 100];

const filters = ref({
    academic_year_ids: [],
    seasons: [],
    faculty_ids: [],
    company_ids: [],
    student_ids: [],
    status_names: [],
});


/* FILTER UI */
const showYear = ref(false);
const showSemester = ref(false);
const showFaculty = ref(false);
const showCompany = ref(false);
const showStudent = ref(false);
const showStatus = ref(false);


const statusFilterRef = ref(null);
const yearFilterRef = ref(null);
const semesterFilterRef = ref(null);
const facultyFilterRef = ref(null);
const companyFilterRef = ref(null);
const studentFilterRef = ref(null);

const companiesAll = ref([]);
const studentsAll = ref([]);
const statusesAll = ref([]);
const optionsLoaded = ref(false);


const toggleYear = () => {
    showYear.value = !showYear.value;
    showSemester.value = showFaculty.value = showCompany.value = showStudent.value = false;
};
const toggleSemester = () => {
    showSemester.value = !showSemester.value;
    showYear.value = showFaculty.value = showCompany.value = showStudent.value = false;
};
const toggleFaculty = () => {
    showFaculty.value = !showFaculty.value;
    showYear.value = showSemester.value = showCompany.value = showStudent.value = false;
};
const toggleCompany = () => {
    showCompany.value = !showCompany.value;
    showYear.value = showSemester.value = showFaculty.value = showStudent.value = false;
};
const toggleStudent = () => {
    showStudent.value = !showStudent.value;
    showYear.value = showSemester.value = showFaculty.value = showCompany.value = false;
};

const toggleStatus = () => {
        showStatus.value = !showStatus.value;
        showYear.value =
                showSemester.value =
                        showFaculty.value =
                                showCompany.value =
                                        showStudent.value = false;
};


/* CLICK OUTSIDE */
function handleClickOutside(e) {
    if (showYear.value && yearFilterRef.value && !yearFilterRef.value.contains(e.target)) showYear.value = false;
    if (showSemester.value && semesterFilterRef.value && !semesterFilterRef.value.contains(e.target)) showSemester.value = false;
    if (showFaculty.value && facultyFilterRef.value && !facultyFilterRef.value.contains(e.target)) showFaculty.value = false;
    if (showCompany.value && companyFilterRef.value && !companyFilterRef.value.contains(e.target)) showCompany.value = false;
    if (showStudent.value && studentFilterRef.value && !studentFilterRef.value.contains(e.target)) showStudent.value = false;
    if (showStatus.value && statusFilterRef.value && !statusFilterRef.value.contains(e.target)) showStatus.value = false;

}

function handleKeydown(e) {
    if (e.key === 'Escape') {
        if (isDetailOpen.value) closeDetail();
        if (isEditOpen.value) closeEdit();
    }
}


/* OPTIONS */
const semesterOptions = [
    { value: 'winter', label: 'Zimný' },
    { value: 'summer', label: 'Letný' },
];

/* COMPUTED */
const allSelected = computed(
    () => items.value.length > 0 && selectedIds.value.length === items.value.length
);

const hasAllowedActions = computed(() =>
    !hasMixedStatuses.value &&
    (canApprove.value || canDefend.value || canUndefend.value)
);


const companiesForFilter = computed(() => companiesAll.value.length ? companiesAll.value : companies.value);
const studentsForFilter = computed(() => studentsAll.value.length ? studentsAll.value : students.value);
const statusesForFilter = computed(() => statusesAll.value.length ? statusesAll.value : statuses.value);


const selectedItems = computed(() =>
    items.value.filter(i => selectedIds.value.includes(i.id))
);

const selectedStatus = computed(() =>
    hasMixedStatuses.value ? null : selectedStatuses.value[0] ?? null
);


const selectedStatuses = computed(() => {
    const set = new Set(
        selectedItems.value
            .map(i => i.status?.name)
            .filter(Boolean)
    );

    return Array.from(set);
});

const hasMixedStatuses = computed(() =>
    selectedStatuses.value.length > 1
);


const canApprove = computed(() => selectedStatus.value === 'Potvrdená');

const canDefend = computed(() => selectedStatus.value === 'Schválená');
const canUndefend = computed(() => selectedStatus.value === 'Schválená');


const pages = computed(
    () => Array.from({ length: meta.value.last_page }, (_, i) => i + 1)
);

/* METHODS */
function changePage(page) {
    if (page < 1 || page > meta.value.last_page || page === meta.value.current_page) return;
    meta.value.current_page = page;
}

function toggleSelectAll(e) {
    selectedIds.value = e.target.checked ? items.value.map(i => i.id) : [];
}

async function changeStatus(type, isPositive) {
  try {
    if (hasMixedStatuses.value) {
      showToast(
          'error',
          'Vybrané praxe majú rôzne stavy. Hromadná zmena stavu nie je možná.'
      );
      return;
    }

    if (!selectedIds.value.length) {
      showToast('error', 'Nie je vybraná žiadna prax.');
      return;
    }

    isStatusChanging.value = true;

    const res = await axios.post(`/api/internship/change-status/${type}`, {
      is_positive: isPositive,
      note: statusNote.value || null,

      internship_id:
          selectedIds.value.length === 1
              ? selectedIds.value[0]
              : undefined,

      internship_ids:
          selectedIds.value.length > 1
              ? selectedIds.value
              : undefined,
    });

    // IMPORTANT: backend can return 207 with failed items
    if (res.data?.failed?.length) {
      showToast(
          'error',
          res.data.failed[0]?.reason ||
          'Zmena stavu praxe sa nepodarila.'
      );
      return;
    }

    const count = selectedIds.value.length;

    statusNote.value = '';
    selectedIds.value = [];

    showToast(
        'success',
        count === 1
            ? 'Stav praxe bol úspešne zmenený.'
            : `Stav bol úspešne zmenený pre ${count} praxí.`
    );

    await load(); // refresh list
  } catch (e) {
    console.error('Status change failed', e);
    showToast('error', extractApiErrorMessage(e));
  } finally {
    isStatusChanging.value = false;
  }
}


async function exportCsv() {
    if (isExporting.value) return;

    try {
        isExporting.value = true;

        const res = await axios.post(
            '/api/internship/export-csv',
            buildExportPayload(),
            {
                responseType: 'blob',
            }
        );

        const blob = new Blob([res.data], { type: 'text/csv;charset=utf-8;' });
        const url = window.URL.createObjectURL(blob);

        const link = document.createElement('a');
        link.href = url;

        const disposition = res.headers['content-disposition'];
        const fileNameMatch = disposition?.match(/filename="(.+)"/);
        link.download = fileNameMatch?.[1] ?? 'internships.csv';

        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);

        window.URL.revokeObjectURL(url);

        showToast('success', 'Export do CSV bol úspešne spustený.');
    } catch (e) {
        console.error('CSV export failed', e);

        if (e?.response?.status === 404) {
            showToast('error', 'Nie sú dostupné žiadne dáta na export.');
        } else {
            showToast('error', extractApiErrorMessage(e));
        }
    } finally {
        isExporting.value = false;
    }
}


function buildExportPayload() {
    const payload = {};

    if (filters.value.academic_year_ids.length)
        payload.academic_year_ids = filters.value.academic_year_ids;

    if (filters.value.seasons.length)
        payload.season = filters.value.seasons;

    if (filters.value.faculty_ids.length)
        payload.faculty_ids = filters.value.faculty_ids;

    if (filters.value.company_ids.length)
        payload.company_ids = filters.value.company_ids;

    if (filters.value.student_ids.length)
        payload.student_ids = filters.value.student_ids;

    if (filters.value.status_names.length)
        payload.status_names = filters.value.status_names;

    return payload;
}


/* FORMATTERS */
const formatStudent = s =>
    s ? [s.title_before, s.first_name, s.last_name].filter(Boolean).join(' ') : '—';

const formatAcademicYear = s =>
    s?.start_date && s?.end_date
        ? `${new Date(s.start_date).getFullYear()}/${new Date(s.end_date).getFullYear()}`
        : '—';

const formatSemesterName = s => {
  if (!s?.season) return '—';

  const map = {
    winter: 'Zimný',
    summer: 'Letný',
    zimný: 'Zimný',
    letný: 'Letný',
  };

  return map[s.season.toLowerCase()] ?? '—';
};


const formatAcademicYearLabel = y =>
    `${new Date(y.start_date).getFullYear()}/${new Date(y.end_date).getFullYear()}`;

/* LOAD */
async function load() {
    loading.value = true;
    error.value = false;

    try {
        const res = await axios.get('/api/internship/all', {
            params: {
                page: meta.value.current_page,
                per_page: meta.value.per_page,

                academic_year_ids: filters.value.academic_year_ids,
                season: filters.value.seasons,
                faculty_ids: filters.value.faculty_ids,
                company_ids: filters.value.company_ids,
                student_ids: filters.value.student_ids,
                status_names: filters.value.status_names,
            },

        });

        items.value = res.data.data ?? [];
        meta.value = { ...meta.value, ...res.data.meta };

        // Keep master lists stable; only fallback to current page if master not loaded
        if (!optionsLoaded.value) {
            companiesAll.value = hydrateCompaniesFrom(items.value);
            studentsAll.value = hydrateStudentsFrom(items.value);
            statusesAll.value = hydrateStatusesFrom(items.value);
        }

    } catch {
        error.value = true;
    } finally {
        loading.value = false;
    }
}

function hydrateStatusesFromItems() {
    const map = new Map();

    items.value.forEach(i => {
        const name = i.status?.name;
        if (name) map.set(name, name);
    });

    statuses.value = Array.from(map.values()).sort((a, b) =>
        a.localeCompare(b, 'sk')
    );
}

async function loadAcademicYears() {
    const res = await axios.get('/api/academic-years');
    academicYears.value = res.data.data ?? [];
}

async function loadFaculties() {
    const all = await axios.get('/api/faculties');
    faculties.value = all.data ?? [];

    const my = await fetchMyFaculties();
    filters.value.faculty_ids = my.filter(f => f.selected).map(f => f.id);
}

async function loadFilterOptionsOnce() {
    if (optionsLoaded.value) return;

    try {
        // IMPORTANT: no filters emphasize "master options"
        const res = await axios.get('/api/internship/all', {
            params: {
                page: 1,
                per_page: 100, // your backend caps at 100 anyway
            },
        });

        const list = res.data.data ?? [];

        companiesAll.value = hydrateCompaniesFrom(list);
        studentsAll.value = hydrateStudentsFrom(list);
        statusesAll.value = hydrateStatusesFrom(list);

        optionsLoaded.value = true;
    } catch (e) {
        // if this fails, we still keep old behavior (options from current list)
        console.error('Failed to load filter options', e);
    }
}


function hydrateCompaniesFromItems() {
    const map = new Map();
    items.value.forEach(i => {
        if (i.company?.id) map.set(i.company.id, { id: i.company.id, name: i.company.name });
    });
    companies.value = Array.from(map.values()).sort((a, b) =>
        a.name.localeCompare(b.name, 'sk')
    );
}



function hydrateStudentsFromItems() {
    const map = new Map();
    items.value.forEach(i => {
        const s = i.student;
        if (!s?.id) return;
        const label = [s.title_before, s.first_name, s.last_name].filter(Boolean).join(' ');
        map.set(s.id, { id: s.id, label });
    });
    students.value = Array.from(map.values()).sort((a, b) =>
        a.label.localeCompare(b.label, 'sk')
    );
}

function hydrateCompaniesFrom(list) {
    const map = new Map();
    list.forEach(i => {
        if (i.company?.id) map.set(i.company.id, { id: i.company.id, name: i.company.name });
    });
    return Array.from(map.values()).sort((a, b) => a.name.localeCompare(b.name, 'sk'));
}

function hydrateStudentsFrom(list) {
    const map = new Map();
    list.forEach(i => {
        const s = i.student;
        if (!s?.id) return;
        const label = [s.title_before, s.first_name, s.last_name].filter(Boolean).join(' ');
        map.set(s.id, { id: s.id, label });
    });
    return Array.from(map.values()).sort((a, b) => a.label.localeCompare(b.label, 'sk'));
}

function hydrateStatusesFrom(list) {
    const set = new Set();
    list.forEach(i => {
        const name = i.status?.name;
        if (name) set.add(name);
    });
    return Array.from(set.values()).sort((a, b) => a.localeCompare(b, 'sk'));
}


/* WATCHERS */
watch(() => meta.value.current_page, load);
watch(() => meta.value.per_page, () => {
    meta.value.current_page = 1;
    load();
});
watch(filters, () => {
    meta.value.current_page = 1;
    load();
}, { deep: true });

/* LIFECYCLE */
onMounted(() => {
    document.addEventListener('click', handleClickOutside, true);
    document.addEventListener('keydown', handleKeydown);
    loadFilterOptionsOnce();
    load();
    loadAcademicYears();
    loadFaculties();
});


onBeforeUnmount(() => {
    if (toastTimer) clearTimeout(toastTimer);
    document.removeEventListener('click', handleClickOutside, true);
       document.removeEventListener('keydown', handleKeydown);
       // safety: if component unmounts while modal open
           document.body.style.overflow = '';
});

</script>
