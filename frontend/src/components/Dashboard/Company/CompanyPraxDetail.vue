<template>

  <input
      ref="reportInput"
      type="file"
      accept="application/pdf"
      class="company-prax-detail__upload-input"
      @change="onReportSelected"
  />


  <section class="company-prax-detail">
        <header class="company-prax-detail__header">
            <h2 class="company-prax-detail__title">
                Detail odbornej praxe
            </h2>

            <div class="student-prax-list__divider company-prax-detail__divider"></div>
        </header>

        <!-- LOADING -->
        <div v-if="isLoading" class="company-prax-detail__state">
            Načítavam detail praxe…
        </div>

        <!-- ERROR -->
        <div
            v-else-if="error"
            class="company-prax-detail__state company-prax-detail__state--error"
        >
            <p>{{ error }}</p>
            <button
                type="button"
                class="company-prax-detail__button company-prax-detail__button--outline"
                @click="reload"
            >
                Skúsiť znova
            </button>
        </div>

        <!-- CONTENT -->
        <div v-else-if="internship" class="company-prax-detail__content">
            <!-- Firma -->
            <section class="company-prax-detail__section">
                <h3 class="company-prax-detail__section-title">
                    Firma
                </h3>

                <p class="company-prax-detail__company-name">
                    {{ internship.company?.name ?? 'Neznáma firma' }}
                </p>

                <p
                    v-if="internship.company?.description"
                    class="company-prax-detail__text-muted"
                >
                    {{ internship.company.description }}
                </p>

                <p
                    v-if="internship.company?.website"
                    class="company-prax-detail__row"
                >
                    <span class="company-prax-detail__row-label">Web:</span>
                    <a
                        :href="internship.company.website"
                        target="_blank"
                        rel="noopener noreferrer"
                    >
                        {{ internship.company.website }}
                    </a>
                </p>

                <p
                    v-if="internship.company?.address"
                    class="company-prax-detail__row"
                >
                    <span class="company-prax-detail__row-label">Adresa:</span>
                    <span>
                        {{ internship.company.address.street }}
                        {{ internship.company.address.house_number }},
                        {{ internship.company.address.postal_code }}
                        {{ internship.company.address.city }}
                        <span v-if="internship.company.address.country">
                            , {{ internship.company.address.country.name }}
                        </span>
                    </span>
                </p>

                <!-- FALLBACK WARNING -->
                <p
                    v-if="nonBlockingError"
                    class="company-prax-detail__text-muted"
                >
                    {{ nonBlockingError }}
                </p>
            </section>

          <!-- Študent -->
          <section
              v-if="internship.student"
              class="company-prax-detail__section"
          >
            <h3 class="company-prax-detail__section-title">
              Študent
            </h3>

            <p class="company-prax-detail__student-name">
              {{ internship.student.first_name }}
              {{ internship.student.last_name }}
            </p>

            <p class="company-prax-detail__student-email">
              {{ internship.student.email }}
            </p>
          </section>


          <!-- Termín + semester -->
            <section class="company-prax-detail__section">
                <h3 class="company-prax-detail__section-title">
                    Termín a semester
                </h3>

                <p class="company-prax-detail__row">
                    <span class="company-prax-detail__row-label">Začiatok:</span>
                    <span>{{ formatDate(internship.start_date) }}</span>
                </p>

                <p class="company-prax-detail__row">
                    <span class="company-prax-detail__row-label">Koniec:</span>
                    <span>{{ formatDate(internship.date_to) }}</span>
                </p>

                <p
                    v-if="internship.semester"
                    class="company-prax-detail__row"
                >
                    <span class="company-prax-detail__row-label">
                        Akademický rok / semester:
                    </span>
                    <span>{{ formatSemester(internship.semester) }}</span>
                </p>
            </section>

            <!-- Popis -->
            <section class="company-prax-detail__section">
                <h3 class="company-prax-detail__section-title">
                    Popis praxe
                </h3>

                <p class="company-prax-detail__description">
                    {{ internship.description || 'Bez popisu.' }}
                </p>
            </section>

            <!-- Stav -->
            <section class="company-prax-detail__section company-prax-detail__section--status">
                <InternshipStatusBadge
                    :is-draft="false"
                    :status-name="internship.status?.name ?? null"
                />

                <p
                    v-if="internship.status?.changed_at"
                    class="company-prax-detail__status-meta"
                >
                    Stav aktualizovaný:
                    {{ formatDate(internship.status.changed_at) }}
                </p>
            </section>

          <section class="company-prax-detail__section company-prax-detail__section--documents">
            <h3 class="company-prax-detail__section-title">
              Dokumenty
            </h3>

            <div class="company-prax-detail__documents">
              <div class="company-prax-detail__document">
                <div class="company-prax-detail__document-info">
                  <span class="company-prax-detail__document-icon">🧾</span>

                  <div class="company-prax-detail__document-meta">
                    <strong>Výkaz o praxi</strong>

                    <span
                        v-if="reportDocument"
                        class="company-prax-detail__document-name"
                    >
            {{ reportDocument.file_name }}
          </span>

                    <span
                        v-else
                        class="company-prax-detail__document-muted"
                    >
            Dokument zatiaľ nebol nahraný
          </span>
                  </div>

                  <span
                      v-if="reportDocument"
                      class="company-prax-detail__report-status"
                      :data-status="reportDocument.status?.decision"
                  >
          {{ reportStatusLabel }}
        </span>
                </div>

                <div class="company-prax-detail__document-actions">
                  <button
                      v-if="reportDocument"
                      type="button"
                      class="company-prax-detail__link"
                      :disabled="isDownloadingReport"
                      @click="downloadReport"
                  >
                    {{ isDownloadingReport ? 'Sťahujem…' : 'Stiahnuť' }}
                  </button>

                  <button
                      v-if="canUploadReport"
                      type="button"
                      class="company-prax-detail__button company-prax-detail__button--small"
                      :disabled="isUploadingReport"
                      @click="triggerReportSelect"
                  >
                    {{ reportDocument ? 'Nahradiť' : 'Nahrať' }}
                  </button>
                </div>
              </div>
            </div>

            <!-- REVIEW ACTIONS -->
            <div
                v-if="canReviewReport"
                class="company-prax-detail__actions"
            >
              <button
                  class="company-prax-detail__button"
                  @click="openReview('approved')"
              >
                Potvrdiť
              </button>

              <button
                  class="company-prax-detail__button company-prax-detail__button--outline"
                  @click="openReview('rejected')"
              >
                Zamietnuť
              </button>
            </div>

            <p v-if="reportError" class="company-prax-detail__dialog-error">
              {{ reportError }}
            </p>
          </section>




          <!-- ACTIONS -->
            <section class="company-prax-detail__actions">
                <button
                    type="button"
                    class="company-prax-detail__button"
                    @click="emit('back-to-list')"
                >
                    Späť na zoznam
                </button>
            </section>
        </div>
    </section>

    <!-- REVIEW DIALOG -->
    <div
        v-if="showReviewDialog"
        class="company-prax-detail__dialog-backdrop"
    >
        <div class="company-prax-detail__dialog">
            <h3>
                {{ reviewDecision === 'approved'
                ? 'Potvrdenie výkazu'
                : 'Zamietnutie výkazu'
                }}
            </h3>

            <textarea
                v-model="reviewNote"
                placeholder="Vyjadrenie firmy (povinné)"
                maxlength="1000"
            />

            <p v-if="reviewError" class="company-prax-detail__dialog-error">
                {{ reviewError }}
            </p>

            <div class="company-prax-detail__actions">
                <button
                    class="company-prax-detail__button"
                    :disabled="isReviewing"
                    @click="submitReview"
                >
                    Potvrdiť
                </button>

                <button
                    class="company-prax-detail__button company-prax-detail__button--outline"
                    @click="closeReview"
                >
                    Zrušiť
                </button>
            </div>
        </div>
    </div>

