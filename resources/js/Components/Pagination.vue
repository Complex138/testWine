<template>
    <div class="flex items-center justify-center">
      <template v-for="(link, i) in links" :key="i">
        <Link
          v-if="link.url"
          :href="link.url"
          class="px-4 py-2 mx-1 rounded"
          :class="{ 'bg-blue-500 text-white': link.active, 'text-gray-700 hover:bg-gray-100': !link.active }"
          v-html="link.label"
        />
        <span
          v-else
          class="px-4 py-2 mx-1 text-gray-400"
          v-html="link.label"
        />
      </template>
    </div>
  </template>
  
  <script setup>
  import { Link } from '@inertiajs/vue3'
  
  defineProps({
    links: Array
  })
  </script>