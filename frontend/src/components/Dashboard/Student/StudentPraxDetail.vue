<template>
    <section class="student-prax-detail">
        <header class="student-prax-detail__header">
            <div class="student-prax-detail__title-row">
                <h2 class="student-prax-detail__title">
                    Detail odbornej praxe
                </h2>

                <span
                    v-if="isPaidPractice"
                    class="student-prax-detail__practice-badge student-prax-detail__practice-badge--paid"
                >
    Platená prax
</span>

            </div>

            <div class="student-prax-list__divider student-prax-detail__divider"></div>
        </header>


        <!-- LOADING -->
        <div v-if="isLoading" class="student-prax-detail__state">
            Načítavam detail praxe...
        </div>

        <!-- ERROR -->
        <div
            v-else-if="error"
            class="student-prax-detail__state student-prax-detail__state--error"
        >
            <p>{{ error }}</p>
            <button
                type="button"
                class="student-prax-detail__button student-prax-detail__button--outline"
                @click="reload"
            >
                Skúsiť znova
            </button>
        </div>

        <!-- CONTENT -->
        <div v-else-if="internship" class="student-prax-detail__content">
            <!-- HIDDEN FILE INPUTS (required for upload buttons) -->
            <input
                ref="agreementInput"
                type="file"
                accept="application/pdf"
                class="student-prax-detail__upload-input"
                @change="onAgreementSelected"
            />

            <input
                ref="reportInput"
                type="file"
                accept="application/pdf"
                class="student-prax-detail__upload-input"
                @change="onReportSelected"
            />
            <input
                ref="invoiceInput"
                type="file"
                accept="application/pdf"
                class="student-prax-detail__upload-input"
                @change="onInvoiceSelected"
            />

            <!-- Firma + kontakt -->
            <section class="student-prax-detail__section">
                <h3 class="student-prax-detail__section-title">
                    Firma
                </h3>

                <p class="student-prax-detail__company-name">
                    {{ internship.company?.name ?? 'Neznáma firma' }}
                </p>

                <p
                    v-if="internship.company?.description"
                    class="student-prax-detail__text-muted"
                >
                    {{ internship.company.description }}
                </p>

                <p
                    v-if="internship.company?.website"
                    class="student-prax-detail__row"
                >
                    <span class="student-prax-detail__row-label">Web:</span>
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
                    class="student-prax-detail__row"
                >
                    <span class="student-prax-detail__row-label">Adresa:</span>
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

                <p
                    v-if="contactPerson"
                    class="student-prax-detail__row"
                >
                    <span class="student-prax-detail__row-label">Kontakt:</span>
                    <span>
                        {{ contactPerson.title_before }}
                        {{ contactPerson.first_name }}
                        {{ contactPerson.last_name }}
                        {{ contactPerson.title_after }}
                        <span v-if="contactPerson.email">
                            &nbsp;– {{ contactPerson.email }}
                        </span>
                        <span v-if="contactPerson.role_at_company">
                            &nbsp;({{ contactPerson.role_at_company }})
                        </span>
                        <span v-if="contactPerson.phone_number">
                            &nbsp;· Tel: {{ contactPerson.phone_number }}
                        </span>
                    </span>
                </p>

                <!-- Non-blocking warning if detail fetch failed but we still show store fallback -->
                <p
                    v-if="nonBlockingError"
                    class="student-prax-detail__text-muted"
                >
                    {{ nonBlockingError }}
                </p>
            </section>

            <!-- Termíny + semester -->
            <section class="student-prax-detail__section">
                <h3 class="student-prax-detail__section-title">
                    Termín a semester
                </h3>

                <p class="student-prax-detail__row">
                    <span class="student-prax-detail__row-label">Začiatok:</span>
                    <span>{{ formatDate(internship.start_date) }}</span>
                </p>

                <p class="student-prax-detail__row">
                    <span class="student-prax-detail__row-label">Koniec:</span>
                    <span>{{ formatDate(internship.date_to) }}</span>
                </p>

                <p
                    v-if="internship.semester"
                    class="student-prax-detail__row"
                >
                    <span class="student-prax-detail__row-label">
                        Akademický rok / semester:
                    </span>
                    <span>{{ formatSemester(internship.semester) }}</span>
                </p>
            </section>

            <!-- Popis -->
            <section class="student-prax-detail__section">
                <h3 class="student-prax-detail__section-title">
                    Popis praxe
                </h3>

                <p class="student-prax-detail__description">
                    {{ internship.description || 'Bez popisu.' }}
                </p>
            </section>

            <!-- Stav -->
            <section class="student-prax-detail__section student-prax-detail__section--status">
                <div class="student-prax-detail__status-main">
                    <InternshipStatusBadge
                        :is-draft="!!internship.is_draft"
                        :status-name="internship.status?.name ?? null"
                    />

                    <p
                        v-if="internship.is_draft && internship['created_at']"
                        class="student-prax-detail__status-meta"
                    >
                        Návrh vytvorený: {{ formatDate(internship['created_at']) }}
                    </p>

                    <p
                        v-else-if="!internship.is_draft && internship.status?.changed_at"
                        class="student-prax-detail__status-meta"
                    >
                        Stav aktualizovaný:
                        {{ formatDate(internship.status.changed_at) }}
                    </p>
                </div>
            </section>

            <!-- DOKUMENTY -->
            <section class="student-prax-detail__section student-prax-detail__section--documents">
                <h3 class="student-prax-detail__section-title">
                    Dokumenty
                </h3>

                <div class="student-prax-detail__documents">
                    <!-- AGREEMENT (only for standard practice) -->
                    <!-- AGREEMENT (standard + paid employment contract) -->
                    <div
                        v-if="needsAgreementDocument"
                        class="student-prax-detail__document"
                    >
                        <div class="student-prax-detail__document-info">
                            <span class="student-prax-detail__document-icon">📄</span>

                            <div class="student-prax-detail__document-meta">
                                <strong>{{ agreementTitle }}</strong>

                                <span
                                    v-if="agreementDocument"
                                    class="student-prax-detail__document-name"
                                >
                {{ agreementDocument.file_name }}
            </span>

                                <span
                                    v-else
                                    class="student-prax-detail__document-muted"
                                >
                Dokument zatiaľ nebol nahraný
            </span>
                            </div>
                        </div>

                        <div class="student-prax-detail__document-actions">
                            <button
                                v-if="agreementDocument"
                                type="button"
                                class="student-prax-detail__link"
                                :disabled="isDownloadingUploadedAgreement"
                                @click="downloadUploadedAgreement"
                            >
                                {{ isDownloadingUploadedAgreement ? 'Sťahujem…' : 'Stiahnuť' }}
                            </button>

                            <button
                                type="button"
                                class="student-prax-detail__button student-prax-detail__button--small"
                                :disabled="isUploadingAgreement"
                                @click="triggerAgreementSelect"
                            >
                                {{ hasAgreement ? 'Nahradiť' : 'Nahrať' }}
                            </button>
                        </div>
                    </div>


                    <p
                        v-if="agreementError && needsAgreementDocument"
                        class="student-prax-detail__upload-error"
                    >
                        {{ agreementError }}
                    </p>

                    <p
                        v-if="agreementSuccess && needsAgreementDocument"
                        class="student-prax-detail__upload-success"
                    >
                        {{ agreementSuccess }}
                    </p>


                    <!-- REPORT (always required) -->
                    <div class="student-prax-detail__document">
                        <div class="student-prax-detail__document-info">
                            <span class="student-prax-detail__document-icon">🧾</span>

                            <div class="student-prax-detail__document-meta">
                                <strong>Výkaz o praxi</strong>

                                <span
                                    v-if="reportDocument"
                                    class="student-prax-detail__document-name"
                                >
                        {{ reportDocument.file_name }}
                    </span>

                                <span
                                    v-else
                                    class="student-prax-detail__document-muted"
                                >
                        Dokument zatiaľ nebol nahraný
                    </span>
                            </div>

                            <span
                                v-if="reportDocument"
                                class="student-prax-detail__report-status"
                                :data-status="reportDocument.status?.decision"
                            >
                    {{ reportStatusLabel }}
                </span>
                        </div>

                        <div class="student-prax-detail__document-actions">
                            <button
                                v-if="reportDocument"
                                type="button"
                                class="student-prax-detail__link"
                                :disabled="isDownloadingReport"
                                @click="downloadReport"
                            >
                                {{ isDownloadingReport ? 'Sťahujem…' : 'Stiahnuť' }}
                            </button>

                            <button
                                type="button"
                                class="student-prax-detail__button student-prax-detail__button--small"
                                :disabled="isUploadingReport"
                                @click="triggerReportSelect"
                            >
                                {{ hasReport ? 'Nahradiť' : 'Nahrať' }}
                            </button>
                        </div>
                    </div>

                    <p v-if="reportError" class="student-prax-detail__upload-error">
                        {{ reportError }}
                    </p>

                    <p v-if="reportSuccess" class="student-prax-detail__upload-success">
                        {{ reportSuccess }}
                    </p>

                  <!-- INVOICE MONTH PICKER (paid invoices practice) -->
                  <div
                      v-if="isPaidInvoicesPractice && invoiceCount < 3"
                      class="student-prax-detail__upload"
                  >
                    <label class="student-prax-detail__row-label">
                      Mesiac faktúry:
                    </label>

                    <input
                        type="month"
                        v-model="selectedInvoiceMonth"
                    />
                  </div>


                  <!-- INVOICES SUMMARY (paid invoices practice) -->
                    <div
                        v-if="isPaidInvoicesPractice"
                        class="student-prax-detail__document"
                    >
                        <div class="student-prax-detail__document-info">
                            <span class="student-prax-detail__document-icon">🧾</span>

                            <div class="student-prax-detail__document-meta">
                                <strong>Faktúry</strong>

                                <span class="student-prax-detail__document-name">
                Nahrané faktúry: {{ invoiceCount }}
            </span>

                                <span
                                    v-if="invoiceCount < 3"
                                    class="student-prax-detail__document-muted"
                                >
                Minimálne 3 po sebe idúce faktúry sú povinné
            </span>
                            </div>
                        </div>

                        <div class="student-prax-detail__document-actions">
                          <button
                              v-if="invoiceCount < 3"
                              type="button"
                              class="student-prax-detail__button student-prax-detail__button--small"
                              :disabled="isUploadingInvoice || !selectedInvoiceMonth"
                              @click="triggerInvoiceSelect()"
                          >

                          {{ isUploadingInvoice ? 'Nahrávam…' : 'Nahrať faktúru' }}
                            </button>
                        </div>
                    </div>


                    <!-- INVOICE LIST -->
                    <div
                        v-if="isPaidInvoicesPractice && invoiceDocuments.length"
                        class="student-prax-detail__documents"
                    >
                        <div
                            v-for="invoice in invoiceDocuments"
                            :key="invoice.id"
                            class="student-prax-detail__document"
                        >
                            <div class="student-prax-detail__document-info">
                                <span class="student-prax-detail__document-icon">📄</span>

                                <div class="student-prax-detail__document-meta">
                                  <strong>
                                    Faktúra – {{ formatInvoiceMonth(invoice.invoice_month) }}
                                  </strong>


                                  <span class="student-prax-detail__document-name">
                            {{ invoice.file_name }}
                        </span>
                                </div>
                            </div>

                            <div class="student-prax-detail__document-actions">
                                <button
                                    type="button"
                                    class="student-prax-detail__link"
                                    @click="downloadInvoice(invoice)"
                                >
                                    Stiahnuť
                                </button>

                                <button
                                    type="button"
                                    class="student-prax-detail__button student-prax-detail__button--small"
                                    :disabled="isUploadingInvoice"
                                    @click="triggerInvoiceSelect(invoice)"
                                >
                                    Nahradiť
                                </button>
                            </div>
                            <p v-if="invoiceError" class="student-prax-detail__upload-error">
                                {{ invoiceError }}
                            </p>

                            <p v-if="invoiceSuccess" class="student-prax-detail__upload-success">
                                {{ invoiceSuccess }}
                            </p>

                        </div>
                    </div>
                </div>
            </section>


            <!-- Akcie -->
            <section class="student-prax-detail__actions">
                <button
                    type="button"
                    class="student-prax-detail__button"
                    :disabled="isDownloading"
                    @click="downloadPdf"
                >
                    <span v-if="isDownloading">Sťahujem PDF...</span>
                    <span v-else>Stiahnuť PDF dohody</span>
                </button>

                <button
                    type="button"
                    class="student-prax-detail__button student-prax-detail__button--ghost"
                    @click="emit('back-to-list')"
                >
                    Späť na zoznam
                </button>
            </section>

            <p
                v-if="pdfError"
                class="student-prax-detail__pdf-error"
            >
                {{ pdfError }}
            </p>
        </div>
    </section>
