<script setup>
import { ref } from 'vue'

const props = defineProps({
  feature: {
    type: Object,
    required: true
  }
})

// Track which usage index is open
const openIndexes = ref([])

const toggle = (index) => {
  if (openIndexes.value.includes(index)) {
    openIndexes.value = openIndexes.value.filter(i => i !== index)
  } else {
    openIndexes.value.push(index)
  }
}

const isOpen = (index) => openIndexes.value.includes(index)
</script>

<template>
  <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden">
    <!-- Feature header -->
    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
      <div class="flex items-center">
        <div class="w-12 h-12 rounded-full flex items-center justify-center text-white mr-4"
          :style="{ backgroundColor: feature.color }">
          <span class="text-white text-xl font-bold">{{ feature.number }}</span>
        </div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ feature.title }}</h1>
      </div>
    </div>

    <!-- Feature content -->
    <div class="p-6">
      <!-- Feature image -->
      <div class="mb-8 rounded-lg overflow-hidden">
        <img :src="`/storage/${feature.image}`" alt="Feature image"
          class="w-full max-w-3xl h-auto mx-auto object-contain" style="aspect-ratio: 16/9;">
      </div>

      <!-- Feature description -->
      <div class="prose prose-blue max-w-none dark:prose-dark">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Overview</h2>
        <p class="text-gray-700 dark:text-gray-300">{{ feature.longDescription }}</p>

        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mt-8">Usage</h2>
        <ul class="mt-4 space-y-4">
          <li v-for="(usage, index) in feature.usages" :key="index" class="flex flex-col">
            <button
              @click="toggle(index)"
              class="flex items-center text-left text-gray-700 dark:text-gray-300 font-medium focus:outline-none"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-500 mr-2 mt-0.5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
              </svg>
              {{ usage.title }}
              <svg class="w-4 h-4 ml-2 transition-transform" :class="{ 'rotate-180': isOpen(index) }" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
              </svg>
            </button>

            <transition name="fade">
              <p v-if="isOpen(index)" class="mt-2 ml-7 text-sm text-gray-600 dark:text-gray-400">
                {{ usage.description }}
              </p>
            </transition>
          </li>
        </ul>
      </div>
    </div>
  </div>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: all 0.1s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
  max-height: 0;
}
</style>