</template>

<script setup>
import axios from 'axios';
import { ref, computed, onMounted, watch } from 'vue';
import InternshipStatusBadge from '@/components/Dashboard/General/InternshipStatusBadge.vue';
import { fetchCompanyInternships } from '@/services/companyInternships';
import { fetchCompanyDetail } from '@/services/company';

const props = defineProps({
    internshipId: {
        type: [Number, String],
        required: true,
    },
});

const emit = defineEmits(['back-to-list']);

const internship = ref(null);
const isLoading = ref(false);
const error = ref('');
const nonBlockingError = ref('');

const documents = ref([]);
const isReviewing = ref(false);

const showReviewDialog = ref(false);
const reviewDecision = ref(null); // 'approved' | 'rejected'
const reviewNote = ref('');
const reviewError = ref('');

const reportInput = ref(null);
const isUploadingReport = ref(false);
const reportError = ref('');
const isDownloadingReport = ref(false);

const canUploadReport = computed(() => true);



const numericId = computed(() => {
    const n = Number(props.internshipId);
    return Number.isFinite(n) ? n : null;
});

const reportDocument = computed(() =>
    documents.value.find(d => d.type === 'statement') ?? null
);

const canReviewReport = computed(() =>
    reportDocument.value?.status?.decision === 'pending'
);