</template>

<script setup>
import axios from 'axios';
import { ref, computed, watch, onMounted } from 'vue';
import { useInternshipStore } from '@/stores/internship';
import {
    fetchStudentInternshipDetail,
    downloadStudentInternshipAgreementPdf,
} from '@/services/internship';
import InternshipStatusBadge from '@/components/Dashboard/General/InternshipStatusBadge.vue';

const props = defineProps({
    internshipId: {
        type: [Number, String],
        required: true,
    },
});

const emit = defineEmits(['back-to-list']);

const internshipStore = useInternshipStore();

const internship = ref(null);
const isLoading = ref(false);
const error = ref('');
const nonBlockingError = ref('');
const isDownloading = ref(false);
const pdfError = ref('');

const agreementInput = ref(null);
const isUploadingAgreement = ref(false);
const agreementError = ref('');
const agreementSuccess = ref('');
const isDownloadingUploadedAgreement = ref(false);

const reportInput = ref(null);
const isUploadingReport = ref(false);
const reportError = ref('');
const reportSuccess = ref('');
const isDownloadingReport = ref(false);

const invoiceInput = ref(null);
const isUploadingInvoice = ref(false);
const invoiceError = ref('');
const invoiceSuccess = ref('');
const replacingInvoice = ref(null); // invoice being replaced (or null)

