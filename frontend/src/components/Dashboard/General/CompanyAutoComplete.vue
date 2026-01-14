<!-- resources/js/components/internships/CompanyAutocomplete.vue -->
<template>
    <div class="company-autocomplete">
        <label
            v-if="label"
            class="company-autocomplete__label"
        >
            {{ label }}
            <span v-if="required" class="company-autocomplete__required">*</span>
        </label>

        <div
            class="company-autocomplete__control"
            :class="{
                'company-autocomplete__control--disabled': disabled,
                'company-autocomplete__control--error': displayError,
                'company-autocomplete__control--open': isOpen,
            }"
        >
            <input
                type="text"
                class="company-autocomplete__input"
                :placeholder="placeholder"
                :disabled="disabled"
                v-model="searchTerm"
                @focus="onFocus"
                @blur="onBlur"
            />

            <button
                v-if="searchTerm && !disabled"
                type="button"
                class="company-autocomplete__clear"
                @mousedown.prevent="clearSelection"
            >
                ✕
            </button>
        </div>

        <p
            v-if="hint && !displayError"
            class="company-autocomplete__hint"
        >
            {{ hint }}
        </p>

        <p
            v-if="displayError"
            class="company-autocomplete__error"
        >
            {{ displayError }}
        </p>

        <!-- Card-like dropdown -->
        <ul
            v-if="isOpen"
            class="company-autocomplete__dropdown"
        >
            <li
                v-if="isLoading"
                class="company-autocomplete__item company-autocomplete__item--muted"
            >
                Načítavam firmy...
            </li>

            <li
                v-else-if="!options.length && !internalError"
                class="company-autocomplete__item company-autocomplete__item--muted"
            >
                Nenájdené žiadne firmy.
            </li>

            <li
                v-else-if="internalError"
                class="company-autocomplete__item company-autocomplete__item--muted"
            >
                {{ internalError }}
            </li>

            <li
                v-else
                v-for="company in options"
                :key="company.id"
                class="company-autocomplete__item"
                @mousedown.prevent="selectCompany(company)"
            >
                <div class="company-autocomplete__item-name">
                    {{ company.name }}
                </div>
            </li>
        </ul>
    </div>
</template>

<script setup>
import { ref, watch, computed, onBeforeUnmount } from 'vue';
import { searchCompaniesByName } from '@/services/company';

const props = defineProps({
    modelValue: {
        type: Object,
        default: null,
    },
    label: {
        type: String,
        default: 'Firma',
    },
    placeholder: {
        type: String,
        default: '',
    },
    required: {
        type: Boolean,
        default: false,
    },
    hint: {
        type: String,
        default: '',
    },
    error: {
        type: String,
        default: '',
    },
    disabled: {
        type: Boolean,
        default: false,
    },
    minChars: {
        type: Number,
        default: 2,
    },
    debounceMs: {
        type: Number,
        default: 300,
    },
});

const emit = defineEmits(['update:modelValue', 'blur']);

const searchTerm = ref(props.modelValue?.name ?? '');
const options = ref([]);
const isLoading = ref(false);
const isOpen = ref(false);
const internalError = ref('');
let debounceTimer = null;

// request versioning to prevent race conditions
let lastRequestId = 0;

const displayError = computed(() => props.error || internalError.value);

watch(
    () => props.modelValue,
    (newVal) => {
        if (newVal && newVal.name && !isOpen.value) {
            searchTerm.value = newVal.name;
        }
        if (!newVal && !isOpen.value) {
            searchTerm.value = '';
        }
    }
);

watch(searchTerm, (newTerm) => {
    // programmatic change (select / hydrate)
    if (props.modelValue && newTerm === props.modelValue.name) {
        options.value = [];
        internalError.value = '';
        isLoading.value = false;
        isOpen.value = false;
        return;
    }

    // user overwrote selection
    if (props.modelValue && newTerm !== props.modelValue.name) {
        emit('update:modelValue', null);
    }

    if (debounceTimer) {
        clearTimeout(debounceTimer);
    }

    if (!newTerm || newTerm.length < props.minChars) {
        options.value = [];
        internalError.value = '';
        isLoading.value = false;
        isOpen.value = false;
        return;
    }

    debounceTimer = setTimeout(() => {
        loadOptions(newTerm);
    }, props.debounceMs);
});

async function loadOptions(term) {
    const requestId = ++lastRequestId;

    isLoading.value = true;
    internalError.value = '';
    isOpen.value = true;

    try {
        const result = await searchCompaniesByName(term);

        // ignore stale responses
        if (requestId !== lastRequestId) return;

        if (Array.isArray(result)) {
            options.value = result;
        } else if (result && Array.isArray(result.data)) {
            options.value = result.data;
        } else {
            options.value = [];
        }
    } catch (error) {
        if (requestId !== lastRequestId) return;

        console.error('[CompanyAutocomplete] Failed to search companies:', error);
        internalError.value = 'Nepodarilo sa načítať zoznam firiem.';
        options.value = [];
    } finally {
        if (requestId === lastRequestId) {
            isLoading.value = false;
        }
    }
}

function onFocus() {
    if (options.value.length) {
        isOpen.value = true;
    }
}

function onBlur() {
    setTimeout(() => {
        isOpen.value = false;
        emit('blur');
    }, 150);
}

function selectCompany(company) {
    emit('update:modelValue', company);
    searchTerm.value = company.name ?? '';
    isOpen.value = false;
    internalError.value = '';
}

function clearSelection() {
    searchTerm.value = '';
    options.value = [];
    internalError.value = '';
    isOpen.value = false;
    emit('update:modelValue', null);
}

onBeforeUnmount(() => {
    if (debounceTimer) {
        clearTimeout(debounceTimer);
    }
});
</script>
