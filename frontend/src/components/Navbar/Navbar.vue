<template>
    <!--
      Navbar
      - Minimal, sticky nav bar with centered logo on wide viewports.
      - On smaller viewports the logo shifts left (see SCSS for breakpoint).
      - Reacts to auth store: guest vs. prihlásený používateľ.
    -->
    <header class="lp-nav" aria-label="Primárna navigácia">
        <div class="container lp-nav__wrap">
            <!-- Left spacer column (balances the centered logo in the grid). -->
            <div aria-hidden="true"></div>

            <!-- Logo links home -->
            <RouterLink to="/" class="lp-nav__logo" aria-label="Domov">
                <img :src="logoUrl" alt="PraxSI logo" />
            </RouterLink>

            <!-- Right-side group -->
            <div class="lp-nav__right">
                <!-- LOGGED IN: Dashboard + Úvod + profile icon + hover card -->
                <template v-if="isAuthenticated">
                    <RouterLink
                        to="/dashboard"
                        class="lp-nav__link lp-nav__link--dashboard"
                    >
                        Dashboard
                    </RouterLink>

                    <RouterLink
                        to="/"
                        class="lp-nav__link"
                    >
                        Úvod
                    </RouterLink>

                    <!-- Notifications -->
                    <div class="lp-nav__notifications">
                        <NotificationBell @toggle="toggleNotifications" />

                        <NotificationDropdown
                            v-if="showNotifications"
                        />
                    </div>

                    <!-- Profile bubble + hover card -->
                    <div class="lp-nav__profile-wrap">
                        <button
                            type="button"
                            class="lp-nav__profile"
                            aria-label="Profil používateľa"
                        >
                            <span class="lp-nav__profile-initials">
                                {{ profileInitials }}
                            </span>
                        </button>

                        <div
                            class="lp-nav__profile-card"
                            role="menu"
                            aria-label="Používateľské menu"
                        >
                            <div class="lp-nav__profile-card-top">
                                <div class="lp-nav__profile-card-name">
                                    <p class="lp-nav__profile-card-name-line lp-nav__profile-card-name-line--first">
                                        {{ firstName || '-' }}
                                    </p>
                                    <p class="lp-nav__profile-card-name-line lp-nav__profile-card-name-line--last">
                                        {{ lastName || '-' }}
                                    </p>
                                </div>
                            </div>

                            <div class="lp-nav__profile-card-actions">
                                <button
                                    type="button"
                                    class="lp-nav__profile-card-link"
                                    @click="handleLogout"
                                >
                                    Odhlásiť sa
                                </button>

                                <RouterLink
                                    :to="{ name: 'SettingsPage' }"
                                    class="lp-nav__profile-card-link"
                                >
                                    Nastavenia
                                </RouterLink>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- GUEST: Úvod + Prihlásenie button -->
                <template v-else>
                    <RouterLink
                        to="/"
                        class="lp-nav__link"
                    >
                        Úvod
                    </RouterLink>

                    <RouterLink
                        to="/login"
                        class="btn-primary lp-nav__btn"
                    >
                        Prihlásenie
                    </RouterLink>
                </template>
            </div>
        </div>
    </header>
</template>

<script setup>
import { computed } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth.js';
import { logout } from '@/services/auth.js';
import NotificationBell from '../Navbar/NotificationBell.vue';
import NotificationDropdown from '../Navbar/NotificationDropdown.vue';
import { ref } from 'vue';

const showNotifications = ref(false);

const toggleNotifications = () => {
    showNotifications.value = !showNotifications.value;
};

// Header logo path (served from public/storage).
const logoUrl = '/storage/lp-nav-1.png';

const router = useRouter();
const authStore = useAuthStore();

const isAuthenticated = computed(() => authStore.isAuthenticated);

// Safely read profile from the store.
const profile = computed(() => authStore.profile || null);

const firstName = computed(() => profile.value?.first_name || '');
const lastName = computed(() => profile.value?.last_name || '');

const profileInitials = computed(() => {
    const p = profile.value || {};

    let baseName =
        p['full_name'] || // bracket notation to avoid unresolved variable warning
        p.name ||
        (p.first_name && p.last_name
            ? `${p.first_name} ${p.last_name}`
            : null);

    // Fallback to email username if no proper name yet
    if (!baseName && p.email) {
        baseName = String(p.email).split('@')[0];
    }

    if (!baseName) {
        return '??';
    }

    const parts = String(baseName).trim().split(/\s+/);
    const first = parts[0]?.charAt(0) ?? '';
    const second = parts[1]?.charAt(0) ?? '';

    const initials = (first + second).toUpperCase();
    return initials || first.toUpperCase() || '?';
});

const handleLogout = async () => {
    try {
        await logout();
    } finally {
        // After logout, send user to landing page.
        await router.push({ name: 'LandingPage' });
    }
};
</script>