const reportStatusLabel = computed(() => {
    switch (reportDocument.value?.status?.decision) {
        case 'approved':
            return 'Potvrdený';
        case 'rejected':
            return 'Zamietnutý';
        default:
            return 'Čaká na potvrdenie';
    }
});


async function loadDetail() {
    isLoading.value = true;
    error.value = '';
    nonBlockingError.value = '';

    if (!numericId.value) {
        error.value = 'Neplatné ID praxe.';
        isLoading.value = false;
        return;
    }

    try {
        const list = await fetchCompanyInternships();
        const raw = list.find(i => String(i.id) === String(numericId.value));

        if (!raw) {
            error.value = 'Detail praxe sa nepodarilo načítať.';
            return;
        }

        const latestHistory = raw.internship_status_histories
            ?.slice()
            .sort(
                (a, b) =>
                    new Date(b.status_changed_at) -
                    new Date(a.status_changed_at)
            )[0] ?? null;

        let company = raw.company ?? null;

        // 🔑 CONDITIONAL enrichment (fallback only)
        if (company?.id) {
            try {
                const fullCompany = await fetchCompanyDetail(company.id);
                company = {
                    ...company,
                    website: fullCompany.website ?? null,
                    address: fullCompany.address ?? null,
                };
            } catch {
                /* silent fallback */
            }
        }

      internship.value = {
        id: raw.id,
        start_date: raw.start_date,
        date_to: raw.date_to,
        description: raw.description ?? null,
        company,

        student: raw.student_profile?.user
            ? {
              first_name: raw.student_profile.user.first_name,
              last_name: raw.student_profile.user.last_name,
              email: raw.student_profile.user.email,
            }
            : null,

        semester: raw.academic_year
            ? {
              id: raw.academic_year.id,
              season: raw.academic_year.season,
              start_date: raw.academic_year.start_date,
              end_date: raw.academic_year.end_date,
            }
            : null,

        status: latestHistory
            ? {
              name: latestHistory.status?.name ?? null,
              changed_at: latestHistory.status_changed_at,
            }
            : null,
      };

    } catch (e) {
        console.error('[CompanyPraxDetail] Failed to load detail', e);
        error.value = 'Nepodarilo sa načítať detail praxe.';
        nonBlockingError.value =
          'Detail praxe sa nepodarilo načítať. Zobrazujú sa údaje zo zoznamu.';
    } finally {
        isLoading.value = false;
    }
  await loadDocuments();
  showReviewDialog.value =false;
  reviewNote.value = '';

}