const selectedInvoiceMonth = ref(null); // YYYY-MM

function triggerInvoiceSelect(invoice = null) {
    invoiceError.value = '';
    invoiceSuccess.value = '';
    replacingInvoice.value = invoice; // null = new upload
    invoiceInput.value?.click();
}

async function onInvoiceSelected(event) {
    const file = event.target.files?.[0];
    if (!file || !numericId.value) return;


    invoiceError.value = '';
    invoiceSuccess.value = '';

  if (!selectedInvoiceMonth.value) {
    invoiceError.value = 'Vyberte mesiac faktúry.';
    return;
  }


  if (file.type !== 'application/pdf') {
        invoiceError.value = 'Súbor musí byť vo formáte PDF.';
        event.target.value = '';
        return;
    }

    if (file.size > 10 * 1024 * 1024) {
        invoiceError.value = 'Maximálna veľkosť súboru je 10 MB.';
        event.target.value = '';
        return;
    }

    const formData = new FormData();
    formData.append('internship_id', numericId.value);
    formData.append('document', file);
  formData.append('invoice_month', selectedInvoiceMonth.value);

    // optional: backend may detect replace by ID
    if (replacingInvoice.value?.id) {
        formData.append('replace_document_id', replacingInvoice.value.id);
    }

    isUploadingInvoice.value = true;

    try {
        const response = await axios.post(
            `/api/internship/document/upload-invoices/${numericId.value}`,
            formData,
            { headers: { 'Content-Type': 'multipart/form-data' } }
        );

        invoiceSuccess.value =
            response.data?.message ?? 'Faktúra bola úspešne nahraná.';

        await loadDocuments();
    } catch (e) {
        const status = e.response?.status;
        const data = e.response?.data;

        if (status === 422 && data?.errors) {
            invoiceError.value =
                Object.values(data.errors).flat()[0] ?? 'Neplatný súbor.';
        } else {
            invoiceError.value =
                data?.message ?? 'Nahratie faktúry zlyhalo.';
        }
    } finally {
        isUploadingInvoice.value = false;
        replacingInvoice.value = null;
      selectedInvoiceMonth.value = null;
        event.target.value = '';
    }
}


