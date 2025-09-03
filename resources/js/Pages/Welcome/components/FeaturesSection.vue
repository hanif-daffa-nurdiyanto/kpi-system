<script setup>
import { ref } from 'vue';
import { useVisibilityAnimation } from '../../../Composables/useVisibilityAnimation';

// Features with icons using Heroicons - updated with violet accent colors
const features = ref([
    {
        title: 'Real-time Performance Tracking',
        description: 'Monitor KPIs in real-time with interactive dashboards and alerts.',
        icon: 'chart-bar',
        color: 'from-violet-600 to-violet-800',
        iconPath: 'M8 13v-1m4 1v-3m4 3V8M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z'
    },
    {
        title: 'Goal Management',
        description: 'Set, track, and manage organizational and individual performance goals.',
        icon: 'flag',
        color: 'from-violet-500 to-violet-700',
        iconPath: 'M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9'
    },
    {
        title: 'Data Visualization',
        description: 'Transform complex data into actionable insights with powerful visualizations.',
        icon: 'presentation-chart-bar',
        color: 'from-violet-600 to-violet-800',
        iconPath: 'M8 13v-1m4 1v-3m4 3V8M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z'
    },
    {
        title: 'Collaboration Tools',
        description: 'Enable teams to collaborate effectively on achieving performance metrics.',
        icon: 'users',
        color: 'from-violet-500 to-violet-700',
        iconPath: 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z'
    },
    {
        title: 'Performance Reviews',
        description: 'Streamline employee evaluations with customizable review templates.',
        icon: 'clipboard-check',
        color: 'from-violet-600 to-violet-800',
        iconPath: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4'
    },
    {
        title: 'Analytics & Reporting',
        description: 'Generate comprehensive reports with just a few clicks.',
        icon: 'document-report',
        color: 'from-violet-500 to-violet-700',
        iconPath: 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'
    },
]);

// Setup animation dengan composable
const { visibleSections, setupIntersectionObserver } = useVisibilityAnimation();
setupIntersectionObserver('features');
</script>

<template>
    <section id="features" class="py-20 bg-gray-200 dark:bg-transparent transition-colors duration-300">
        <div class="container px-4 mx-auto sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto transition-all duration-500"
                :class="visibleSections.has('features') ? 'translate-y-0 opacity-100' : 'translate-y-10 opacity-0'">
                <h2 class="text-3xl text-violet-500 dark:text-violet-400 font-bold tracking-tight sm:text-4xl">
                    Powerful Features for Performance Excellence
                </h2>
                <p class="max-w-2xl mx-auto mt-4 text-lg text-gray-600 dark:text-gray-300">
                    Everything you need to track, analyze, and improve organizational performance in one integrated platform.
                </p>
            </div>
            <div class="grid grid-cols-1 gap-8 mt-16 sm:grid-cols-2 lg:grid-cols-3">
                <div v-for="(feature, index) in features" :key="index"
                    class="group p-6 transition-all duration-500 bg-white/80 dark:bg-gray-800/50 backdrop-blur-sm rounded-xl shadow-lg hover:shadow-violet-500/20 dark:hover:shadow-violet-400/20 hover:shadow-2xl transform hover:-translate-y-2 border border-gray-200 dark:border-gray-700 hover:border-violet-500/50 dark:hover:border-violet-400/50"
                    :class="[visibleSections.has('features') ? 'translate-y-0 opacity-100' : 'translate-y-10 opacity-0']"
                    :style="{ transitionDelay: `${Math.min(index * 150, 500)}ms` }">
                    
                    <!-- Gradient overlay on hover with dark mode -->
                    <div class="absolute inset-0 bg-gradient-to-br from-violet-600/10 dark:from-violet-500/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500 rounded-xl"></div>
                    
                    <div class="relative z-10">
                        <div class="w-12 h-12 mb-4 rounded-full flex items-center justify-center text-white bg-gradient-to-r group-hover:scale-110 transition-transform duration-300" :class="feature.color">
                            <!-- Add SVG icon -->
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="feature.iconPath" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-800 dark:text-gray-100 group-hover:text-violet-600 dark:group-hover:text-violet-200 transition-colors duration-300">{{ feature.title }}</h3>
                        <p class="mt-2 text-gray-600 dark:text-gray-400">{{ feature.description }}</p>
                        <div class="mt-4">
                            <a href="#" 
                                class="text-violet-500 dark:text-violet-400 hover:text-violet-600 dark:hover:text-violet-300 hover:underline inline-flex items-center group-hover:translate-x-1 transition-all duration-300">
                                Learn more
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1 group-hover:translate-x-1 transition-transform duration-300" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </a>
                        </div>
                    </div>
                    
                    <!-- Glow effect with dark mode -->
                    <div class="absolute inset-0 bg-violet-500/5 dark:bg-violet-400/5 rounded-xl opacity-0 group-hover:opacity-100 transition-opacity duration-500 blur-xl"></div>
                </div>
            </div>
        </div>
    </section>
</template>