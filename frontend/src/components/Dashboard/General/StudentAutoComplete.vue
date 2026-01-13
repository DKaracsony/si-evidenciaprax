<!-- resources/js/components/internships/StudentAutocomplete.vue -->
<template>
    <div class="student-autocomplete">
        <label
            v-if="label"
            class="student-autocomplete__label"
        >
            {{ label }}
            <span v-if="required" class="student-autocomplete__required">*</span>
        </label>

        <div
            class="student-autocomplete__control"
            :class="{
                'student-autocomplete__control--disabled': disabled,
                'student-autocomplete__control--error': displayError,
                'student-autocomplete__control--open': isOpen,
            }"
        >
            <input
                type="text"
                class="student-autocomplete__input"
                :placeholder="placeholder"
                :disabled="disabled"
                v-model="searchTerm"
                @focus="onFocus"
                @blur="onBlur"
            />

            <button
                v-if="searchTerm && !disabled"
                type="button"
                class="student-autocomplete__clear"
                @mousedown.prevent="clearSelection"
            >
                ✕
            </button>
        </div>

        <p
            v-if="displayError"
            class="student-autocomplete__error"
        >
            {{ displayError }}
        </p>

        <!-- Card-like dropdown -->
        <ul
            v-if="isOpen"
            class="student-autocomplete__dropdown"
        >
            <li
                v-if="isLoading"
                class="student-autocomplete__item student-autocomplete__item--muted"
            >
                Načítavam študentov...
            </li>

            <li
                v-else-if="!options.length && !internalError"
                class="student-autocomplete__item student-autocomplete__item--muted"
            >
                Nenájdení žiadni študenti.
            </li>

            <li
                v-else-if="internalError"
                class="student-autocomplete__item student-autocomplete__item--muted"
            >
                {{ internalError }}
            </li>

            <li
                v-else
                v-for="s in options"
                :key="s.id"
                class="student-autocomplete__item"
                @mousedown.prevent="selectStudent(s)"
            >
                <div class="student-autocomplete__item-name">
                    {{ s.label }}
                </div>
            </li>
        </ul>
    </div>
</template>

<script setup>
import { ref, watch, computed, onBeforeUnmount } from 'vue';
import axios from 'axios';

const props = defineProps({
    modelValue: { type: Object, default: null },
    label: { type: String, default: 'Študent' },
    placeholder: { type: String, default: '' },
    required: { type: Boolean, default: false },
    error: { type: String, default: '' },
    disabled: { type: Boolean, default: false },
    minChars: { type: Number, default: 2 },
    debounceMs: { type: Number, default: 300 },
});

const emit = defineEmits(['update:modelValue', 'blur']);

const searchTerm = ref(props.modelValue?.label ?? '');
const options = ref([]);
const isLoading = ref(false);
const isOpen = ref(false);
const internalError = ref('');
let debounceTimer = null;
let lastRequestId = 0;

const displayError = computed(() => props.error || internalError.value);

watch(
    () => props.modelValue,
    (val) => {
        if (val?.label && !isOpen.value) {
            searchTerm.value = val.label;
        }
        if (!val && !isOpen.value) {
            searchTerm.value = '';
        }
    }
);

watch(searchTerm, (term) => {
    if (props.modelValue && term === props.modelValue.label) {
        options.value = [];
        internalError.value = '';
        isLoading.value = false;
        isOpen.value = false;
        return;
    }

    if (props.modelValue && term !== props.modelValue.label) {
        emit('update:modelValue', null);
    }

    if (debounceTimer) clearTimeout(debounceTimer);

    if (!term || term.length < props.minChars) {
        options.value = [];
        internalError.value = '';
        isLoading.value = false;
        isOpen.value = false;
        return;
    }

    debounceTimer = setTimeout(() => load(term), props.debounceMs);
});

async function load(term) {
    const req = ++lastRequestId;
    isLoading.value = true;
    internalError.value = '';
    isOpen.value = true;

    try {
        const res = await axios.get('/api/student/search-by-name', {
            params: { q: term },
        });

        if (req !== lastRequestId) return;

        const raw = res.data?.students ?? [];

        options.value = raw.map(s => ({
            id: s.id,
            label: [s.first_name, s.last_name].filter(Boolean).join(' ')
        }));
    } catch (e) {
        console.error('[StudentAutocomplete] Failed to search students', e);
        internalError.value = 'Nepodarilo sa načítať študentov.';
        options.value = [];
    } finally {
        if (req === lastRequestId) {
            isLoading.value = false;
        }
    }
}

function selectStudent(s) {
    emit('update:modelValue', s);
    searchTerm.value = s.label ?? '';
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

onBeforeUnmount(() => {
    if (debounceTimer) clearTimeout(debounceTimer);
});
</script>
