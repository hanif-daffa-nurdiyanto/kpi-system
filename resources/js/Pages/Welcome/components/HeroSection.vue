<script setup>
import { ref, onMounted, computed } from "vue";

// Animation trigger references
const visibleSections = ref(new Set(["hero"]));
const animationStarted = ref(false);
const animationComplete = ref(false);
const hoverEffect = ref(false);

// Props
const props = defineProps({
    loginRoutes: {
        type: Object,
        default: () => ({
            superadmin: "/login/admin",
        }),
    },
});

// Methods
const visitLoginPage = (route) => {
    window.location.href = route;
};

const startAnimation = () => {
    setTimeout(() => {
        animationStarted.value = true;
    }, 300);

    setTimeout(() => {
        animationComplete.value = true;
    }, 1500);
};

const toggleHoverEffect = (status) => {
    hoverEffect.value = status;
};

const chartData = ref(
    Array.from({ length: 12 }, (_, i) => ({
        month: i + 1,
        value: Math.floor(Math.random() * 80) + 20,
    }))
);

// Icon data for dashboard boxes with enhanced dark mode colors
const boxIcons = [
    {
        icon: "💎",
        name: "Insights",
        color: "from-gray-600 to-gray-800 dark:from-gray-500 to-gray-700",
    },
    {
        icon: "⚡",
        name: "Speed",
        color: "from-gray-700 to-gray-900 dark:from-gray-600 to-gray-800",
    },
    {
        icon: "🎨",
        name: "Design",
        color: "from-gray-600 to-gray-800 dark:from-gray-500 to-gray-700",
    },
    {
        icon: "🚀",
        name: "Launch",
        color: "from-gray-700 to-gray-900 dark:from-gray-600 to-gray-800",
    },
];

onMounted(() => {
    // Set up intersection observer for animations
    const observerOptions = {
        root: null,
        rootMargin: "0px",
        threshold: 0.1,
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                visibleSections.value.add("hero");
                startAnimation();
            }
        });
    }, observerOptions);

    const element = document.getElementById("hero");
    if (element) observer.observe(element);

    // Start animation sequence for first load
    startAnimation();

    // Set chart animation interval
    setInterval(() => {
        chartData.value = chartData.value.map((item) => ({
            ...item,
            value: Math.floor(Math.random() * 80) + 20,
        }));
    }, 3000);
});

// Computed values for dashboard animation
const chartHeight = computed(() => {
    const maxValue = Math.max(...chartData.value.map((item) => item.value));
    return (value) => (value / maxValue) * 100 + "%";
});
</script>

