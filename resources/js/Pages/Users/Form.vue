<template>
    <form @submit.prevent="submit" class="max-w-2xl mx-auto mt-6">
        <div class="space-y-6">
            <!-- Имя -->
            <div>
                <label class="block text-sm font-medium text-gray-700">Имя</label>
                <input type="text" v-model="form.name"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                <div v-if="form.errors.name" class="text-red-500 text-sm mt-1">
                    {{ form.errors.name }}
                </div>
            </div>

            <!-- Телефон -->
            <div>
                <label class="block text-sm font-medium text-gray-700">Телефон</label>
                <input type="text" v-model="form.phone" placeholder="+7XXXXXXXXXX"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                <div v-if="form.errors.phone" class="text-red-500 text-sm mt-1">
                    {{ form.errors.phone }}
                </div>
            </div>

            <!-- Дата рождения -->
            <div>
                <label class="block text-sm font-medium text-gray-700">Дата рождения</label>
                <input type="date" v-model="form.birth_date"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                <div v-if="form.errors.birth_date" class="text-red-500 text-sm mt-1">
                    {{ form.errors.birth_date }}
                </div>
            </div>

            <!-- Адрес -->
            <div>
                <label class="block text-sm font-medium text-gray-700">Адрес</label>
                <input type="text" v-model="form.address"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" />
            </div>

            <!-- Любимое вино -->
            <div>
                <label class="block text-sm font-medium text-gray-700">Любимое вино</label>
                <select v-model="form.favorite_wine_id"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option :value="null">Не выбрано</option>
                    <option v-for="wine in wines" :key="wine.id" :value="wine.id">
                        {{ wine.name }}
                    </option>
                </select>
            </div>

            <!-- Кнопки -->
            <div class="flex justify-end gap-4">
                <Link href="/users" class="bg-gray-200 py-2 px-4 rounded hover:bg-gray-300">
                Отмена
                </Link>
                <div v-if="form.errors.error" class="text-red-500 mb-4">
                    {{ form.errors.error }}
                </div>
                <button type="submit" class="bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600"
                    :disabled="form.processing">
                    {{ form.processing ? 'Сохранение...' : 'Сохранить' }}
                </button>
            </div>
        </div>
    </form>
</template>

<script setup>
import { useForm, Link } from '@inertiajs/vue3'

const props = defineProps({
    user: {
        type: Object,
        default: () => ({
            name: '',
            phone: '',
            birth_date: '',
            address: '',
            favorite_wine_id: null
        })
    },
    wines: {
        type: Array,
        required: true
    }
})


const form = useForm({
  name: props.user.name,
  phone: props.user.phone,
  birth_date: props.user.birth_date ? new Date(props.user.birth_date).toISOString().split('T')[0] : '',
  address: props.user.address,
  favorite_wine_id: props.user.favorite_wine_id
})

async function submit() {
  try {
    if (props.user.id) {
      await form.put(`/users/${props.user.id}`)
    } else {
      await form.post('/users')
    }
    
    if (form.hasErrors) {
      console.error('Ошибки валидации:', form.errors)
    }
  } catch (error) {
    console.error('Ошибка отправки формы:', error)
  }
}
</script>