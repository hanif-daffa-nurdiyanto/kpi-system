<script setup>
// import { useVisibilityAnimation } from '../../../Composables/useVisibilityAnimation';

// // Setup animation dengan composable
// const { visibleSections, setupIntersectionObserver } = useVisibilityAnimation();
// setupIntersectionObserver('cta');

const visitLoginPage = (route) => {
    window.location.href = route;
};

const loginRoutes = {
    superadmin: "/login",
};
</script>

<template>
    <!-- CTA Section -->
    <section
        id="cta"
        class="relative py-20 overflow-hidden transition-colors duration-500 bg-gray-200 dark:bg-transparent"
    >
        <!-- Content -->
        <div
            class="relative container px-4 mx-auto text-center sm:px-6 lg:px-8"
        >
            <h2
                class="text-4xl font-extrabold sm:text-5xl drop-shadow-lg text-violet-500 dark:text-white"
            >
                Ready to transform your performance management?
            </h2>
            <p
                class="mt-4 text-xl text-gray-500 dark:text-violet-100 max-w-2xl mx-auto"
            >
                Start your journey to
                <span class="font-semibold text-violet-400"
                    >data-driven performance excellence</span
                >
                today.
            </p>
            <div class="mt-10 flex justify-center">
                <button
                    @click="visitLoginPage(loginRoutes.superadmin)"
                    class="px-10 py-4 text-lg font-semibold text-white bg-gradient-to-r from-violet-500 to-blue-500 rounded-xl shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1 hover:from-violet-600 hover:to-blue-600 focus:ring-4 focus:ring-violet-300"
                >
                    🚀 Start Now
                </button>
            </div>
        </div>
    </section>
</template>
