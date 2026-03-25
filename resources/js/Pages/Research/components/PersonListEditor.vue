<script>
export default {
    name: 'PersonListEditor',
    props: {
        modelValue: {
            type: Array,
            default: () => [],
        },
        title: {
            type: String,
            default: 'People',
        },
        emptyLabel: {
            type: String,
            default: 'No people added yet.',
        },
    },
    emits: ['update:modelValue'],
    data() {
        return {
            touched: {},
        }
    },
    methods: {
        normalizedRows() {
            return Array.isArray(this.modelValue) ? this.modelValue : []
        },
        updateRow(index, field, value) {
            const rows = this.normalizedRows().map((row) => ({
                name: row?.name || '',
                position: row?.position || '',
            }))

            rows[index] = {
                ...rows[index],
                [field]: value,
            }

            this.$emit('update:modelValue', rows)
        },
        markTouched(index, field) {
            this.touched[`${index}-${field}`] = true
        },
        rowFieldError(index, field) {
            const row = this.normalizedRows()[index] || {}
            const name = String(row?.name || '').trim()
            const position = String(row?.position || '').trim()

            if (!this.touched[`${index}-${field}`]) {
                return ''
            }

            if (field === 'name' && position && !name) {
                return 'Name is required when position is provided.'
            }

            if (field === 'position' && name && !position) {
                return 'Position is required when name is provided.'
            }

            return ''
        },
        addRow() {
            this.$emit('update:modelValue', [
                ...this.normalizedRows(),
                { name: '', position: '' },
            ])
        },
        removeRow(index) {
            this.$emit(
                'update:modelValue',
                this.normalizedRows().filter((_, currentIndex) => currentIndex !== index),
            )
        },
    },
}
</script>

<template>
    <div class="space-y-3">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-semibold text-gray-900">{{ title }}</p>
                <p class="text-xs text-gray-500">Name and position pairs can be updated anytime.</p>
            </div>
            <button type="button" class="rounded-lg border px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-50" @click="addRow">
                Add
            </button>
        </div>

        <div v-if="!normalizedRows().length" class="rounded-lg border border-dashed border-gray-300 px-4 py-4 text-sm text-gray-500">
            {{ emptyLabel }}
        </div>

        <div v-for="(person, index) in normalizedRows()" :key="`${title}-${index}`" class="grid gap-3 rounded-xl border border-gray-200 bg-gray-50 p-4 md:grid-cols-[1fr_1fr_auto]">
            <div>
                <input
                    :value="person.name"
                    :aria-label="`${title} person name ${index + 1}`"
                    class="w-full rounded-lg border-gray-300"
                    placeholder="Name"
                    @input="updateRow(index, 'name', $event.target.value)"
                    @blur="markTouched(index, 'name')"
                />
                <p v-if="rowFieldError(index, 'name')" class="mt-1 text-xs text-red-600">{{ rowFieldError(index, 'name') }}</p>
            </div>
            <div>
                <input
                    :value="person.position"
                    :aria-label="`${title} person position ${index + 1}`"
                    class="w-full rounded-lg border-gray-300"
                    placeholder="Position"
                    @input="updateRow(index, 'position', $event.target.value)"
                    @blur="markTouched(index, 'position')"
                />
                <p v-if="rowFieldError(index, 'position')" class="mt-1 text-xs text-red-600">{{ rowFieldError(index, 'position') }}</p>
            </div>
            <button type="button" class="rounded-lg border border-red-200 px-3 py-2 text-sm text-red-700 hover:bg-red-50" @click="removeRow(index)">
                Remove
            </button>
        </div>
    </div>
</template>