<template>
    <!-- Hero Section with Enhanced Dark Mode Support -->
    <section
        id="hero"
        class="min-h-screen flex items-center relative overflow-hidden bg-gray-200 dark:bg-transparent transition-colors duration-300"
    >
        <!-- Grid pattern overlay with dark mode support -->
        <div
            class="absolute inset-0 bg-grid-gray-900/[0.02] dark:bg-grid-white/[0.01] bg-grid-16"
        ></div>

        <div class="container px-6 mx-auto sm:px-8 lg:px-12 relative z-10">
            <div class="grid lg:grid-cols-2 gap-16 items-center">
                <!-- Content Section - Left -->
                <div
                    class="text-center lg:text-left order-1 lg:order-1 mt-10 lg:mt-0"
                >
                    <!-- Badge with enhanced dark mode -->
                    <div
                        class="mt-10 inline-flex items-center px-4 py-2 text-sm font-medium bg-gray-200 text-gray-700 border border-gray-300 dark:bg-gray-800/70 dark:text-gray-300 dark:border-gray-600/30 backdrop-blur-sm rounded-full transform transition-all duration-700 delay-200"
                        :class="
                            animationStarted
                                ? 'translate-y-0 opacity-100'
                                : 'translate-y-8 opacity-0'
                        "
                    >
                        <span
                            class="w-2 h-2 bg-violet-500 dark:bg-violet-400 rounded-full mr-2 animate-ping"
                        ></span>
                        Next Generation Analytics
                    </div>

                    <!-- Main Heading with dark mode support -->
                    <h1
                        class="text-5xl md:text-7xl font-black leading-tight mb-6"
                    >
                        <div class="overflow-hidden">
                            <div
                                class="text-violet-500 dark:text-violet-400 transform transition-all duration-1000 delay-300"
                                :class="
                                    animationStarted
                                        ? 'translate-y-0'
                                        : 'translate-y-full'
                                "
                            >
                                Transform
                            </div>
                        </div>
                        <div class="overflow-hidden">
                            <div
                                class="text-violet-500 dark:text-violet-400 transform transition-all duration-1000 delay-500"
                                :class="
                                    animationStarted
                                        ? 'translate-y-0'
                                        : 'translate-y-full'
                                "
                            >
                                Your Vision
                            </div>
                        </div>
                        <div class="overflow-hidden">
                            <div
                                class="text-4xl md:text-5xl text-gray-600 dark:text-gray-400 transform transition-all duration-1000 delay-700"
                                :class="
                                    animationStarted
                                        ? 'translate-y-0'
                                        : 'translate-y-full'
                                "
                            >
                                Into Reality
                            </div>
                        </div>
                    </h1>

                    <!-- Description with dark mode -->
                    <p
                        class="text-lg md:text-xl text-gray-600 dark:text-gray-300 mb-10 leading-relaxed max-w-2xl transition-all duration-700 delay-900"
                        :class="
                            animationStarted
                                ? 'translate-y-0 opacity-100'
                                : 'translate-y-8 opacity-0'
                        "
                    >
                        Harness the power of advanced KPI tracking and real-time
                        analytics to drive unprecedented growth and performance
                        across your organization.
                    </p>

                    <!-- CTA Buttons with dark mode -->
                    <div
                        class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start transition-all duration-700 delay-1100"
                        :class="
                            animationStarted
                                ? 'translate-y-0 opacity-100'
                                : 'translate-y-8 opacity-0'
                        "
                    >
                        <button
                            @click="visitLoginPage(loginRoutes.superadmin)"
                            class="group relative px-8 py-4 bg-gradient-to-r from-violet-700 to-violet-600 dark:from-violet-600 dark:to-violet-500 text-white font-semibold rounded-xl overflow-hidden shadow-2xl hover:shadow-violet-500/25 dark:hover:shadow-violet-400/25 transform transition-all duration-300 hover:scale-105"
                        >
                            <span
                                class="relative z-10 flex items-center justify-center"
                            >
                                <span class="mr-2">Start Your Journey</span>
                                <svg
                                    class="w-5 h-5 transform group-hover:translate-x-1 transition-transform duration-300"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M13 7l5 5m0 0l-5 5m5-5H6"
                                    />
                                </svg>
                            </span>
                            <div
                                class="absolute inset-0 bg-gradient-to-r from-violet-600 to-violet-500 dark:from-violet-500 dark:to-violet-400 transform scale-x-0 group-hover:scale-x-100 transition-transform duration-300 origin-left"
                            ></div>
                        </button>

                        <a
                            href="#features"
                            class="group px-8 py-4 bg-gray-100/80 dark:bg-white/10 backdrop-blur-sm text-gray-700 dark:text-white font-semibold rounded-xl border border-gray-300 dark:border-white/20 hover:bg-gray-200/80 dark:hover:bg-white/20 transform transition-all duration-300 hover:scale-105 flex items-center justify-center"
                        >
                            <span class="mr-2">Explore Features</span>
                            <svg
                                class="w-5 h-5 transform group-hover:rotate-45 transition-transform duration-300"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                                />
                            </svg>
                        </a>
                    </div>

                    <!-- Stats with dark mode -->
                    <div
                        class="grid grid-cols-3 gap-8 mt-16 pt-8 border-t border-gray-300 dark:border-gray-700/50 transition-all duration-700 delay-1300"
                        :class="
                            animationStarted
                                ? 'translate-y-0 opacity-100'
                                : 'translate-y-8 opacity-0'
                        "
                    >
                        <div class="text-center">
                            <div
                                class="text-3xl font-bold text-gray-700 dark:text-gray-400 mb-1"
                            >
                                99%
                            </div>
                            <div
                                class="text-sm text-gray-500 dark:text-gray-500"
                            >
                                Accuracy
                            </div>
                        </div>
                        <div class="text-center">
                            <div
                                class="text-3xl font-bold text-gray-700 dark:text-gray-400 mb-1"
                            >
                                24/7
                            </div>
                            <div
                                class="text-sm text-gray-500 dark:text-gray-500"
                            >
                                Monitoring
                            </div>
                        </div>
                        <div class="text-center">
                            <div
                                class="text-3xl font-bold text-gray-700 dark:text-gray-400 mb-1"
                            >
                                10k+
                            </div>
                            <div
                                class="text-sm text-gray-500 dark:text-gray-500"
                            >
                                Users
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Dashboard Preview - Right with enhanced dark mode -->
                <div
                    class="order-2 lg:order-2 transition-all duration-1000 delay-800 mb-10 lg:mt-20"
                    @mouseenter="toggleHoverEffect(true)"
                    @mouseleave="toggleHoverEffect(false)"
                    :class="
                        animationStarted
                            ? 'translate-y-0 opacity-100'
                            : 'translate-y-12 opacity-0'
                    "
                >
                    <div class="relative perspective-1000">
                        <!-- Main Dashboard Container with enhanced dark mode -->
                        <div
                            class="relative bg-gradient-to-br from-gray-100/90 to-gray-200/90 dark:from-white/10 dark:to-white/5 backdrop-blur-xl rounded-3xl p-6 border border-gray-300/50 dark:border-white/20 shadow-2xl transform transition-all duration-700"
                            :class="[
                                hoverEffect
                                    ? 'rotate-y-5 scale-105'
                                    : 'rotate-y-0 scale-100',
                                animationComplete
                                    ? 'translate-y-0'
                                    : 'translate-y-8',
                            ]"
                        >
                            <!-- Header with dark mode -->
                            <div class="flex items-center justify-between mb-6">
                                <div class="flex items-center space-x-3">
                                    <div
                                        class="w-3 h-3 bg-red-400 dark:bg-red-500 rounded-full animate-pulse-slow"
                                    ></div>
                                    <div
                                        class="w-3 h-3 bg-yellow-400 dark:bg-yellow-500 rounded-full animate-pulse-slow"
                                        style="animation-delay: 0.2s"
                                    ></div>
                                    <div
                                        class="w-3 h-3 bg-green-400 dark:bg-green-500 rounded-full animate-pulse-slow"
                                        style="animation-delay: 0.4s"
                                    ></div>
                                </div>
                                <div
                                    class="text-gray-600 dark:text-white/60 text-sm font-mono"
                                >
                                    Live Dashboard
                                </div>
                            </div>

                            <!-- Metric Cards with enhanced dark mode -->
                            <div class="grid grid-cols-2 gap-4 mb-6">
                                <div
                                    v-for="(box, index) in boxIcons"
                                    :key="index"
                                    class="group relative overflow-hidden rounded-2xl p-4 cursor-pointer transform transition-all duration-700 hover:scale-110"
                                    :style="{
                                        transitionDelay: `${
                                            1000 + index * 150
                                        }ms`,
                                        opacity: animationComplete ? '1' : '0',
                                        transform: animationComplete
                                            ? 'translateY(0) rotateX(0)'
                                            : 'translateY(30px) rotateX(15deg)',
                                    }"
                                >
                                    <!-- Gradient Background with dark mode -->
                                    <div
                                        class="absolute inset-0 bg-gradient-to-br opacity-80 group-hover:opacity-100 transition-opacity duration-300"
                                        :class="box.color"
                                    ></div>

                                    <!-- Animated Pattern with dark mode -->
                                    <div
                                        class="absolute inset-0 bg-white/10 dark:bg-white/10 bg-opacity-20 group-hover:animate-shimmer"
                                    ></div>

                                    <!-- Content -->
                                    <div class="relative z-10">
                                        <div
                                            class="text-3xl mb-2 transform transition-all duration-300 group-hover:scale-125 group-hover:rotate-12"
                                        >
                                            {{ box.icon }}
                                        </div>
                                        <div
                                            class="text-sm font-semibold text-white"
                                        >
                                            {{ box.name }}
                                        </div>
                                        <div class="text-xs text-white/80 mt-1">
                                            Active
                                        </div>
                                    </div>

                                    <!-- Hover Glow with dark mode -->
                                    <div
                                        class="absolute inset-0 bg-white/20 dark:bg-white/20 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 blur-xl"
                                    ></div>
                                </div>
                            </div>

                            <!-- Animated Chart Area with enhanced dark mode -->
                            <div
                                class="bg-gradient-to-br from-gray-300/50 to-gray-400/50 dark:from-slate-800/50 dark:to-slate-900/50 rounded-2xl p-4 backdrop-blur-sm border border-gray-300/30 dark:border-white/10"
                            >
                                <div
                                    class="flex items-center justify-between mb-4"
                                >
                                    <h3
                                        class="text-gray-800 dark:text-white font-medium"
                                    >
                                        Performance Metrics
                                    </h3>
                                    <div class="flex items-center space-x-2">
                                        <div
                                            class="w-2 h-2 bg-gray-500 dark:bg-gray-400 rounded-full animate-pulse"
                                        ></div>
                                        <span
                                            class="text-gray-600 dark:text-gray-400 text-sm"
                                            >Live</span
                                        >
                                    </div>
                                </div>

                                <!-- Chart Bars with dark mode -->
                                <div
                                    class="flex items-end justify-between h-32 space-x-2"
                                >
                                    <div
                                        v-for="(item, index) in chartData"
                                        :key="`chart-${index}`"
                                        class="flex-1 bg-gradient-to-t from-gray-500 to-gray-400 dark:from-gray-600 dark:to-gray-500 rounded-t-lg relative overflow-hidden group cursor-pointer transition-all duration-500"
                                        :style="{
                                            height: animationComplete
                                                ? chartHeight(item.value)
                                                : '0%',
                                            transitionDelay: `${
                                                1500 + index * 100
                                            }ms`,
                                        }"
                                    >
                                        <!-- Animated glow effect with dark mode -->
                                        <div
                                            class="absolute inset-0 bg-gradient-to-t from-transparent to-gray-300/30 dark:to-white/20 opacity-0 group-hover:opacity-100 transition-opacity duration-300"
                                        ></div>
                                        <!-- Shimmer effect with dark mode -->
                                        <div
                                            class="absolute inset-0 bg-gradient-to-r from-transparent via-gray-300/30 dark:via-white/15 to-transparent -skew-x-12 transform translate-x-full group-hover:translate-x-[-200%] transition-transform duration-1000"
                                        ></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Real-time sync badge with dark mode -->
                            <div
                                class="absolute -bottom-4 -left-4 bg-gradient-to-r from-gray-600 to-gray-500 dark:from-gray-700 dark:to-gray-600 text-white px-4 py-2 rounded-full text-sm font-medium shadow-lg transform transition-all duration-700 delay-200"
                                :class="
                                    animationComplete
                                        ? 'translate-y-0 opacity-100'
                                        : 'translate-y-8 opacity-0'
                                "
                            >
                                Real-time Sync
                            </div>
                        </div>

                        <!-- Background Glow with dark mode -->
                        <div
                            class="absolute inset-0 bg-gradient-to-r from-gray-400/20 to-gray-500/20 dark:from-gray-800/20 dark:to-gray-700/20 rounded-3xl blur-3xl transform scale-110 -z-10 transition-opacity duration-700"
                            :class="hoverEffect ? 'opacity-100' : 'opacity-60'"
                        ></div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<style scoped>