const practiceType = computed(() => internship.value?.practice_type ?? 'standard');

const isStandardPractice = computed(
    () => practiceType.value === 'standard'
);

const isPaidPractice = computed(() =>
    practiceType.value === 'paid_invoices' ||
    practiceType.value === 'paid_employment_contract'
);


// All invoices
const invoiceDocuments = computed(() =>
    Array.isArray(internship.value?.documents)
        ? internship.value.documents.filter(d => d.type === 'invoice')
        : []
);

const invoiceCount = computed(() => invoiceDocuments.value.length);


const numericId = computed(() => {
    const n = Number(props.internshipId);
    return Number.isFinite(n) ? n : null;
});

const reportDocument = computed(() =>
    Array.isArray(internship.value?.documents)
        ? internship.value.documents.find(d => d.type === 'statement')
        : null
);

const hasReport = computed(() => !!reportDocument.value);

const reportStatusLabel = computed(() => {
    const decision = reportDocument.value?.status?.decision;

    switch (decision) {
        case 'approved':
            return 'Potvrdený firmou';
        case 'rejected':
            return 'Zamietnutý firmou';
        case 'pending':
        default:
            return 'Čaká na potvrdenie firmy';
    }
});


const agreementDocument = computed(() =>
    Array.isArray(internship.value?.documents)
        ? internship.value.documents.find(d => d.type === 'agreement')
        : null
);

