<script setup>
import { ref } from 'vue';
import { useVisibilityAnimation } from '../../../Composables/useVisibilityAnimation';

// FAQ items
const faqs = ref([
    { question: 'How quickly can we implement the KPI system?', answer: 'Most organizations complete implementation within 2-4 weeks, depending on the complexity of your performance metrics and the size of your team.' },
    { question: 'Can we customize the KPI metrics?', answer: 'Absolutely! The system is fully customizable to track the specific metrics that matter most to your organization.' },
    { question: 'Is training provided for our team?', answer: 'Yes, we provide comprehensive onboarding and training sessions to ensure your team can maximize the benefits of the system.' },
    { question: 'How secure is our performance data?', answer: 'Security is our top priority. We use enterprise-grade encryption and regular security audits to protect your sensitive performance data.' },
]);

// Active FAQ item
const activeFaq = ref(null);
const toggleFaq = (index) => {
    activeFaq.value = activeFaq.value === index ? null : index;
};

// Setup animation dengan composable
const { visibleSections, setupIntersectionObserver } = useVisibilityAnimation();
setupIntersectionObserver('faq');
</script>

<template>
    <section id="faq" class="py-20 transition-colors duration-300 bg-gray-200 dark:bg-transparent">
        <div class="container px-4 mx-auto sm:px-6 lg:px-8">
            
            <!-- Title -->
            <div class="text-center max-w-3xl mx-auto transition-all duration-500"
                :class="visibleSections.has('faq') ? 'translate-y-0 opacity-100' : 'translate-y-10 opacity-0'">
                <h2 class="text-3xl font-bold tracking-tight text-violet-500 sm:text-4xl">
                    Frequently Asked Questions
                </h2>
                <p class="max-w-2xl mx-auto mt-4 text-lg text-gray-600 dark:text-gray-200">
                    Find answers to common questions about our KPI system.
                </p>
            </div>

            <!-- FAQ Cards -->
            <div class="mt-12 max-w-3xl mx-auto space-y-4">
                <div v-for="(item, index) in faqs" :key="index"
                    class="transition-all duration-500 rounded-xl shadow-md overflow-hidden"
                    :class="[
                        visibleSections.has('faq') ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4',
                        activeFaq === index ? 'bg-white dark:bg-gray-700/40 text-white' : ' bg-white dark:bg-gray-700/40 text-white'
                    ]">
                    
                    <button @click="toggleFaq(index)" 
                        class="flex justify-between items-center w-full px-6 py-4 text-left focus:outline-none">
                        <h3 class="text-lg font-semibold text-gray-600 dark:text-gray-200">
                            {{ item.question }}
                        </h3>
                        <svg xmlns="http://www.w3.org/2000/svg" 
                            class="h-6 w-6 transition-transform duration-300"
                            :class="activeFaq === index ? 'rotate-180 text-gray-600' : 'text-gray-600 dark:text-gray-200'"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div v-show="activeFaq === index" 
                        class="px-6 pb-4 text-gray-700 dark:text-gray-200 transition-all duration-300">
                        <p>{{ item.answer }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
