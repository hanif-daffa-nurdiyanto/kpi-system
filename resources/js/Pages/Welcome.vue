<script setup>
import { Link } from '@inertiajs/vue3';
import { ref, onMounted, computed, watch } from 'vue';

// Define props from backend
const props = defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
    laravelVersion: String,
    phpVersion: String,
});

// Debug prop values
console.log('Props received:', props);

// Client testimonials
const testimonials = ref([
    { name: 'Sarah Chen', position: 'HR Director, TechCorp', content: 'This KPI system transformed how we measure performance. Our productivity increased by 27% in just three months.', avatar: '/api/placeholder/40/40' },
    { name: 'Michael Rodriguez', position: 'CEO, GrowthTech', content: 'The data visualization tools helped us identify bottlenecks we didn\'t even know existed. Game-changer for our quarterly planning.', avatar: '/api/placeholder/40/40' },
    { name: 'Aisha Johnson', position: 'Team Lead, InnovateCo', content: 'My team loves the collaborative features. Setting and tracking goals has never been easier or more transparent.', avatar: '/api/placeholder/40/40' },
]);

// Features with icons using Heroicons (assumed to be available in your project)
const features = ref([
    { title: 'Real-time Performance Tracking', description: 'Monitor KPIs in real-time with interactive dashboards and alerts.', icon: 'chart-bar', color: 'bg-blue-100 dark:bg-blue-900' },
    { title: 'Goal Management', description: 'Set, track, and manage organizational and individual performance goals.', icon: 'flag', color: 'bg-green-100 dark:bg-green-900' },
    { title: 'Data Visualization', description: 'Transform complex data into actionable insights with powerful visualizations.', icon: 'presentation-chart-bar', color: 'bg-purple-100 dark:bg-purple-900' },
    { title: 'Collaboration Tools', description: 'Enable teams to collaborate effectively on achieving performance metrics.', icon: 'users', color: 'bg-amber-100 dark:bg-amber-900' },
    { title: 'Performance Reviews', description: 'Streamline employee evaluations with customizable review templates.', icon: 'clipboard-check', color: 'bg-pink-100 dark:bg-pink-900' },
    { title: 'Analytics & Reporting', description: 'Generate comprehensive reports with just a few clicks.', icon: 'document-report', color: 'bg-indigo-100 dark:bg-indigo-900' },
]);

// Stats for dashboard impact
const stats = ref([
    { value: '87%', label: 'Performance Improvement', icon: 'trending-up' },
    { value: '3.5x', label: 'ROI on KPI Tracking', icon: 'cash' },
    { value: '42%', label: 'Time Saved on Reporting', icon: 'clock' },
    { value: '94%', label: 'User Satisfaction', icon: 'emoji-happy' },
]);

// FAQ items
const faqs = ref([
    { question: 'How quickly can we implement the KPI system?', answer: 'Most organizations complete implementation within 2-4 weeks, depending on the complexity of your performance metrics and the size of your team.' },
    { question: 'Can we customize the KPI metrics?', answer: 'Absolutely! The system is fully customizable to track the specific metrics that matter most to your organization.' },
    { question: 'Is training provided for our team?', answer: 'Yes, we provide comprehensive onboarding and training sessions to ensure your team can maximize the benefits of the system.' },
    { question: 'How secure is our performance data?', answer: 'Security is our top priority. We use enterprise-grade encryption and regular security audits to protect your sensitive performance data.' },
]);

// Animation trigger references
const activeSection = ref('hero');
const sections = ref(['hero', 'features', 'stats', 'testimonials', 'faq']);
const visibleSections = ref(new Set(['hero']));

// Dark mode toggle - FIX: Improved dark mode implementation
const isDarkMode = ref(false);
const toggleDarkMode = () => {
    isDarkMode.value = !isDarkMode.value;

    // Apply dark mode class to both html and body for better compatibility
    if (isDarkMode.value) {
        document.documentElement.classList.add('dark');
        document.body.classList.add('dark-mode');
    } else {
        document.documentElement.classList.remove('dark');
        document.body.classList.remove('dark-mode');
    }

    // Store preference in localStorage
    localStorage.setItem('darkMode', isDarkMode.value ? 'enabled' : 'disabled');
};