const hasAgreement = computed(() => !!agreementDocument.value);

const needsAgreementDocument = computed(() =>
    practiceType.value === 'standard' ||
    practiceType.value === 'paid_employment_contract'
);

const agreementTitle = computed(() =>
    practiceType.value === 'paid_employment_contract'
        ? 'Pracovná zmluva'
        : 'Dohoda o praxi'
);


const contactPerson = computed(() =>
    internship.value?.company?.['contact_person'] ?? null
);

function toTime(value) {
    const t = new Date(value).getTime();
    return Number.isFinite(t) ? t : 0;
}

async function loadDetail() {
    isLoading.value = true;
    error.value = '';
    nonBlockingError.value = '';
    pdfError.value = '';

    if (!numericId.value) {
        internship.value = null;
        error.value = 'Neplatné ID praxe.';
        isLoading.value = false;
        return;
    }

    try {
        // If we have an item in store, use it as quick fallback (clone to avoid mutating Pinia object)
        const fromStore = internshipStore.internshipById(numericId.value);
        if (fromStore) {
            internship.value = { ...fromStore };
        }

        const data = await fetchStudentInternshipDetail(numericId.value);

        // Primary data
        internship.value = data;
        await loadDocuments();


        // Extract latest status safely (do not assume ordering)
        const historyRaw = Array.isArray(data?.['status_history'])
            ? data['status_history']
            : [];

        const history = historyRaw.flat ? historyRaw.flat() : historyRaw;

        if (history.length > 0) {
            const latest = history
                .slice()
                .sort(
                    (a, b) =>
                        toTime(b?.['status_changed_at']) -
                        toTime(a?.['status_changed_at'])
                )[0];

            const rawStatus = latest?.status ?? null;

            internship.value.status = {
                name:
                    typeof rawStatus === 'string'
                        ? rawStatus
                        : rawStatus?.name ?? internship.value.status?.name ?? null,
                changed_at:
                    latest?.['status_changed_at'] ??
                    internship.value.status?.changed_at ??
                    null,
            };

        }

        // Keep list/store in sync with newes-t version of the internship
        internshipStore.upsertInternship(internship.value);
    } catch (e) {
        console.error('[StudentPraxDetail] Failed to load detail', e);

        // If we have no fallback data, show blocking error
        if (!internship.value) {
            error.value = 'Nepodarilo sa načítať detail praxe.';
        } else {
            // Otherwise show a non-blocking message (user still sees basic info)
            nonBlockingError.value =
                'Detail praxe sa nepodarilo načítať. Zobrazujú sa údaje zo zoznamu.';
        }
    } finally {
        isLoading.value = false;
    }
}

