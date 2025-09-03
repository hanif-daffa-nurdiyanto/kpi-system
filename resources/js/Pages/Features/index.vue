<script setup>
import { ref, onMounted } from 'vue'; // Tambahkan import ref
// import { usePage } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';
import TheHeader from '../Welcome/components/TheHeader.vue';
import TheFooter from '../Welcome/components/TheFooter.vue';
import FeatureCard from './components/FeatureCard.vue';

// Cara yang lebih aman untuk mengakses props
const page = usePage();
const features = page.props.features || []; // Tambahkan fallback jika features undefined

// Dark mode management
const isDarkMode = ref(false);
const toggleDarkMode = () => {
    isDarkMode.value = !isDarkMode.value;
    if (isDarkMode.value) {
        document.documentElement.classList.add('dark');
        document.body.classList.add('dark-mode');
    } else {
        document.documentElement.classList.remove('dark');
        document.body.classList.remove('dark-mode');
    }
    localStorage.setItem('darkMode', isDarkMode.value ? 'enabled' : 'disabled');
};

// Login routes
const loginRoutes = {
    employee: '/kpi',
    superadmin: '/admin'
};

onMounted(() => {
    // Check for stored dark mode preference
    const savedDarkMode = localStorage.getItem('darkMode');
    if (savedDarkMode === 'enabled') {
        isDarkMode.value = true;
        document.documentElement.classList.add('dark');
        document.body.classList.add('dark-mode');
    } else {
        const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
        isDarkMode.value = mediaQuery.matches;
        if (isDarkMode.value) {
            document.documentElement.classList.add('dark');
            document.body.classList.add('dark-mode');
        }
    }
});
</script>

<template>
    <div class="min-h-screen bg-gray-50 dark:bg-gray-950 transition-colors duration-300">
        <!-- Header -->
        <TheHeader
            :isDarkMode="isDarkMode"
            :toggleDarkMode="toggleDarkMode"
            :loginRoutes="loginRoutes"
        />

        <!-- Main content -->
        <main class="pt-28 pb-16">
            <div class="container px-4 mx-auto sm:px-6 lg:px-8">
                <!-- Features header -->
                <div class="text-center mb-12">
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">Our KPI System Features</h1>
                    <p class="text-lg text-gray-600 dark:text-gray-400 max-w-2xl mx-auto">
                        Explore our comprehensive set of tools designed to streamline your KPI management and improve organizational performance.
                    </p>
                </div>

                <!-- Features grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <FeatureCard
                        v-for="feature in features"
                        :key="feature.id"
                        :feature="feature"
                    />
                </div>
            </div>
        </main>

        <!-- Footer -->
        <TheFooter />
    </div>
</template>