async function loadDocuments() {
  if (!numericId.value) return;

  try {
    const res = await axios.get(
        `/api/internship/documents/${numericId.value}`
    );
    documents.value = res.data?.documents ?? [];
  } catch {
    documents.value = [];
  }
}

function reload() {
    loadDetail();
}

function formatDate(value) {
    if (!value) return '';
    const [y, m, d] = String(value).split('T')[0].split('-').map(Number);
    return y && m && d ? `${d}.${m}.${y}` : value;
}

function mapSeasonLabel(seasonRaw) {
    if (!seasonRaw) return '';
    const s = seasonRaw.toLowerCase();
    if (['winter', 'zimny', 'zimný', 'zima'].includes(s)) return 'Zimný semester';
    if (['summer', 'letny', 'letný', 'leto'].includes(s)) return 'Letný semester';
    return seasonRaw;
}

function formatSemester(semester) {
    const sy = new Date(semester.start_date).getFullYear();
    const ey = new Date(semester.end_date).getFullYear();
    const range =
        sy && ey && sy !== ey ? `${sy}/${String(ey).slice(-2)}` : sy || '';
    return [range, mapSeasonLabel(semester.season)].filter(Boolean).join(' – ');
}

function openReview(decision) {
    reviewDecision.value = decision;
    reviewNote.value = '';
    reviewError.value = '';
    showReviewDialog.value = true;
}

function closeReview() {
    showReviewDialog.value = false;
}

async function submitReview() {
    if (!reviewNote.value.trim()) {
        reviewError.value = 'Vyjadrenie je povinné.';
        return;
    }

    isReviewing.value = true;

    try {
        await axios.patch(
            `/api/internship/document/review-report/${reportDocument.value.id}`,
            {
                decision: reviewDecision.value,
                note: reviewNote.value,
            }
        );

        showReviewDialog.value = false;
        await loadDocuments(); // refresh status
    } catch (e) {
        reviewError.value =
            e.response?.data?.message ?? 'Akcia zlyhala.';
    } finally {
        isReviewing.value = false;
    }
}

function triggerReportSelect() {
  reportError.value = '';
  reportInput.value?.click();
}

async function onReportSelected(event) {
  const file = event.target.files?.[0];
  if (!file || !numericId.value) return;

  reportError.value = '';

  if (file.type !== 'application/pdf') {
    reportError.value = 'Súbor musí byť vo formáte PDF.';
    event.target.value = '';
    return;
  }

  if (file.size > 10 * 1024 * 1024) {
    reportError.value = 'Maximálna veľkosť súboru je 10 MB.';
    event.target.value = '';
    return;
  }

  const formData = new FormData();
  formData.append('document', file);

  isUploadingReport.value = true;

  try {
    await axios.post(
        `/api/internship/document/upload-report/${numericId.value}`,
        formData,
        { headers: { 'Content-Type': 'multipart/form-data' } }
    );

    await loadDocuments();
  } catch (e) {
    reportError.value =
        e.response?.data?.message ?? 'Nahratie výkazu zlyhalo.';
  } finally {
    isUploadingReport.value = false;
    event.target.value = '';
  }
}

async function downloadReport() {
  if (!reportDocument.value) return;

  isDownloadingReport.value = true;

  try {
    const res = await axios.get(
        `/api/internship/document/download/${reportDocument.value.id}`,
        { responseType: 'blob' }
    );

    const blob = new Blob([res.data], { type: 'application/pdf' });
    const url = URL.createObjectURL(blob);

    const a = document.createElement('a');
    a.href = url;
    a.download = reportDocument.value.file_name;
    a.click();

    URL.revokeObjectURL(url);
  } catch {
    reportError.value = 'Nepodarilo sa stiahnuť výkaz.';
  } finally {
    isDownloadingReport.value = false;
  }
}


onMounted(loadDetail);
watch(() => props.internshipId, loadDetail);
</script>

