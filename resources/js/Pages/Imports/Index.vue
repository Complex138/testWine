<template>
  <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="px-4 sm:px-0">
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-semibold">История импортов</h1>

        <!-- Форма загрузки файла -->
        <form @submit.prevent="submitFile" class="flex items-center gap-4">
          <input type="file" ref="fileInput" class="hidden" accept=".csv" @change="handleFileChange" />
          <button type="button" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600"
            @click="$refs.fileInput.click()">
            Загрузить CSV файл
          </button>
          <Link href="/users" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
          Назад
          </Link>
        </form>
      </div>

      <!-- Таблица импортов -->
      <div class="bg-white shadow-sm rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Файл
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Статус
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Обработано
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Черновики
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Дата
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Действия
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="item in imports.data" :key="item.id">
              <td class="px-6 py-4 whitespace-nowrap">{{ item.filename }}</td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="{
                  'px-2 py-1 text-xs rounded': true,
                  'bg-yellow-100 text-yellow-800': item.status === 'pending',
                  'bg-blue-100 text-blue-800': item.status === 'processing',
                  'bg-green-100 text-green-800': item.status === 'completed',
                  'bg-red-100 text-red-800': item.status === 'failed'
                }">
                  {{ getStatusText(item.status) }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                {{ item.processed_rows }} / {{ item.total_rows }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                {{ item.draft_rows }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                {{ formatDate(item.created_at) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <Link :href="`/imports/${item.id}`" class="text-blue-600 hover:text-blue-900">
                Подробнее
                </Link>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Пагинация -->
      <Pagination :links="imports.links" class="mt-6" />
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import Pagination from '@/Components/Pagination.vue'

const props = defineProps({
  imports: Object
})

const fileInput = ref(null)
const uploading = ref(false)
const refreshInterval = ref(null)

function startPolling() {
  refreshInterval.value = setInterval(() => {
    router.reload({ preserveScroll: true, preserveState: true })
  }, 3000)
}

function stopPolling() {
  clearInterval(refreshInterval.value)
}

function checkForActiveImports() {
  const hasProcessing = props.imports.data.some(imp => imp.status === 'processing' || imp.status === 'pending')
  if (hasProcessing) {
    startPolling()
  } else {
    stopPolling()
  }
}

function submitFile() {
  if (!fileInput.value?.files[0]) return

  const form = new FormData()
  form.append('file', fileInput.value.files[0])

  uploading.value = true

  router.post('/users/import-csv', form, {
    onSuccess: () => {
      startPolling()
    },
    onFinish: () => {
      uploading.value = false
      fileInput.value.value = ''
    }
  })
}

function handleFileChange(e) {
  if (e.target.files.length > 0) {
    submitFile()
  }
}

function getStatusText(status) {
  const statuses = {
    pending: 'Ожидает',
    processing: 'Обработка',
    completed: 'Завершено',
    failed: 'Ошибка'
  }
  return statuses[status] || status
}

function formatDate(date) {
  return new Date(date).toLocaleString('ru-RU')
}

onMounted(() => {
  checkForActiveImports()
})

onBeforeUnmount(() => {
  stopPolling()
})
</script>