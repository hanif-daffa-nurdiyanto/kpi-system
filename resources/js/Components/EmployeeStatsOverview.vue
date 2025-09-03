<template>
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
      <!-- OB Calls Card -->
      <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <h3 class="text-lg font-medium text-gray-900">OB Calls</h3>
            </div>
          </div>
          <div class="mt-2">
            <div class="flex items-baseline justify-between">
              <p class="text-2xl font-semibold text-gray-900">
                {{ cards[0].value }} <span class="text-sm font-medium text-gray-500">/ {{ cards[0].target }}</span>
              </p>
              <p :class="getStatusColor(cards[0].progress)">
                {{ cards[0].status }}
              </p>
            </div>
            <div class="mt-3">
              <div class="relative h-2 bg-gray-200 rounded-full overflow-hidden">
                <div class="absolute h-full bg-blue-600 rounded-full" :style="{ width: cards[0].progress + '%' }"></div>
              </div>
              <p class="mt-1 text-sm text-gray-600">{{ cards[0].progress }}%</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Talk Time Card -->
      <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <h3 class="text-lg font-medium text-gray-900">Talk Time</h3>
            </div>
          </div>
          <div class="mt-2">
            <div class="flex items-baseline justify-between">
              <p class="text-2xl font-semibold text-gray-900">
                {{ cards[1].value }} <span class="text-sm font-medium text-gray-500">/ {{ cards[1].target }} {{ cards[1].unit }}</span>
              </p>
              <p :class="getStatusColor(cards[1].progress)">
                {{ cards[1].status }}
              </p>
            </div>
            <div class="mt-3">
              <div class="relative h-2 bg-gray-200 rounded-full overflow-hidden">
                <div class="absolute h-full bg-blue-600 rounded-full" :style="{ width: cards[1].progress + '%' }"></div>
              </div>
              <p class="mt-1 text-sm text-gray-600">{{ cards[1].progress }}%</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Auto Quotes Card -->
      <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <h3 class="text-lg font-medium text-gray-900">Auto Quotes</h3>
            </div>
          </div>
          <div class="mt-2">
            <div class="flex items-baseline justify-between">
              <p class="text-2xl font-semibold text-gray-900">
                {{ cards[2].value }} <span class="text-sm font-medium text-gray-500">/ {{ cards[2].target }}</span>
              </p>
              <p class="text-green-600">
                {{ cards[2].status }}
              </p>
            </div>
            <div class="mt-3">
              <div class="relative h-2 bg-gray-200 rounded-full overflow-hidden">
                <div class="absolute h-full bg-green-600 rounded-full" :style="{ width: cards[2].progress + '%' }"></div>
              </div>
              <p class="mt-1 text-sm text-gray-600">{{ cards[2].progress }}%</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Email Response Card -->
      <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <h3 class="text-lg font-medium text-gray-900">Email Response</h3>
            </div>
          </div>
          <div class="mt-2">
            <div class="flex items-baseline justify-between">
              <p class="text-2xl font-semibold text-gray-900">
                {{ cards[3].value }} <span class="text-sm font-medium text-gray-500">/ {{ cards[3].target }}</span>
              </p>
              <p :class="getStatusColor(cards[3].progress)">
                {{ cards[3].status }}
              </p>
            </div>
            <div class="mt-3">
              <div class="relative h-2 bg-gray-200 rounded-full overflow-hidden">
                <div class="absolute h-full bg-blue-600 rounded-full" :style="{ width: cards[3].progress + '%' }"></div>
              </div>
              <p class="mt-1 text-sm text-gray-600">{{ cards[3].progress }}%</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </template>

  <script>
  export default {
    props: {
      cards: {
        type: Array,
        required: true
      }
    },
    methods: {
      getStatusColor(progress) {
        if (progress >= 100) return 'text-green-600';
        if (progress >= 80) return 'text-yellow-600';
        return 'text-red-600';
      }
    }
  }
  </script>