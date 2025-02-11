<template>
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="px-4 sm:px-0">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-semibold">
                    Результаты импорта: {{ importData.filename }}
                </h1>
                <Link href="/imports" class="text-blue-600 hover:text-blue-900">
                ← Назад к списку
                </Link>
            </div>

            <!-- Общая статистика -->
            <div class="grid grid-cols-4 gap-4 mb-6">
                <div class="bg-white p-4 rounded-lg shadow">
                    <div class="text-sm text-gray-500">Статус</div>
                    <div class="text-lg font-semibold">{{ getStatusText(importData.status) }}</div>
                </div>
                <div class="bg-white p-4 rounded-lg shadow">
                    <div class="text-sm text-gray-500">Всего строк</div>
                    <div class="text-lg font-semibold">{{ importData.total_rows }}</div>
                </div>
                <div class="bg-white p-4 rounded-lg shadow">
                    <div class="text-sm text-gray-500">Обработано</div>
                    <div class="text-lg font-semibold">{{ importData.processed_rows }}</div>
                </div>
                <div class="bg-white p-4 rounded-lg shadow">
                    <div class="text-sm text-gray-500">Черновики</div>
                    <div class="text-lg font-semibold">{{ importData.draft_rows }}</div>
                </div>
            </div>

            <!-- Ошибки -->
            <div v-if="importData.errors?.length" class="mb-6">
                <h2 class="text-lg font-semibold mb-3">Ошибки</h2>
                <div class="bg-red-50 p-4 rounded-lg">
                    <ul class="list-disc list-inside space-y-1">
                        <li v-for="(error, index) in importData.errors" :key="index" class="text-red-700">
                            {{ error }}
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Предупреждения -->
            <div v-if="importData.warnings?.length" class="mb-6">
                <h2 class="text-lg font-semibold mb-3">Предупреждения</h2>
                <div class="bg-yellow-50 p-4 rounded-lg">
                    <ul class="list-disc list-inside space-y-1">
                        <li v-for="(warning, index) in importData.warnings" :key="index" class="text-yellow-700">
                            {{ warning }}
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Черновики -->
            <div v-if="importData.drafts?.length" class="mb-6">
                <h2 class="text-lg font-semibold mb-3">Черновики</h2>
                <div class="bg-gray-50 p-4 rounded-lg">
                    <ul class="list-disc list-inside space-y-1">
                        <li v-for="(draft, index) in importData.drafts" :key="index" class="text-gray-700">
                            {{ draft.name || 'Без имени' }} (Дата рождения: {{ draft.date || 'нет' }})
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Дубликаты -->
            <div v-if="importData.duplicates?.length" class="mb-6">
                <h2 class="text-lg font-semibold mb-3">Дубликаты</h2>
                <div class="bg-blue-50 p-4 rounded-lg">
                    <ul class="list-disc list-inside space-y-1">
                        <li v-for="(duplicate, index) in importData.duplicates" :key="index" class="text-blue-700">
                            {{ duplicate }}
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'

defineProps({
    importData: Object
})

function getStatusText(status) {
    const statuses = {
        pending: 'Ожидает',
        processing: 'Обработка',
        completed: 'Завершено',
        failed: 'Ошибка'
    }
    return statuses[status] || status
}
</script>