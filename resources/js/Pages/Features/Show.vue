<script setup>
import { ref, onMounted } from 'vue';
import { usePage } from '@inertiajs/vue3';
import TheHeader from '../Welcome/components/TheHeader.vue';
import TheFooter from '../Welcome/components/TheFooter.vue';
import FeatureDetail from './components/FeatureDetail.vue';

// Dapatkan data dari controller
const { feature, features } = usePage().props;

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
                <div class="flex flex-col lg:flex-row gap-8">
                    <!-- Feature navigation sidebar -->
                    <div class="w-full lg:w-1/4">
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-4 sticky top-24">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Features</h3>
                            <nav class="space-y-2">
                                <Link
                                    v-for="item in features"
                                    :key="item.id"
                                    :href="`/features/${item.id}`"
                                    class="block px-3 py-2 rounded-md transition-colors"
                                    :class="feature.id === item.id ?
                                        'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-100' :
                                        'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700'"
                                >
                                    {{ item.title }}
                                </Link>
                            </nav>
                        </div>
                    </div>

                    <!-- Feature detail content -->
                    <div class="w-full lg:w-3/4">
                        <FeatureDetail :feature="feature" />
                    </div>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <TheFooter />
    </div>
</template>