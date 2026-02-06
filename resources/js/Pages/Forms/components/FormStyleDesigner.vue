<script>
import TextInput from "@/Components/TextInput.vue";
import { FORM_STYLE_FIELDS, mergeFormStyleTokens } from "@/Modules/shared/formStyleTokens";

export default {
    name: "FormStyleDesigner",
    components: { TextInput },
    props: {
        modelValue: {
            type: Object,
            default: () => ({}),
        },
        error: {
            type: String,
            default: null,
        },
    },
    data() {
        return {
            fields: FORM_STYLE_FIELDS,
            localTokens: mergeFormStyleTokens(this.modelValue),
            syncingFromParent: false,
        };
    },
    watch: {
        modelValue: {
            handler(value) {
                this.syncingFromParent = true;
                this.localTokens = mergeFormStyleTokens(value);
                this.$nextTick(() => {
                    this.syncingFromParent = false;
                });
            },
            deep: true,
        },
        localTokens: {
            handler(value) {
                if (this.syncingFromParent) {
                    return;
                }
                this.$emit('update:modelValue', value);
            },
            deep: true,
        },
    },
    methods: {
        isTextColorOrShadowField(key) {
            return key.includes('-text-color') || key === 'form-text-shadow';
        },
        handleModeChange(key, rawValue) {
            const mode = rawValue === '' ? null : rawValue;
            const current = this.localTokens[key] || { mode: null, value: null };
            let value = current.value;

            if (!mode) {
                value = null;
            } else if (mode === 'color' && !value) {
                value = '#1f2937';
            } else if (mode === 'image' && !value) {
                value = '';
            }

            this.updateToken(key, { mode, value });
        },
        updateToken(key, patch) {
            this.localTokens = {
                ...this.localTokens,
                [key]: {
                    ...this.localTokens[key],
                    ...patch,
                },
            };
        },
        clearToken(key) {
            if (this.isTextColorOrShadowField(key)) {
                this.updateToken(key, { value: null });
            } else {
                this.updateToken(key, { mode: null, value: null });
            }
        },
    },
};
</script>

<template>
    <div class="flex flex-col gap-2">
        <div class="text-sm">
            <p class="font-semibold text-gray-700">Form Theme</p>
            <p class="text-xs text-gray-500">Pick a solid color or supply a public image URL for each highlighted block.</p>
        </div>
        <div class="flex flex-col gap-2">
            <div
                v-for="field in fields"
                :key="field.key"
                class="border rounded-xl p-3 bg-white shadow-sm flex flex-col gap-2"
            >
                <div class="flex flex-col gap-1 md:flex-row md:items-center md:justify-between">
                    <div>
                        <p class="text-sm font-semibold text-gray-800 leading-tight">{{ field.label }}</p>
                        <p class="text-xs text-gray-500">{{ field.description }}</p>
                    </div>
                    <select
                        v-if="!isTextColorOrShadowField(field.key)"
                        class="border rounded-md text-sm px-2 py-1 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-AB/40"
                        :value="localTokens[field.key]?.mode ?? ''"
                        @change="handleModeChange(field.key, $event.target.value)"
                    >
                        <option value="">Default Theme</option>
                        <option value="color">Solid Color</option>
                        <option value="image">Background Image</option>
                    </select>
                </div>

                <!-- Background color/image fields -->
                <template v-if="!isTextColorOrShadowField(field.key)">
                    <div v-if="localTokens[field.key]?.mode === 'color'" class="flex flex-col gap-3 md:flex-row md:items-center">
                        <input
                            type="color"
                            class="h-10 w-14 rounded border border-gray-200 cursor-pointer"
                            :value="localTokens[field.key]?.value || '#1f2937'"
                            @input="updateToken(field.key, { value: $event.target.value })"
                        >
                        <TextInput
                            v-model="localTokens[field.key].value"
                            placeholder="#1F2937"
                            classes="text-sm"
                        />
                    </div>

                    <div v-else-if="localTokens[field.key]?.mode === 'image'" class="flex flex-col gap-1">
                        <TextInput
                            v-model="localTokens[field.key].value"
                            placeholder="https://cdn.example.com/background.jpg"
                            classes="text-sm"
                        />
                        <p class="text-[0.65rem] text-gray-500">Use a public URL or relative storage path to an uploaded asset.</p>
                    </div>
                </template>

                <!-- Text color and shadow fields -->
                <template v-else>
                    <div class="flex flex-col gap-3">
                        <div v-if="field.key.includes('-text-color')" class="flex items-center gap-3">
                            <div class="flex items-center gap-2">
                                <input
                                    type="color"
                                    class="h-10 w-14 rounded border border-gray-200 cursor-pointer"
                                    :value="localTokens[field.key]?.value || '#111827'"
                                    @input="updateToken(field.key, { value: $event.target.value })"
                                >
                            </div>
                            <TextInput
                                v-model="localTokens[field.key].value"
                                placeholder="#111827"
                                classes="text-sm"
                            />
                        </div>

                        <div v-else class="flex flex-col gap-1">
                            <TextInput
                                v-model="localTokens[field.key].value"
                                placeholder="0 1px 2px rgba(0, 0, 0, 0.35)"
                                classes="text-sm"
                            />
                            <p class="text-[0.65rem] text-gray-500">Optional CSS text-shadow value (e.g., 0 1px 2px rgba(0,0,0,0.5))</p>
                        </div>
                    </div>
                </template>

                <div class="flex justify-end">
                    <button
                        type="button"
                        class="text-xs text-red-600 hover:text-red-700"
                        @click="clearToken(field.key)"
                    >
                        Reset
                    </button>
                </div>
            </div>
        </div>
        <p v-if="error" class="text-xs text-red-600">{{ error }}</p>
    </div>
</template>