// Active FAQ item
const activeFaq = ref(null);
const toggleFaq = (index) => {
    activeFaq.value = activeFaq.value === index ? null : index;
};

// Testimonial slider
const currentTestimonial = ref(0);
const nextTestimonial = () => {
    currentTestimonial.value = (currentTestimonial.value + 1) % testimonials.value.length;
};
const prevTestimonial = () => {
    currentTestimonial.value = (currentTestimonial.value - 1 + testimonials.value.length) % testimonials.value.length;
};

// Login routes with beforehand initialization to prevent white flash
const loginRoutes = {
    employee: '/app',
    superadmin: '/admin'
};

// For debugging login issue
const isLoginAvailable = computed(() => {
    // Always return true to ensure login buttons are shown
    return true;
});

// FIX: Added login page loading optimization
const visitLoginPage = (route) => {
    // Optional: Show loading spinner or indicator here
    // For example:
    // isLoading.value = true;

    // Navigate to login route
    window.location.href = route;

    // Return false to prevent default behavior if using <a> tags
    return false;
};

onMounted(() => {
    // FIX: Check for stored dark mode preference and apply it
    const savedDarkMode = localStorage.getItem('darkMode');
    if (savedDarkMode === 'enabled') {
        isDarkMode.value = true;
        document.documentElement.classList.add('dark');
        document.body.classList.add('dark-mode');
    } else {
        // Also check for system preference
        const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
        isDarkMode.value = mediaQuery.matches;
        if (isDarkMode.value) {
            document.documentElement.classList.add('dark');
            document.body.classList.add('dark-mode');
        }
    }

    // Listen for system dark mode changes
    const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
    mediaQuery.addEventListener('change', event => {
        // Only apply if user hasn't manually set preference
        if (!localStorage.getItem('darkMode')) {
            isDarkMode.value = event.matches;
            document.documentElement.classList.toggle('dark', isDarkMode.value);
            document.body.classList.toggle('dark-mode', isDarkMode.value);
        }
    });

    // Set up intersection observers for animations
    const observerOptions = {
        root: null,
        rootMargin: '0px',
        threshold: 0.1
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                visibleSections.value.add(entry.target.id);
                activeSection.value = entry.target.id;
            }
        });
    }, observerOptions);

    sections.value.forEach(section => {
        const element = document.getElementById(section);
        if (element) observer.observe(element);
    });

    // Auto-rotate testimonials
    const testimonialInterval = setInterval(nextTestimonial, 5000);
    return () => clearInterval(testimonialInterval);
});
</script>

