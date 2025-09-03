<script setup>
import { ref } from 'vue'
import { useVisibilityAnimation } from '../../../Composables/useVisibilityAnimation'

const stats = ref([
  { value: '87%', label: 'Performance Improvement', icon: '📈' },
  { value: '3.5x', label: 'ROI on KPI Tracking', icon: '💰' },
  { value: '42%', label: 'Time Saved on Reporting', icon: '⏱️' },
  { value: '94%', label: 'User Satisfaction', icon: '😊' },
])

const { visibleSections, setupIntersectionObserver } = useVisibilityAnimation()
setupIntersectionObserver('stats')
</script>

<template>
  <section
    id="stats"
    class="py-20 bg-gray-200 dark:bg-transparent transition-colors duration-500"
  >
    <div class="container px-4 mx-auto sm:px-6 lg:px-8">
      <!-- Heading -->
      <div
        class="text-center max-w-2xl mx-auto mb-12 transition-all duration-700"
        :class="visibleSections.has('stats') ? 'translate-y-0 opacity-100' : 'translate-y-10 opacity-0'"
      >
        <h2 class="text-3xl sm:text-4xl font-bold text-violet-500">
          Impact in Numbers
        </h2>
        <p class="mt-4 text-lg text-gray-600 dark:text-gray-300">
          Clear metrics to showcase organizational growth and efficiency.
        </p>
      </div>

      <!-- Stats Grid -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
        <div
          v-for="(stat, index) in stats"
          :key="index"
          class="relative p-6 rounded-2xl backdrop-blur-md shadow-xl text-center border bg-white dark:bg-white/10 border-violet-400/30 hover:shadow-violet-500/20 hover:scale-105 transform transition-all duration-500"
          :class="[visibleSections.has('stats') ? 'translate-y-0 opacity-100' : 'translate-y-10 opacity-0',
                   `delay-${Math.min(index * 150, 500)}`]"
        >
          <!-- Icon -->
          <div
            class="w-16 h-16 mx-auto flex items-center justify-center text-3xl rounded-full bg-gradient-to-r from-violet-500 to-fuchsia-600 text-white shadow-lg shadow-violet-500/40 mb-4"
          >
            {{ stat.icon }}
          </div>

          <!-- Value -->
          <div class="text-4xl font-extrabold text-gray-600 dark:text-white">{{ stat.value }}</div>

          <!-- Label -->
          <div class="mt-2 text-gray-600 dark:text-gray-400 text-sm">{{ stat.label }}</div>
        </div>
      </div>
    </div>
  </section>
</template>
