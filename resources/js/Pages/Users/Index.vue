<template>
  <div class="mx-auto py-6 sm:px-6 lg:px-8">
    <div class="px-4 py-6 sm:px-0">
      <!-- Заголовок и кнопка добавления -->
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-semibold">Пользователи</h1>
        <div class="flex gap-2">
          <Link href="/users/create" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
          Добавить пользователя
          </Link>
          <Link href="/imports" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
          Импорт CSV
          </Link>
          <!-- Здесь позже добавим кнопку для импорта CSV -->
        </div>
      </div>

      <!-- Таблица пользователей -->
      <div class="bg-white shadow-sm rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Имя
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Телефон
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Дата рождения
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Адрес
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Любимое вино
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Действия
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="user in users.data" :key="user.id" :class="{ 'text-gray-500 bg-gray-200': user.is_draft }">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex flex-col relative">
                  <div v-if="user.is_draft" class="text-xs text-red-400 border border-red-400 rounded px-1 mb-1 w-fit absolute top-1 right-0">
                    черновик
                  </div>
                  {{ user.name || "—" }}
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">{{ user.phone }}</td>
              <td class="px-6 py-4 whitespace-nowrap">{{ user.birth_date == '1900-01-01T00:00:00.000000Z' ? "—" :
                formatDate(user.birth_date) }}</td>
              <td class="px-6 py-4 whitespace-nowrap">{{ user.address || '—' }}</td>
              <td class="px-6 py-4 whitespace-nowrap">{{ user.favorite_wine?.name || '—' }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm">
                <div class="flex gap-2">
                  <Link :href="`/users/${user.id}/edit`" class="text-blue-600 hover:text-blue-900">Редактировать</Link>
                  <button @click="deleteUser(user)" class="text-red-600 hover:text-red-900">Удалить</button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Пагинация -->
      <Pagination :links="users.links" class="mt-6" />
    </div>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import { router } from '@inertiajs/vue3'
import Pagination from '@/Components/Pagination.vue'

defineProps({
  users: Object
})

function formatDate(date) {
  return new Date(date).toLocaleDateString('ru-RU')
}

function deleteUser(user) {
  if (confirm('Вы уверены, что хотите удалить этого пользователя?')) {
    router.delete(`/users/${user.id}`)
  }
}
</script>