function reload() {
    loadDetail();
}

function triggerAgreementSelect() {
    agreementError.value = '';
    agreementSuccess.value = '';
    agreementInput.value?.click();
}

function formatInvoiceMonth(value) {
  if (!value) return 'bez mesiaca';
  const [y, m] = String(value).split('-');
  return `${m}.${y}`;
}

async function onAgreementSelected(event) {
    const file = event.target.files?.[0];
    if (!file || !numericId.value) return;

    agreementError.value = '';
    agreementSuccess.value = '';

    if (file.type !== 'application/pdf') {
        agreementError.value = 'Súbor musí byť vo formáte PDF.';
        event.target.value = '';
        return;
    }

    if (file.size > 10 * 1024 * 1024) {
        agreementError.value = 'Maximálna veľkosť súboru je 10 MB.';
        event.target.value = '';
        return;
    }

    const formData = new FormData();
    formData.append('internship_id', numericId.value);
    formData.append('document', file);

    isUploadingAgreement.value = true;

    try {
        const response = await axios.post(
            '/api/internship/document/upload-agreement',
            formData,
            { headers: { 'Content-Type': 'multipart/form-data' } }
        );

        agreementSuccess.value = response.data?.message
            ?? 'Dohoda bola úspešne nahraná.';

        await loadDetail(); // refresh internship data
    } catch (e) {
        const status = e.response?.status;
        const data = e.response?.data;

        if (status === 422 && data?.errors) {
            agreementError.value =
                Object.values(data.errors).flat()[0]
                ?? 'Neplatný súbor.';
        } else if (status === 404) {
            agreementError.value = 'Praxa nebola nájdená.';
        } else {
            agreementError.value =
                data?.message ?? 'Nahratie dohody zlyhalo.';
        }
    } finally {
        isUploadingAgreement.value = false;
        event.target.value = '';
    }
}


async function loadDocuments() {
    if (!numericId.value) return;

    try {
        const response = await axios.get(
            `/api/internship/documents/${numericId.value}`
        );

        internship.value.documents = response.data?.documents ?? [];
    } catch (e) {
        // Non-blocking: documents are optional for detail rendering
        internship.value.documents = [];
    }
}


async function downloadUploadedAgreement() {
    if (!agreementDocument.value) return;

    isDownloadingUploadedAgreement.value = true;

    try {
        const response = await axios.get(
            `/api/internship/document/download/${agreementDocument.value.id}`,
            { responseType: 'blob' }
        );

        const blob = new Blob([response.data], { type: 'application/pdf' });
        const url = URL.createObjectURL(blob);

        const a = document.createElement('a');
        a.href = url;
        a.download = agreementDocument.value.file_name;
        a.click();

        URL.revokeObjectURL(url);
    } catch (e) {
        console.error('[StudentPraxDetail] Failed to download uploaded agreement', e);
        agreementError.value = 'Nepodarilo sa stiahnuť nahranú dohodu.';
    } finally {
        isDownloadingUploadedAgreement.value = false;
    }
}


async function downloadPdf() {
    if (!numericId.value) return;

    isDownloading.value = true;
    pdfError.value = '';

    try {
        const response =
            await downloadStudentInternshipAgreementPdf(numericId.value);
        const blob = new Blob([response.data], { type: 'application/pdf' });
        const url = URL.createObjectURL(blob);

        const a = document.createElement('a');
        a.href = url;
        a.download = `dohoda-praxe-${numericId.value}.pdf`;
        a.click();

        URL.revokeObjectURL(url);
    } catch (e) {
        console.error('[StudentPraxDetail] Failed to download PDF', e);

        const status = e.response?.status;
        const message = e.response?.data?.message;

        if (status === 403) {
            pdfError.value = 'Tento dokument nie je dostupný pre váš účet.';
        } else if (status === 409) {
            pdfError.value = message || 'PDF dohody nie je momentálne dostupné.';
        } else {
            pdfError.value = 'Pri sťahovaní PDF došlo k chybe. Skúste to znova.';
        }
    } finally {
        isDownloading.value = false;
    }
}

