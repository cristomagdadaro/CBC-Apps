<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm">
      <div class="mx-auto max-w-2xl px-4 py-6 sm:px-6 lg:px-8">
        <div class="flex items-center gap-4">
          <Link href="/apps/system/options" class="text-gray-500 hover:text-gray-700">
            ← Back
          </Link>
          <div>
            <h1 class="text-3xl font-bold tracking-tight text-gray-900">Edit Option</h1>
            <p class="mt-2 text-sm text-gray-600">{{ data?.key }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Form -->
    <div class="mx-auto max-w-2xl px-4 py-8 sm:px-6 lg:px-8">
      <form @submit.prevent="submitForm" class="space-y-6 rounded-lg bg-white p-6 shadow">
        <!-- Key (Read-only) -->
        <div>
          <label for="key" class="block text-sm font-medium text-gray-900">
            Key
          </label>
          <input
            id="key"
            :value="form.key"
            type="text"
            disabled
            class="mt-2 block w-full rounded-lg border border-gray-300 bg-gray-100 px-4 py-2 text-gray-600"
          />
          <p class="mt-1 text-xs text-gray-500">Keys cannot be changed</p>
        </div>

        <!-- Label -->
        <div>
          <label for="label" class="block text-sm font-medium text-gray-900">
            Label <span class="text-red-500">*</span>
          </label>
          <input
            id="label"
            v-model="form.label"
            type="text"
            class="mt-2 block w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
          />
          <p v-if="errors.label" class="mt-1 text-sm text-red-600">{{ errors.label[0] }}</p>
        </div>

        <!-- Description -->
        <div>
          <label for="description" class="block text-sm font-medium text-gray-900">
            Description
          </label>
          <textarea
            id="description"
            v-model="form.description"
            rows="3"
            class="mt-2 block w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
          ></textarea>
          <p v-if="errors.description" class="mt-1 text-sm text-red-600">{{ errors.description[0] }}</p>
        </div>

        <!-- Type (Read-only) -->
        <div>
          <label for="type" class="block text-sm font-medium text-gray-900">
            Type
          </label>
          <input
            id="type"
            :value="form.type"
            type="text"
            disabled
            class="mt-2 block w-full rounded-lg border border-gray-300 bg-gray-100 px-4 py-2 text-gray-600"
          />
          <p class="mt-1 text-xs text-gray-500">Types cannot be changed after creation</p>
        </div>

        <!-- Group -->
        <div>
          <label for="group" class="block text-sm font-medium text-gray-900">
            Group
          </label>
          <input
            id="group"
            v-model="form.group"
            type="text"
            list="group-list"
            class="mt-2 block w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
          />
          <datalist id="group-list">
            <option value="system" />
            <option value="email" />
            <option value="inventory" />
            <option value="forms" />
            <option value="rental" />
            <option value="requests" />
            <option value="locations" />
            <option value="reports" />
          </datalist>
          <p class="mt-1 text-xs text-gray-500">For organizing related options</p>
        </div>

        <!-- Value -->
        <div>
          <label for="value" class="block text-sm font-medium text-gray-900">
            Value <span class="text-red-500">*</span>
          </label>
          <component
            :is="getValueComponent()"
            v-model="form.value"
            :errors="errors"
            :type="form.type"
          />
          <p v-if="errors.value" class="mt-1 text-sm text-red-600">{{ errors.value[0] }}</p>
        </div>

        <!-- Options (for select type) -->
        <div v-if="form.type === 'select'">
          <label for="options-metadata" class="block text-sm font-medium text-gray-900">
            Options Metadata (JSON)
          </label>
          <textarea
            id="options-metadata"
            v-model="form.options"
            rows="5"
            class="mt-2 block w-full font-mono text-sm rounded-lg border border-gray-300 px-4 py-2 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
          ></textarea>
          <p v-if="errors.options" class="mt-1 text-sm text-red-600">{{ errors.options[0] }}</p>
          <p class="mt-1 text-xs text-gray-500">JSON array of options for select/dropdown</p>
        </div>

        <!-- Audit Info -->
        <div v-if="data" class="rounded-lg bg-gray-50 p-4">
          <div class="grid grid-cols-2 gap-4 text-sm">
            <div>
              <p class="font-medium text-gray-900">Created</p>
              <p class="text-gray-600">{{ formatDate(data.created_at) }}</p>
            </div>
            <div>
              <p class="font-medium text-gray-900">Last Updated</p>
              <p class="text-gray-600">{{ formatDate(data.updated_at) }}</p>
            </div>
          </div>
        </div>

        <!-- Form Actions -->
        <div class="flex gap-3 pt-6">
          <Link
            href="/apps/system/options"
            class="flex-1 rounded-lg border border-gray-300 px-4 py-2 text-center text-sm font-medium text-gray-900 hover:bg-gray-50"
          >
            Cancel
          </Link>
          <button
            type="button"
            @click="openDeleteModal"
            class="flex-1 rounded-lg border border-red-300 px-4 py-2 text-sm font-medium text-red-600 hover:bg-red-50"
          >
            Delete
          </button>
          <button
            type="submit"
            :disabled="isSubmitting"
            class="flex-1 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50"
          >
            {{ isSubmitting ? 'Saving...' : 'Save Changes' }}
          </button>
        </div>
      </form>
    </div>

    <!-- Delete Modal -->
    <Teleport to="body">
      <div
        v-if="showDeleteModal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
      >
        <div class="rounded-lg bg-white p-6 shadow-xl max-w-md w-full mx-4">
          <h3 class="text-lg font-semibold text-gray-900">Delete Option</h3>
          <p class="mt-2 text-sm text-gray-600">
            Are you sure you want to delete <strong>{{ form.key }}</strong>? This action cannot be undone.
          </p>
          <div class="mt-6 flex gap-3 justify-end">
            <button
              @click="showDeleteModal = false"
              class="rounded-lg border border-gray-300 px-4 py-2 text-sm hover:bg-gray-50"
            >
              Cancel
            </button>
            <button
              @click="deleteOption()"
              :disabled="isDeleting"
              class="rounded-lg bg-red-600 px-4 py-2 text-sm text-white hover:bg-red-700 disabled:opacity-50"
            >
              {{ isDeleting ? 'Deleting...' : 'Delete' }}
            </button>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import axios from 'axios'
import TextInput from './components/ValueInputs/TextInput.vue'
import NumberInput from './components/ValueInputs/NumberInput.vue'
import TextareaInput from './components/ValueInputs/TextareaInput.vue'
import BooleanInput from './components/ValueInputs/BooleanInput.vue'
import JsonInput from './components/ValueInputs/JsonInput.vue'

const props = defineProps({
  data: {
    type: Object,
    required: true,
  },
})

const isSubmitting = ref(false)
const isDeleting = ref(false)
const showDeleteModal = ref(false)
const errors = ref({})

const form = ref({
  key: props.data.key,
  label: props.data.label,
  description: props.data.description,
  type: props.data.type,
  group: props.data.group,
  value: typeof props.data.value === 'string' ? props.data.value : JSON.stringify(props.data.value),
  options: typeof props.data.options === 'string' ? props.data.options : JSON.stringify(props.data.options || {}),
})

const getValueComponent = () => {
  const components = {
    text: TextInput,
    number: NumberInput,
    textarea: TextareaInput,
    boolean: BooleanInput,
    select: TextInput,
    checkbox: BooleanInput,
    json: JsonInput,
  }
  return components[form.value.type] || TextInput
}

const formatDate = (date) => {
  return new Date(date).toLocaleString()
}

const submitForm = async () => {
  isSubmitting.value = true
  errors.value = {}

  try {
    const payload = { ...form.value }
    
    // Parse JSON fields if needed
    if (form.value.type === 'json' && typeof form.value.value === 'string') {
      try {
        payload.value = JSON.parse(form.value.value)
      } catch (e) {
        payload.value = form.value.value
      }
    }

    if (form.value.options && typeof form.value.options === 'string') {
      try {
        payload.options = JSON.parse(form.value.options)
      } catch (e) {
        payload.options = form.value.options
      }
    }

    await axios.put(route('api.options.update', props.data.id), payload)
    router.push(route('system.options.index'))
  } catch (error) {
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors
    } else {
      console.error('Error updating option:', error)
      alert('Failed to update option')
    }
  } finally {
    isSubmitting.value = false
  }
}

const openDeleteModal = () => {
  showDeleteModal.value = true
}

const deleteOption = async () => {
  isDeleting.value = true
  try {
    await axios.delete(route('api.options.destroy', props.data.id))
    router.push(route('system.options.index'))
  } catch (error) {
    console.error('Error deleting option:', error)
    alert('Failed to delete option')
  } finally {
    isDeleting.value = false
  }
}
</script>
