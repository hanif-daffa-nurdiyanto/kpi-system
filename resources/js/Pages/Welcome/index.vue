<script setup>
import { ref, onMounted } from "vue";
import TheHeader from "./components/TheHeader.vue";
import TheFooter from "./components/TheFooter.vue";
import HeroSection from "./components/HeroSection.vue";
import FeaturesSection from "./components/FeaturesSection.vue";
import StatsSection from "./components/StatsSection.vue";
import FaqSection from "./components/FaqSection.vue";
import CtaSection from "./components/CtaSection.vue";

const props = defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
    laravelVersion: String,
    phpVersion: String,
});

const isDarkMode = ref(false);

const applyDarkMode = (enabled) => {
    isDarkMode.value = enabled;
    document.documentElement.classList.toggle("dark", enabled);
    localStorage.setItem("darkMode", enabled ? "enabled" : "disabled");
};

const toggleDarkMode = () => {
    applyDarkMode(!isDarkMode.value);
};

const loginRoutes = {
    employee: "/kpi",
    superadmin: "/admin",
};

onMounted(() => {
    const saved = localStorage.getItem("darkMode");

    if (saved === "enabled") {
        applyDarkMode(true);
    } else if (saved === "disabled") {
        applyDarkMode(false);
    } else {
        const prefersDark = window.matchMedia(
            "(prefers-color-scheme: dark)"
        ).matches;
        applyDarkMode(prefersDark);
    }

    const mediaQuery = window.matchMedia("(prefers-color-scheme: dark)");
    mediaQuery.addEventListener("change", (event) => {
        if (!localStorage.getItem("darkMode")) {
            applyDarkMode(event.matches);
        }
    });
});
</script>

<template>
    <div
        class="min-h-screen bg-gray-50 dark:bg-gray-950 transition-colors duration-300"
    >
        <!-- Header -->
        <TheHeader
            :isDarkMode="isDarkMode"
            :toggleDarkMode="toggleDarkMode"
            :loginRoutes="loginRoutes"
        />

        <!-- Main content -->
        <main class="bg-gradient-to-br from-gray-900 via-gray-800 to-black">
            <HeroSection :loginRoutes="loginRoutes" />
            <FeaturesSection />
            <StatsSection />
            <FaqSection />
            <CtaSection />
        </main>

        <!-- Footer -->
        <TheFooter />
    </div>
</template>
