<script setup>
// import { Link } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';

defineProps({
    feature: {
        type: Object,
        required: true
    }
});
</script>

<template>
    <Link
        :href="`/features/${feature.id}`"
        class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden transition-all duration-300 hover:shadow-lg transform hover:-translate-y-1"
    >
        <div class="p-6">
            <div class="flex items-center mb-4">
                <div
                    class="w-10 h-10 rounded-full flex items-center justify-center text-white mr-3"
                    :style="{ backgroundColor: feature.color }"
                >
                    <span class="text-white text-lg font-bold">{{ feature.number }}</span>
                </div>
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">{{ feature.title }}</h2>
            </div>
            <p class="text-gray-600 dark:text-gray-400 mb-4">{{ feature.shortDescription }}</p>
            <div class="text-blue-600 dark:text-blue-400 font-medium flex items-center">
                Learn more
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </div>
        </div>
    </Link>
</template>