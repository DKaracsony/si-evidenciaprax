<template>
    <!--
      Section: Process Steps (lp-second)
      Purpose:
      - Two content variants (tabs): Študenti / Firma.
      - Each variant shows 4 steps with icons and arrows between.
      Accessibility:
      - Tabs use role="tablist"/"tab"/"tabpanel" with aria-selected + aria-controls.
    -->
    <section class="lp-second">
        <div class="container">
            <article class="lp-second__card">
                <!-- Hanging tab buttons (clipped by card top) -->
                <div class="lp-second__tabs" role="tablist" aria-label="Prepínač obsahu">
                    <button
                        ref="btn1"
                        class="lp-second__btn"
                        :style="{ width: equalBtnWidth }"
                        :class="{ 'is-active': activeTab === 'set1' }"
                        role="tab"
                        :aria-selected="activeTab === 'set1'"
                        aria-controls="lp-second-panel-1"
                        @click="activeTab = 'set1'"
                    >
                        Študenti
                    </button>
                    <button
                        ref="btn2"
                        class="lp-second__btn"
                        :style="{ width: equalBtnWidth }"
                        :class="{ 'is-active': activeTab === 'set2' }"
                        role="tab"
                        :aria-selected="activeTab === 'set2'"
                        aria-controls="lp-second-panel-2"
                        @click="activeTab = 'set2'"
                    >
                        Firma
                    </button>
                </div>

                <!-- Content: 4 steps with arrows between (variant: Študenti) -->
                <div
                    v-if="activeTab === 'set1'"
                    id="lp-second-panel-1"
                    role="tabpanel"
                    class="lp-second__flow"
                >
                    <div class="lp-second__step">
                        <div class="lp-second__step-text">Vytvorte si účet a overte ho</div>
                        <img class="lp-second__step-icon" :src="iconPath('lp-sec2icons-1-1.svg')" data-name="lp-sec2icons-1-1.svg" data-base-index="0" alt="" @error="onImgError" />
                    </div>
                    <img class="lp-second__arrow" :src="iconPath('lp-sec2icons-arrow.svg')" data-name="lp-sec2icons-arrow.svg" data-base-index="0" alt="" @error="onImgError" />
                    <div class="lp-second__step">
                        <div class="lp-second__step-text">Doplňte profil, následne údaje o praxi</div>
                        <img class="lp-second__step-icon" :src="iconPath('lp-sec2icons-1-2.svg')" data-name="lp-sec2icons-1-2.svg" data-base-index="0" alt="" @error="onImgError" />
                    </div>
                    <img class="lp-second__arrow" :src="iconPath('lp-sec2icons-arrow.svg')" data-name="lp-sec2icons-arrow.svg" data-base-index="0" alt="" @error="onImgError" />
                    <div class="lp-second__step">
                        <div class="lp-second__step-text">Nahrajte výkazy a potvrdenia</div>
                        <img class="lp-second__step-icon" :src="iconPath('lp-sec2icons-1-3.svg')" data-name="lp-sec2icons-1-3.svg" data-base-index="0" alt="" @error="onImgError" />
                    </div>
                    <img class="lp-second__arrow" :src="iconPath('lp-sec2icons-arrow.svg')" data-name="lp-sec2icons-arrow.svg" data-base-index="0" alt="" @error="onImgError" />
                    <div class="lp-second__step">
                        <div class="lp-second__step-text">Počkajte na schválenie garanta</div>
                        <img class="lp-second__step-icon" :src="iconPath('lp-sec2icons-1-4.svg')" data-name="lp-sec2icons-1-4.svg" data-base-index="0" alt="" @error="onImgError" />
                    </div>
                </div>

                <!-- Content: 4 steps with arrows between (variant: Firma) -->
                <div
                    v-else
                    id="lp-second-panel-2"
                    role="tabpanel"
                    class="lp-second__flow"
                >
                    <div class="lp-second__step">
                        <div class="lp-second__step-text">Zaregistrujte zástupcu firmy</div>
                        <img class="lp-second__step-icon" :src="iconPath('lp-sec2icons-2-1.svg')" data-name="lp-sec2icons-2-1.svg" data-base-index="0" alt="" @error="onImgError" />
                    </div>
                    <img class="lp-second__arrow" :src="iconPath('lp-sec2icons-arrow.svg')" data-name="lp-sec2icons-arrow.svg" data-base-index="0" alt="" @error="onImgError" />
                    <div class="lp-second__step">
                        <div class="lp-second__step-text">Počkajte na overenie účtu</div>
                        <img class="lp-second__step-icon" :src="iconPath('lp-sec2icons-2-2.svg')" data-name="lp-sec2icons-2-2.svg" data-base-index="0" alt="" @error="onImgError" />
                    </div>
                    <img class="lp-second__arrow" :src="iconPath('lp-sec2icons-arrow.svg')" data-name="lp-sec2icons-arrow.svg" data-base-index="0" alt="" @error="onImgError" />
                    <div class="lp-second__step">
                        <div class="lp-second__step-text">Spravujte študentov na praxi</div>
                        <img class="lp-second__step-icon" :src="iconPath('lp-sec2icons-2-3.svg')" data-name="lp-sec2icons-2-3.svg" data-base-index="0" alt="" @error="onImgError" />
                    </div>
                    <img class="lp-second__arrow" :src="iconPath('lp-sec2icons-arrow.svg')" data-name="lp-sec2icons-arrow.svg" data-base-index="0" alt="" @error="onImgError" />
                    <div class="lp-second__step">
                        <div class="lp-second__step-text">Potvrďte priebeh a dokumenty</div>
                        <img class="lp-second__step-icon" :src="iconPath('lp-sec2icons-2-4.svg')" data-name="lp-sec2icons-2-4.svg" data-base-index="0" alt="" @error="onImgError" />
                    </div>
                </div>
            </article>
        </div>
    </section>