<template>
    <div class="min-h-screen bg-gray-50 dark:bg-gray-950 transition-colors duration-300">
        <!-- Header Navigation -->
        <header class="fixed top-0 left-0 right-0 z-50 border-b border-gray-200 dark:border-gray-800 bg-white/90 dark:bg-gray-950/90 backdrop-blur-sm transition-all duration-300">
            <div class="container flex items-center justify-between px-4 py-4 mx-auto sm:px-6 lg:px-8">
                <div class="flex items-center space-x-2">
                    <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-green-500 rounded-lg flex items-center justify-center">
                        <span class="text-white font-bold">KPI</span>
                    </div>
                    <h1 class="text-xl font-semibold text-gray-900 dark:text-white">KPI System</h1>
                </div>

                <div class="flex items-center space-x-4">
                    <!-- Dark mode toggle - FIX: Added active state classes -->
                    <!-- <button @click="toggleDarkMode" class="p-2 rounded-full hover:bg-gray-200 dark:hover:bg-gray-800 transition-colors">
                        <svg v-if="isDarkMode" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" clip-rule="evenodd" />
                        </svg>
                        <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-700" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z" />
                        </svg>
                    </button> -->

                    <!-- FIX: Added preload indication and avoid white flash -->
                    <div class="flex items-center space-x-3">
                        <!-- Option 1: Using Inertia Link with preload event hook -->
                        <button @click="visitLoginPage(loginRoutes.employee)"
                                class="px-4 py-2 cursor-pointer text-sm font-medium text-white bg-green-600 rounded-md hover:bg-green-700 transition-colors">
                        Employee Login
                        </button>

                        <button @click="visitLoginPage(loginRoutes.superadmin)"
                                class="px-4 py-2 cursor-pointer text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 transition-colors">
                        Admin Login
                        </button>
                    </div>
                </div>
            </div>
        </header>

        <!-- Hero Section -->
        <section id="hero" class="pt-36 pb-24 bg-gradient-to-br from-white to-blue-50 dark:from-gray-900 dark:to-gray-800 transition-colors duration-300">
            <div class="container px-4 mx-auto sm:px-6 lg:px-8">
                <div class="flex flex-col lg:flex-row items-center justify-between">
                    <div class="w-full lg:w-1/2 mb-10 lg:mb-0">
                        <h2 class="text-4xl md:text-5xl font-bold text-gray-900 dark:text-white leading-tight transform transition-transform duration-500"
                            :class="visibleSections.has('hero') ? 'translate-y-0 opacity-100' : 'translate-y-10 opacity-0'">
                            Unlock Your Organization's<br/><span class="text-blue-600 dark:text-blue-400">Full Potential</span>
                        </h2>
                        <p class="mt-6 text-xl text-gray-600 dark:text-gray-300 max-w-lg transition-all duration-500 delay-150"
                            :class="visibleSections.has('hero') ? 'translate-y-0 opacity-100' : 'translate-y-10 opacity-0'">
                            Our KPI system helps you set, track, and achieve your most important performance goals with powerful analytics and collaboration tools.
                        </p>
                        <div class="mt-8 flex flex-wrap gap-4 transition-all duration-500 delay-300"
                            :class="visibleSections.has('hero') ? 'translate-y-0 opacity-100' : 'translate-y-10 opacity-0'">
                            <button @click="visitLoginPage(loginRoutes.superadmin)" class="px-6 py-3 cursor-pointer text-base font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 shadow-lg hover:shadow-xl transition-all transform hover:-translate-y-1">
                                Get Started
                            </button>
                            <a href="#features" class="px-6 py-3 text-base font-medium text-blue-600 bg-white dark:bg-gray-800 dark:text-blue-400 border border-blue-600 rounded-md hover:bg-blue-50 dark:hover:bg-gray-700 transition-all">
                                Learn More
                            </a>
                        </div>
                    </div>
                    <div class="w-full lg:w-1/2 transition-all duration-500 delay-300"
                        :class="visibleSections.has('hero') ? 'translate-y-0 opacity-100 scale-100' : 'translate-y-10 opacity-0 scale-95'">
                        <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-2xl p-4 transform rotate-1 transition-transform hover:rotate-0">
                            <!-- Mocked dashboard image -->
                            <div class="bg-gray-100 dark:bg-gray-700 rounded-lg h-64 overflow-hidden">
                                <div class="p-4">
                                    <div class="h-6 bg-blue-200 dark:bg-blue-700 w-1/3 rounded mb-4"></div>
                                    <div class="flex space-x-4 mb-4">
                                        <div class="h-20 bg-green-200 dark:bg-green-700 w-1/4 rounded"></div>
                                        <div class="h-20 bg-purple-200 dark:bg-purple-700 w-1/4 rounded"></div>
                                        <div class="h-20 bg-yellow-200 dark:bg-yellow-700 w-1/4 rounded"></div>
                                        <div class="h-20 bg-red-200 dark:bg-red-700 w-1/4 rounded"></div>
                                    </div>
                                    <div class="h-32 bg-gray-200 dark:bg-gray-600 rounded mb-4"></div>
                                    <div class="h-6 bg-blue-200 dark:bg-blue-700 w-1/2 rounded"></div>
                                </div>
                            </div>
                            <div class="absolute -bottom-3 -right-3 bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium">
                                Real-time Analytics
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section id="features" class="py-20 bg-white dark:bg-gray-900 transition-colors duration-300">
            <div class="container px-4 mx-auto sm:px-6 lg:px-8">
                <div class="text-center max-w-3xl mx-auto transition-all duration-500"
                    :class="visibleSections.has('features') ? 'translate-y-0 opacity-100' : 'translate-y-10 opacity-0'">
                    <h2 class="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl dark:text-white">
                        Powerful Features for Performance Excellence
                    </h2>
                    <p class="max-w-2xl mx-auto mt-4 text-lg text-gray-600 dark:text-gray-400">
                        Everything you need to track, analyze, and improve organizational performance in one integrated platform.
                    </p>
                </div>
                <div class="grid grid-cols-1 gap-8 mt-16 sm:grid-cols-2 lg:grid-cols-3">
                    <div v-for="(feature, index) in features" :key="index"
                        class="p-6 transition-all duration-500 bg-white dark:bg-gray-800 rounded-lg shadow-md hover:shadow-xl transform hover:-translate-y-1 border border-gray-100 dark:border-gray-700"
                        :class="[visibleSections.has('features') ? 'translate-y-0 opacity-100' : 'translate-y-10 opacity-0',
                                 `delay-${Math.min(index * 150, 500)}`]">
                        <div class="w-12 h-12 mb-4 rounded-full flex items-center justify-center text-white" :class="feature.color">
                            <!-- Placeholder for icon -->
                            <div class="w-6 h-6"></div>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-white">{{ feature.title }}</h3>
                        <p class="mt-2 text-gray-600 dark:text-gray-400">{{ feature.description }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Stats Section -->
        <section id="stats" class="py-16 bg-blue-600 dark:bg-blue-800 transition-colors duration-300">
            <div class="container px-4 mx-auto sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-4">
                    <div v-for="(stat, index) in stats" :key="index"
                        class="text-center transition-all duration-500"
                        :class="[visibleSections.has('stats') ? 'translate-y-0 opacity-100' : 'translate-y-10 opacity-0',
                                 `delay-${Math.min(index * 150, 500)}`]">
                        <div class="text-4xl font-bold text-white">{{ stat.value }}</div>
                        <div class="mt-2 text-blue-100">{{ stat.label }}</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Testimonials Section -->
        <!-- <section id="testimonials" class="py-20 bg-gray-50 dark:bg-gray-900 transition-colors duration-300">
            <div class="container px-4 mx-auto sm:px-6 lg:px-8">
                <div class="text-center max-w-3xl mx-auto transition-all duration-500"
                    :class="visibleSections.has('testimonials') ? 'translate-y-0 opacity-100' : 'translate-y-10 opacity-0'">
                    <h2 class="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl dark:text-white">
                        What Our Clients Say
                    </h2>
                    <p class="max-w-2xl mx-auto mt-4 text-lg text-gray-600 dark:text-gray-400">
                        Organizations across industries have transformed their performance management with our KPI system.
                    </p>
                </div>

                <div class="mt-16 max-w-3xl mx-auto relative">
                    <div class="bg-white dark:bg-gray-800 p-8 rounded-lg shadow-lg transition-all duration-500 transform"
                        :class="visibleSections.has('testimonials') ? 'translate-y-0 opacity-100' : 'translate-y-10 opacity-0'">
                        <div class="text-gray-600 dark:text-gray-300 text-lg italic">
                            "{{ testimonials[currentTestimonial].content }}"
                        </div>
                        <div class="mt-6 flex items-center">
                            <img :src="testimonials[currentTestimonial].avatar" alt="Avatar" class="w-10 h-10 rounded-full mr-4">
                            <div>
                                <div class="font-semibold text-gray-900 dark:text-white">{{ testimonials[currentTestimonial].name }}</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">{{ testimonials[currentTestimonial].position }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 flex justify-center space-x-2">
                        <button @click="prevTestimonial" class="p-2 rounded-full bg-white dark:bg-gray-800 shadow hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-700 dark:text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                        </button>
                        <button @click="nextTestimonial" class="p-2 rounded-full bg-white dark:bg-gray-800 shadow hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-700 dark:text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </section> -->

        <!-- FAQ Section -->
        <section id="faq" class="py-20 bg-white dark:bg-gray-900 transition-colors duration-300">
            <div class="container px-4 mx-auto sm:px-6 lg:px-8">
                <div class="text-center max-w-3xl mx-auto transition-all duration-500"
                    :class="visibleSections.has('faq') ? 'translate-y-0 opacity-100' : 'translate-y-10 opacity-0'">
                    <h2 class="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl dark:text-white">
                        Frequently Asked Questions
                    </h2>
                    <p class="max-w-2xl mx-auto mt-4 text-lg text-gray-600 dark:text-gray-400">
                        Find answers to common questions about our KPI system.
                    </p>
                </div>

                <div class="mt-12 max-w-3xl mx-auto divide-y divide-gray-200 dark:divide-gray-700">
                    <div v-for="(item, index) in faqs" :key="index" class="py-6 transition-all duration-300"
                        :class="visibleSections.has('faq') ? 'opacity-100' : 'opacity-0'">
                        <button @click="toggleFaq(index)" class="flex justify-between items-center w-full text-left">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white">{{ item.question }}</h3>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500 dark:text-gray-400 transition-transform duration-300"
                                :class="activeFaq === index ? 'transform rotate-180' : ''"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div v-show="activeFaq === index" class="mt-2 text-gray-600 dark:text-gray-400 transition-all duration-300 overflow-hidden">
                            <p>{{ item.answer }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="py-20 bg-gradient-to-r from-blue-500 to-blue-700 dark:from-blue-700 dark:to-blue-900 transition-colors duration-300">
            <div class="container px-4 mx-auto text-center sm:px-6 lg:px-8">
                <h2 class="text-3xl font-bold text-white sm:text-4xl">Ready to transform your performance management?</h2>
                <p class="mt-4 text-xl text-blue-100 max-w-2xl mx-auto">
                    Start your journey to data-driven performance excellence today.
                </p>
                <div class="mt-8 flex justify-center">
                    <Link :href="loginRoutes.employee" class="px-8 py-4 text-lg font-medium text-blue-700 bg-white rounded-md hover:bg-blue-50 shadow-lg hover:shadow-xl transition-all transform hover:-translate-y-1">
                        Start Now
                    </Link>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="py-12 bg-gray-900 dark:bg-gray-950 text-white transition-colors duration-300">
            <div class="container px-4 mx-auto sm:px-6 lg:px-8">
                <div class="flex flex-col md:flex-row justify-between items-center">
                    <div class="mb-6 md:mb-0">
                        <div class="flex items-center space-x-2">
                            <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-green-500 rounded-lg flex items-center justify-center">
                                <span class="text-white font-bold">KPI</span>
                            </div>
                            <h1 class="text-xl font-semibold text-white">KPI System</h1>
                        </div>
                        <p class="mt-2 text-gray-400 max-w-md">
                            Empowering organizations to achieve their performance goals through data-driven insights.
                        </p>
                    </div>
                    <div>
                        <div class="flex space-x-6">
                            <a href="#" class="text-gray-400 hover:text-white transition-colors">
                                <span class="sr-only">Twitter</span>
                                <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M8.29 20.251c7.547 0 11.675-6.253 11.675-11.675 0-.178 0-.355-.012-.53A8.348 8.348 0 0022 5.92a8.19 8.19 0 01-2.357.646 4.118 4.118 0 001.804-2.27 8.224 8.224 0 01-2.605.996 4.107 4.107 0 00-6.993 3.743 11.65 11.65 0 01-8.457-4.287 4.106 4.106 0 001.27 5.477A4.072 4.072 0 012.8 9.713v.052a4.105 4.105 0 003.292 4.022 4.095 4.095 0 01-1.853.07 4.108 4.108 0 003.834 2.85A8.233 8.233 0 012 18.407a11.616 11.616 0 006.29 1.84"></path>
                                </svg>
                            </a>
                            <a href="#" class="text-gray-400 hover:text-white transition-colors">
                                <span class="sr-only">LinkedIn</span>
                                <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"></path>
                                </svg>
                            </a>
                            <a href="#" class="text-gray-400 hover:text-white transition-colors">
                                <span class="sr-only">GitHub</span>
                                <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
                                    <path fill-rule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z" clip-rule="evenodd"></path>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="mt-8 pt-8 border-t border-gray-800 text-center text-gray-400 text-sm">
                    <p>&copy; {{ new Date().getFullYear() }} KPI System. All rights reserved.</p>
                </div>
            </div>
        </footer>
    </div>
</template>