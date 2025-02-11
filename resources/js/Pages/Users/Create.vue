<template>
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
      <div class="px-4 sm:px-0">
        <h1 class="text-2xl font-semibold mb-6">Создать пользователя</h1>
        <UserForm :wines="wines" />
      </div>
    </div>
  </template>
  
  <script setup>
  import UserForm from './Form.vue'
  
  defineProps({
    wines: Array
  })
  </script>