</template>

<script setup lang="ts">
import { ref, onMounted, onBeforeUnmount, nextTick } from 'vue'

/**
 * Icon path fallback:
 * - Tries multiple base directories; on <img> error we rewrite src to the next base.
 * - Keeps markup simple (data-name + data-base-index).
 */
const ICON_BASES = [
    '/storage/icons',
    '/storage/app/public/icons',
    '/icons',
] as const
function iconPath(name: string, baseIndex = 0) { return `${ICON_BASES[baseIndex]}/${name}` }
function onImgError(e: Event) {
    const el = e.target as HTMLImageElement
    const name = el.dataset.name
    const idx = Number(el.dataset.baseIndex || 0)
    if (!name) return
    const next = idx + 1
    if (next < ICON_BASES.length) {
        el.dataset.baseIndex = String(next)
        el.src = iconPath(name, next)
    }
}

/** Active tab state (Študenti / Firma) */
const activeTab = ref<'set1' | 'set2'>('set1')

/**
 * Equal-width tab buttons:
 * - On very small screens we disable equalizing to allow natural wrapping.
 * - Else we measure 'widest' button and set both widths to match.
 */
const btn1 = ref<HTMLButtonElement | null>(null)
const btn2 = ref<HTMLButtonElement | null>(null)
const equalBtnWidth = ref<string>('auto')
let resizeRaf = 0
const smallMQL = window.matchMedia('(max-width: 520px)')

async function setEqualBtnWidth() {
    if (!btn1.value || !btn2.value) return
    equalBtnWidth.value = 'auto'
    await nextTick()
    const max = Math.max(btn1.value.offsetWidth, btn2.value.offsetWidth)
    equalBtnWidth.value = `${max}px`
}

async function applyEqualize() {
    if (smallMQL.matches) {
        equalBtnWidth.value = 'auto' // natural sizing on tiny screens
    } else {
        await setEqualBtnWidth()
    }
}

/** Resize + media-query listeners (fire-and-forget; promise discarded via `void`) */
function handleResize() {
    cancelAnimationFrame(resizeRaf)
    resizeRaf = requestAnimationFrame(() => { void applyEqualize() })
}
function handleMQChange() {
    void applyEqualize()
}

/** Mount lifecycle: ensure DOM is ready, then equalize once; attach listeners */
onMounted(async () => {
    await nextTick()
    await applyEqualize()
    window.addEventListener('resize', handleResize)
    smallMQL.addEventListener('change', handleMQChange)
})
/** Cleanup listeners */
onBeforeUnmount(() => {
    window.removeEventListener('resize', handleResize)
    smallMQL.removeEventListener('change', handleMQChange)
})
</script>
