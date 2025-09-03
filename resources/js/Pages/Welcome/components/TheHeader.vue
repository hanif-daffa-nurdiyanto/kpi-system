<template>
    <header
        class="fixed top-0 left-0 right-0 z-50 bg-white/95 dark:bg-slate-900/95 backdrop-blur-md border-b border-slate-200/50 dark:border-slate-700/50 shadow-sm transition-all duration-300"
    >
        <div class="container mx-auto px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Logo & Brand -->
                <div class="flex items-center space-x-4">
                    <div class="relative group">
                        <img
                            class="h-20"
                            :src="isDarkMode ? '/assets/kpi_logo_dark.png' : '/assets/kpi_logo_light.png'"
                            alt="KPI Logo"
                        />
                    </div>
                </div>

                <!-- Navigation & Controls -->
                <div class="hidden lg:flex items-center space-x-8">
                    <!-- Navigation Links -->
                    <nav class="flex space-x-1">
                        <a
                            v-for="link in navLinks"
                            :key="link.name"
                            :href="link.href"
                            :class="[
                                'relative px-4 py-2 text-sm font-medium rounded-lg transition-all duration-300',
                                currentPath === link.href
                                    ? 'text-violet-600 dark:text-violet-400 bg-violet-50 dark:bg-violet-900/20 shadow-sm'
                                    : 'text-slate-600 dark:text-slate-300 hover:text-violet-600 dark:hover:text-violet-400 hover:bg-slate-50 dark:hover:bg-slate-800/50',
                            ]"
                        >
                            {{ link.name }}
                        </a>
                    </nav>

                    <!-- Dark Mode Toggle -->
                    <div class="relative">
                        <button
                            @click="props.toggleDarkMode"
                            class="group relative p-3 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 transition-all duration-300 hover:scale-110"
                        >
                            <div
                                class="absolute inset-0 bg-gradient-to-r from-yellow-400 to-orange-500 dark:from-blue-500 dark:to-purple-600 rounded-xl opacity-0 group-hover:opacity-20 transition-opacity duration-300"
                            ></div>
                            <!-- Sun Icon -->
                            <svg
                                v-if="props.isDarkMode"
                                class="relative w-5 h-5 text-yellow-500 group-hover:text-yellow-400 transition-colors duration-300"
                                fill="currentColor"
                                viewBox="0 0 20 20"
                            >
                                <path
                                    fill-rule="evenodd"
                                    d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z"
                                />
                            </svg>
                            <!-- Moon Icon -->
                            <svg
                                v-else
                                class="relative w-5 h-5 text-slate-600 group-hover:text-blue-500 transition-colors duration-300"
                                fill="currentColor"
                                viewBox="0 0 20 20"
                            >
                                <path
                                    d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"
                                />
                            </svg>
                        </button>
                    </div>

                    <!-- Login Buttons -->
                    <div class="flex space-x-3">
                        <button
                            @click="visitLoginPage(loginRoutes.employee)"
                            class="group relative px-6 py-2.5 text-sm font-semibold text-white rounded-lg overflow-hidden transition-all duration-300 hover:scale-105 hover:shadow-lg"
                        >
                            <div
                                class="absolute inset-0 bg-gradient-to-r from-emerald-500 to-teal-600 group-hover:from-emerald-600 group-hover:to-teal-700 transition-all duration-300"
                            ></div>
                            <span class="relative flex items-center space-x-2">
                                <svg class="w-6 h-6 text-white" fill="currentColor" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><path d="M320 312C386.3 312 440 258.3 440 192C440 125.7 386.3 72 320 72C253.7 72 200 125.7 200 192C200 258.3 253.7 312 320 312zM290.3 368C191.8 368 112 447.8 112 546.3C112 562.7 125.3 576 141.7 576L498.3 576C514.7 576 528 562.7 528 546.3C528 447.8 448.2 368 349.7 368L290.3 368z"/></svg>
                                <span>Employee Login</span>
                            </span>
                        </button>

                        <button
                            @click="visitLoginPage(loginRoutes.superadmin)"
                            class="group relative px-6 py-2.5 text-sm font-semibold text-white rounded-lg overflow-hidden transition-all duration-300 hover:scale-105 hover:shadow-lg"
                        >
                            <div
                                class="absolute inset-0 bg-gradient-to-r from-violet-500 to-purple-600 group-hover:from-violet-600 group-hover:to-purple-700 transition-all duration-300"
                            ></div>
                            <span class="relative flex items-center space-x-2">
                                <svg class="w-6 h-6 text-white" fill="currentColor" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><path d="M256.5 72C322.8 72 376.5 125.7 376.5 192C376.5 258.3 322.8 312 256.5 312C190.2 312 136.5 258.3 136.5 192C136.5 125.7 190.2 72 256.5 72zM226.7 368L286.1 368L287.6 368C274.7 394.8 279.8 426.2 299.1 447.5C278.9 469.8 274.3 503.3 289.7 530.9L312.2 571.3C313.1 572.9 314.1 574.5 315.1 576L78.1 576C61.7 576 48.4 562.7 48.4 546.3C48.4 447.8 128.2 368 226.7 368zM432.6 311.6C432.6 298.3 443.3 287.6 456.6 287.6L504.6 287.6C517.9 287.6 528.6 298.3 528.6 311.6L528.6 317.7C528.6 336.6 552.7 350.5 569.1 341.1L574.1 338.2C585.7 331.5 600.6 335.6 607.1 347.3L629.5 387.5C635.7 398.7 632.1 412.7 621.3 419.5L616.6 422.4C600.4 432.5 600.4 462.3 616.6 472.5L621.2 475.4C632 482.2 635.7 496.2 629.5 507.4L607 547.8C600.5 559.5 585.6 563.7 574 556.9L569.1 554C552.7 544.5 528.6 558.5 528.6 577.4L528.6 583.5C528.6 596.8 517.9 607.5 504.6 607.5L456.6 607.5C443.3 607.5 432.6 596.8 432.6 583.5L432.6 577.6C432.6 558.6 408.4 544.6 391.9 554.1L387.1 556.9C375.5 563.6 360.7 559.5 354.1 547.8L331.5 507.4C325.3 496.2 328.9 482.1 339.8 475.3L344.2 472.6C360.5 462.5 360.5 432.5 344.2 422.4L339.7 419.6C328.8 412.8 325.2 398.7 331.4 387.5L353.9 347.2C360.4 335.5 375.3 331.4 386.8 338.1L391.6 340.9C408.1 350.4 432.3 336.4 432.3 317.4L432.3 311.5zM532.5 447.8C532.5 419.1 509.2 395.8 480.5 395.8C451.8 395.8 428.5 419.1 428.5 447.8C428.5 476.5 451.8 499.8 480.5 499.8C509.2 499.8 532.5 476.5 532.5 447.8z"/></svg>
                                <span>Admin Login</span>
                            </span>
                        </button>
                    </div>
                </div>

                <!-- Mobile Controls -->
                <div class="flex items-center space-x-3 lg:hidden">
                    <!-- Mobile Dark Mode Toggle -->
                    <button
                        @click="props.toggleDarkMode"
                        class="p-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 transition-all duration-300"
                    >
                        <svg
                            v-if="props.isDarkMode"
                            class="w-5 h-5 text-yellow-500"
                            fill="currentColor"
                            viewBox="0 0 20 20"
                        >
                            <path
                                fill-rule="evenodd"
                                d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z"
                            />
                        </svg>
                        <svg
                            v-else
                            class="w-5 h-5 text-slate-600"
                            fill="currentColor"
                            viewBox="0 0 20 20"
                        >
                            <path
                                d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"
                            />
                        </svg>
                    </button>

                    <!-- Mobile Menu Button -->
                    <button
                        @click="toggleMobileMenu"
                        class="relative p-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 transition-all duration-300"
                    >
                        <div
                            class="w-6 h-6 flex flex-col justify-center items-center"
                        >
                            <span
                                :class="[
                                    'block h-0.5 w-6 bg-slate-600 dark:bg-slate-300 transition-all duration-300',
                                    mobileMenuOpen
                                        ? 'rotate-45 translate-y-0.5'
                                        : '-translate-y-1',
                                ]"
                            ></span>
                            <span
                                :class="[
                                    'block h-0.5 w-6 bg-slate-600 dark:bg-slate-300 transition-all duration-300',
                                    mobileMenuOpen
                                        ? 'opacity-0'
                                        : 'opacity-100',
                                ]"
                            ></span>
                            <span
                                :class="[
                                    'block h-0.5 w-6 bg-slate-600 dark:bg-slate-300 transition-all duration-300',
                                    mobileMenuOpen
                                        ? '-rotate-45 -translate-y-0.5'
                                        : 'translate-y-1',
                                ]"
                            ></span>
                        </div>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div
            :class="[
                'lg:hidden overflow-hidden transition-all duration-300 ease-in-out',
                mobileMenuOpen ? 'max-h-96 opacity-100' : 'max-h-0 opacity-0',
            ]"
        >
            <div
                class="bg-white/95 dark:bg-slate-900/95 backdrop-blur-md border-t border-slate-200/50 dark:border-slate-700/50"
            >
                <div class="px-6 py-6 space-y-4">
                    <!-- Mobile Navigation -->
                    <nav class="space-y-2">
                        <a
                            v-for="link in navLinks"
                            :key="link.name"
                            :href="link.href"
                            :class="[
                                'block px-4 py-3 text-sm font-medium rounded-xl transition-all duration-300',
                                currentPath === link.href
                                    ? 'text-violet-600 dark:text-violet-400 bg-violet-50 dark:bg-violet-900/20'
                                    : 'text-slate-600 dark:text-slate-300 hover:text-violet-600 dark:hover:text-violet-400 hover:bg-slate-50 dark:hover:bg-slate-800/50',
                            ]"
                        >
                            {{ link.name }}
                        </a>
                    </nav>

                    <!-- Mobile Login Buttons -->
                    <div
                        class="space-y-3 pt-4 border-t border-slate-200 dark:border-slate-700"
                    >
                        <button
                            @click="visitLoginPage(props.loginRoutes.employee)"
                            class="w-full flex items-center justify-center space-x-2 px-4 py-3 text-sm font-semibold text-white bg-gradient-to-r from-emerald-500 to-teal-600 rounded-xl hover:from-emerald-600 hover:to-teal-700 transition-all duration-300 shadow-lg hover:shadow-xl"
                        >
                            <svg class="w-6 h-6 text-white" fill="currentColor" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><path d="M320 312C386.3 312 440 258.3 440 192C440 125.7 386.3 72 320 72C253.7 72 200 125.7 200 192C200 258.3 253.7 312 320 312zM290.3 368C191.8 368 112 447.8 112 546.3C112 562.7 125.3 576 141.7 576L498.3 576C514.7 576 528 562.7 528 546.3C528 447.8 448.2 368 349.7 368L290.3 368z"/></svg>
                            <span>Employee Login</span>
                        </button>

                        <button
                            @click="
                                visitLoginPage(props.loginRoutes.superadmin)
                            "
                            class="w-full flex items-center justify-center space-x-2 px-4 py-3 text-sm font-semibold text-white bg-gradient-to-r from-violet-500 to-purple-600 rounded-xl hover:from-violet-600 hover:to-purple-700 transition-all duration-300 shadow-lg hover:shadow-xl"
                        >
                            <svg class="w-6 h-6 text-white" fill="currentColor" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><path d="M256.5 72C322.8 72 376.5 125.7 376.5 192C376.5 258.3 322.8 312 256.5 312C190.2 312 136.5 258.3 136.5 192C136.5 125.7 190.2 72 256.5 72zM226.7 368L286.1 368L287.6 368C274.7 394.8 279.8 426.2 299.1 447.5C278.9 469.8 274.3 503.3 289.7 530.9L312.2 571.3C313.1 572.9 314.1 574.5 315.1 576L78.1 576C61.7 576 48.4 562.7 48.4 546.3C48.4 447.8 128.2 368 226.7 368zM432.6 311.6C432.6 298.3 443.3 287.6 456.6 287.6L504.6 287.6C517.9 287.6 528.6 298.3 528.6 311.6L528.6 317.7C528.6 336.6 552.7 350.5 569.1 341.1L574.1 338.2C585.7 331.5 600.6 335.6 607.1 347.3L629.5 387.5C635.7 398.7 632.1 412.7 621.3 419.5L616.6 422.4C600.4 432.5 600.4 462.3 616.6 472.5L621.2 475.4C632 482.2 635.7 496.2 629.5 507.4L607 547.8C600.5 559.5 585.6 563.7 574 556.9L569.1 554C552.7 544.5 528.6 558.5 528.6 577.4L528.6 583.5C528.6 596.8 517.9 607.5 504.6 607.5L456.6 607.5C443.3 607.5 432.6 596.8 432.6 583.5L432.6 577.6C432.6 558.6 408.4 544.6 391.9 554.1L387.1 556.9C375.5 563.6 360.7 559.5 354.1 547.8L331.5 507.4C325.3 496.2 328.9 482.1 339.8 475.3L344.2 472.6C360.5 462.5 360.5 432.5 344.2 422.4L339.7 419.6C328.8 412.8 325.2 398.7 331.4 387.5L353.9 347.2C360.4 335.5 375.3 331.4 386.8 338.1L391.6 340.9C408.1 350.4 432.3 336.4 432.3 317.4L432.3 311.5zM532.5 447.8C532.5 419.1 509.2 395.8 480.5 395.8C451.8 395.8 428.5 419.1 428.5 447.8C428.5 476.5 451.8 499.8 480.5 499.8C509.2 499.8 532.5 476.5 532.5 447.8z"/></svg>
                            <span>Admin Login</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </header>
</template>

<script setup>
import { ref, onMounted } from "vue";

// Props
const props = defineProps({
    isDarkMode: {
        type: Boolean,
        default: false,
    },
    toggleDarkMode: {
        type: Function,
        required: true,
    },
    loginRoutes: {
        type: Object,
        default: () => ({
            employee: "/login/employee",
            superadmin: "/login/admin",
        }),
    },
});

// State
const mobileMenuOpen = ref(false);
const currentPath = ref("");

// Set current path on mount
onMounted(() => {
    currentPath.value = window.location.pathname;
});

// Navigation Links
const navLinks = ref([
    { name: "Home", href: "/" },
    { name: "Features", href: "/features" },
]);

// Toggle Mobile Menu
const toggleMobileMenu = () => {
    mobileMenuOpen.value = !mobileMenuOpen.value;
};

// Redirect to Login
const visitLoginPage = (route) => {
    window.location.href = route;
};
</script>