function formatDate(value) {
    if (!value) return '';
    const datePart = String(value).split('T')[0];
    const [year, month, day] = datePart.split('-').map(Number);
    if (!year || !month || !day) return value;
    return `${day}.${month}.${year}`;
}

async function downloadInvoice(invoice) {
    try {
        const response = await axios.get(
            `/api/internship/document/download/${invoice.id}`,
            { responseType: 'blob' }
        );

        const blob = new Blob([response.data]);
        const url = URL.createObjectURL(blob);

        const a = document.createElement('a');
        a.href = url;
        a.download = invoice.file_name;
        a.click();

        URL.revokeObjectURL(url);
    } catch (e) {
        console.error('Failed to download invoice', e);
    }
}


function mapSeasonLabel(seasonRaw) {
    if (!seasonRaw) return '';
    const s = String(seasonRaw).toLowerCase();

    if (['winter', 'zimny', 'zimný', 'zima'].includes(s)) {
        return 'Zimný semester';
    }
    if (['summer', 'letny', 'letný', 'leto'].includes(s)) {
        return 'Letný semester';
    }
    return seasonRaw;
}

function formatSemester(semester) {
    if (!semester) return '';
    const startYear = new Date(semester.start_date).getFullYear();
    const endYear = new Date(semester.end_date).getFullYear();

    const range =
        startYear && endYear && startYear !== endYear
            ? `${startYear}/${String(endYear).slice(-2)}`
            : startYear || '';

    const seasonLabel = mapSeasonLabel(semester.season);
    return [range, seasonLabel].filter(Boolean).join(' – ');
}

onMounted(loadDetail);
watch(
    () => props.internshipId,
    () => {
        loadDetail();
    }
);

function triggerReportSelect() {
    reportError.value = '';
    reportSuccess.value = '';
    reportInput.value?.click();
}

async function onReportSelected(event) {
    const file = event.target.files?.[0];
    if (!file || !numericId.value) return;

    reportError.value = '';
    reportSuccess.value = '';

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
        const response = await axios.post(
            `/api/internship/document/upload-report/${numericId.value}`,
            formData,
            { headers: { 'Content-Type': 'multipart/form-data' } }
        );

        reportSuccess.value =
            response.data?.message ?? 'Výkaz bol úspešne nahraný.';

        await loadDocuments();
    } catch (e) {
        const status = e.response?.status;
        const data = e.response?.data;

        if (status === 422 && data?.errors) {
            reportError.value =
                Object.values(data.errors).flat()[0] ?? 'Neplatný súbor.';
        } else if (status === 404) {
            reportError.value = 'Praxa nebola nájdená.';
        } else {
            reportError.value =
                data?.message ?? 'Nahratie výkazu zlyhalo.';
        }
    } finally {
        isUploadingReport.value = false;
        event.target.value = '';
    }
}

async function downloadReport() {
    if (!reportDocument.value) return;

    isDownloadingReport.value = true;

    try {
        const response = await axios.get(
            `/api/internship/document/download/${reportDocument.value.id}`,
            { responseType: 'blob' }
        );

        const blob = new Blob([response.data], { type: 'application/pdf' });
        const url = URL.createObjectURL(blob);

        const a = document.createElement('a');
        a.href = url;
        a.download = reportDocument.value.file_name;
        a.click();

        URL.revokeObjectURL(url);
    } catch (e) {
        reportError.value = 'Nepodarilo sa stiahnuť výkaz.';
    } finally {
        isDownloadingReport.value = false;
    }
}

const isPaidInvoicesPractice = computed(
    () => practiceType.value === 'paid_invoices'
);

</script>

