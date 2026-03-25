<script>
export default {
    name: 'KeyValueEditor',
    props: {
        modelValue: {
            type: Array,
            default: () => [],
        },
        title: {
            type: String,
            default: 'Parameters',
        },
    },
    emits: ['update:modelValue'],
    methods: {
        rows() {
            return Array.isArray(this.modelValue) ? this.modelValue : []
        },
        addRow(prefill = {}) {
            this.$emit('update:modelValue', [
                ...this.rows(),
                {
                    key: prefill.key || '',
                    value: prefill.value || '',
                },
            ])
        },
        updateRow(index, field, value) {
            const rows = this.rows().map((row) => ({
                key: row?.key || '',
                value: row?.value || '',
            }))

            rows[index] = {
                ...rows[index],
                [field]: value,
            }

            this.$emit('update:modelValue', rows)
        },
        removeRow(index) {
            this.$emit(
                'update:modelValue',
                this.rows().filter((_, currentIndex) => currentIndex !== index),
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
                <p class="text-xs text-gray-500">Build a flexible parameter set for each monitoring stage.</p>
            </div>
            <button type="button" class="rounded-lg border px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-50" @click="addRow()">
                Add
            </button>
        </div>

        <div v-if="!rows().length" class="rounded-lg border border-dashed border-gray-300 px-4 py-4 text-sm text-gray-500">
            Add one or more parameter rows for this record.
        </div>

        <div v-for="(row, index) in rows()" :key="`${title}-${index}`" class="grid gap-3 rounded-xl border border-gray-200 bg-gray-50 p-4 md:grid-cols-[minmax(0,0.8fr)_minmax(0,1.2fr)_auto]">
            <input
                :value="row.key"
                class="rounded-lg border-gray-300"
                placeholder="Parameter"
                @input="updateRow(index, 'key', $event.target.value)"
            />
            <input
                :value="row.value"
                class="rounded-lg border-gray-300"
                placeholder="Value"
                @input="updateRow(index, 'value', $event.target.value)"
            />
            <button type="button" class="rounded-lg border border-red-200 px-3 py-2 text-sm text-red-700 hover:bg-red-50" @click="removeRow(index)">
                Remove
            </button>
        </div>
    </div>
</template>
