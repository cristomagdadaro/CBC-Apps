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
            <h1 class="text-3xl font-bold tracking-tight text-gray-900">Create New Option</h1>
            <p class="mt-2 text-sm text-gray-600">Add a new system option or configuration</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Form -->
    <div class="mx-auto max-w-2xl px-4 py-8 sm:px-6 lg:px-8">
      <form @submit.prevent="submitForm" class="space-y-6 rounded-lg bg-white p-6 shadow">
        <!-- Key -->
        <div>
          <label for="key" class="block text-sm font-medium text-gray-900">
            Key <span class="text-red-500">*</span>
          </label>
          <input
            id="key"
            v-model="form.key"
            type="text"
            placeholder="e.g., app_name (snake_case)"
            class="mt-2 block w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
            @input="form.key = form.key.toLowerCase().replace(/\s+/g, '_')"
          />
          <p v-if="errors.key" class="mt-1 text-sm text-red-600">{{ errors.key[0] }}</p>
          <p class="mt-1 text-xs text-gray-500">Unique identifier in snake_case format</p>
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
            placeholder="e.g., Application Name"
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
            placeholder="What is this option used for?"
            class="mt-2 block w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
          ></textarea>
          <p v-if="errors.description" class="mt-1 text-sm text-red-600">{{ errors.description[0] }}</p>
        </div>

        <!-- Type -->
        <div>
          <label for="type" class="block text-sm font-medium text-gray-900">
            Type <span class="text-red-500">*</span>
          </label>
          <select
            id="type"
            v-model="form.type"
            class="mt-2 block w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
          >
            <option value="">Select a type</option>
            <option value="text">Text</option>
            <option value="number">Number</option>
            <option value="textarea">Textarea</option>
            <option value="boolean">Boolean</option>
            <option value="select">Select (with choices)</option>
            <option value="checkbox">Checkbox</option>
            <option value="json">JSON</option>
          </select>
          <p v-if="errors.type" class="mt-1 text-sm text-red-600">{{ errors.type[0] }}</p>
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
            placeholder="e.g., system, email, inventory"
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
        <SelectOptionsEditor v-if="form.type === 'select'" v-model="form.options" :errors="errors" />

        <!-- JSON Options (for select type metadata) -->
        <div v-if="form.type === 'select'">
          <label for="options-metadata" class="block text-sm font-medium text-gray-900">
            Options Metadata (JSON)
          </label>
          <textarea
            id="options-metadata"
            v-model="form.options"
            rows="5"
            placeholder='[{"value": "option1", "label": "Option 1"}, {"value": "option2", "label": "Option 2"}]'
            class="mt-2 block w-full font-mono text-sm rounded-lg border border-gray-300 px-4 py-2 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
          ></textarea>
          <p v-if="errors.options" class="mt-1 text-sm text-red-600">{{ errors.options[0] }}</p>
          <p class="mt-1 text-xs text-gray-500">JSON array of options for select/dropdown</p>
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
            type="submit"
            :disabled="isSubmitting"
            class="flex-1 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50"
          >
            {{ isSubmitting ? 'Creating...' : 'Create Option' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import axios from 'axios'
import TextInput from './components/ValueInputs/TextInput.vue'
import NumberInput from './components/ValueInputs/NumberInput.vue'
import TextareaInput from './components/ValueInputs/TextareaInput.vue'
import BooleanInput from './components/ValueInputs/BooleanInput.vue'
import JsonInput from './components/ValueInputs/JsonInput.vue'
import SelectOptionsEditor from './components/SelectOptionsEditor.vue'

const isSubmitting = ref(false)
const errors = ref({})

const form = ref({
  key: '',
  label: '',
  description: '',
  type: '',
  group: '',
  value: '',
  options: '',
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

    await axios.post(route('api.options.store'), payload)
    router.push(route('system.options.index'))
  } catch (error) {
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors
    } else {
      console.error('Error creating option:', error)
      alert('Failed to create option')
    }
  } finally {
    isSubmitting.value = false
  }
}
</script>