/* Custom Animations */
@keyframes float {
    0%,
    100% {
        transform: translateY(0px) rotate(0deg);
    }
    50% {
        transform: translateY(-20px) rotate(5deg);
    }
}

@keyframes float-reverse {
    0%,
    100% {
        transform: translateY(0px) rotate(0deg);
    }
    50% {
        transform: translateY(20px) rotate(-5deg);
    }
}

@keyframes shimmer {
    0% {
        transform: translateX(-100%) skewX(-15deg);
    }
    100% {
        transform: translateX(200%) skewX(-15deg);
    }
}

@keyframes pulse-slow {
    0%,
    100% {
        opacity: 0.6;
    }
    50% {
        opacity: 1;
    }
}

.animate-float {
    animation: float 6s ease-in-out infinite;
}

.animate-float-reverse {
    animation: float-reverse 8s ease-in-out infinite;
}

.animate-shimmer {
    animation: shimmer 2s ease-in-out infinite;
}

.animate-pulse-slow {
    animation: pulse-slow 3s ease-in-out infinite;
}

.perspective-1000 {
    perspective: 1000px;
}

.rotate-y-5 {
    transform: rotateY(5deg);
}

.rotate-y-0 {
    transform: rotateY(0deg);
}

/* Grid Pattern with dark mode support */
.bg-grid-gray-900\/\[0\.02\] {
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32' width='32' height='32' fill='none' stroke='rgb(17 24 39 / 0.02)'%3e%3cpath d='m0 .5h32m-32 32v-32'/%3e%3c/svg%3e");
}

.bg-grid-white\/\[0\.01\] {
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32' width='32' height='32' fill='none' stroke='rgb(255 255 255 / 0.01)'%3e%3cpath d='m0 .5h32m-32 32v-32'/%3e%3c/svg%3e");
}

.bg-grid-16 {
    background-size: 16px 16px;
}

/* Hover Effects */
.group:hover .group-hover\:scale-125 {
    transform: scale(1.25);
}

.group:hover .group-hover\:rotate-12 {
    transform: rotate(12deg);
}

.group:hover .group-hover\:translate-x-1 {
    transform: translateX(0.25rem);
}

.group:hover .group-hover\:rotate-45 {
    transform: rotate(45deg);
}

.group:hover .group-hover\:translate-x-\[-200\%\] {
    transform: translateX(-200%);
}

/* Dark mode transitions */
* {
    transition-property: background-color, border-color, color, fill, stroke;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 300ms;
}
</style>
