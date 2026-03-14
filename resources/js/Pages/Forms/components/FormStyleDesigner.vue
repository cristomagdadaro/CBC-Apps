<script>
import { FORM_STYLE_FIELDS, mergeFormStyleTokens } from "@/Modules/shared/formStyleTokens";

export default {
    name: "FormStyleDesigner",
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
            expandedFields: {},
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
                if (this.syncingFromParent) return;
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
            if (
                (key.includes('-text-color') || this.fields.find(f => f.key === key)) &&
                patch.hasOwnProperty('value') &&
                typeof patch.value === 'string' &&
                patch.value.startsWith('#')
            ) {
                const hex = patch.value.trim();
                if (!/^#[0-9a-fA-F]{6}$/.test(hex)) return;
            }
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
        toggleExpand(key) {
            this.expandedFields[key] = !this.expandedFields[key];
        },
        isActive(key) {
            const token = this.localTokens[key];
            return token?.mode || token?.value;
        },
    },
};
</script>

<template>
    <div class="w-full max-w-3xl mx-auto space-y-4">
        <!-- Header -->
        <div v-if="error" class="flex items-center gap-1.5 text-sm text-red-600 bg-red-50 px-3 py-1.5 rounded-lg">
            <LuAlertCircle class="w-4 h-4" />
            {{ error }}
        </div>

        <!-- Style Fields -->
        <div class="space-y-3">
            <div
                v-for="field in fields"
                :key="field.key"
                class="group bg-white border border-gray-200 rounded-xl overflow-hidden transition-all duration-200 hover:shadow-md"
                :class="[
                    isActive(field.key) ? 'ring-1 ring-gray-300' : '',
                    expandedFields[field.key] ? 'shadow-md' : ''
                ]"
            >
                <!-- Field Header -->
                <button
                    type="button"
                    @click="toggleExpand(field.key)"
                    class="w-full flex items-center justify-between p-4 hover:bg-gray-50/50 transition-colors"
                >
                    <div class="flex items-center gap-3">
                        <!-- Status Indicator -->
                        <div 
                            class="w-2 h-2 rounded-full transition-colors duration-200"
                            :class="isActive(field.key) ? 'bg-green-500' : 'bg-gray-300'"
                        />
                        
                        <div class="text-left">
                            <p class="font-medium text-gray-800 text-sm">{{ field.label }}</p>
                            <p class="text-xs text-gray-500 mt-0.5">{{ field.description }}</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <!-- Mode Badge -->
                        <span 
                            v-if="localTokens[field.key]?.mode"
                            class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium capitalize"
                            :class="localTokens[field.key]?.mode === 'color' ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700'"
                        >
                            {{ localTokens[field.key]?.mode }}
                        </span>
                        
                        <!-- Expand Icon -->
                        <LuChevronDown 
                            class="w-4 h-4 text-gray-400 transition-transform duration-200"
                            :class="expandedFields[field.key] ? 'rotate-180' : ''"
                        />
                    </div>
                </button>

                <!-- Field Content -->
                <Transition
                    enter-active-class="transition-all duration-200 ease-out"
                    enter-from-class="opacity-0 max-h-0"
                    enter-to-class="opacity-100 max-h-96"
                    leave-active-class="transition-all duration-200 ease-in"
                    leave-from-class="opacity-100 max-h-96"
                    leave-to-class="opacity-0 max-h-0"
                >
                    <div v-show="expandedFields[field.key]" class="border-t border-gray-100">
                        <div class="p-4 space-y-4">
                            <!-- Mode Selector (for background fields) -->
                            <div v-if="!isTextColorOrShadowField(field.key)" class="space-y-2">
                                <label class="text-xs font-medium text-gray-600 uppercase tracking-wide">Style Type</label>
                                <div class="grid grid-cols-3 gap-2">
                                    <button
                                        type="button"
                                        @click="handleModeChange(field.key, '')"
                                        class="flex flex-col items-center gap-2 p-3 rounded-lg border-2 transition-all duration-200"
                                        :class="!localTokens[field.key]?.mode ? 'border-gray-800 bg-gray-50' : 'border-gray-200 hover:border-gray-300'"
                                    >
                                        <LuCircle class="w-5 h-5 text-gray-400" />
                                        <span class="text-xs font-medium text-gray-700">Default</span>
                                    </button>
                                    
                                    <button
                                        type="button"
                                        @click="handleModeChange(field.key, 'color')"
                                        class="flex flex-col items-center gap-2 p-3 rounded-lg border-2 transition-all duration-200"
                                        :class="localTokens[field.key]?.mode === 'color' ? 'border-gray-800 bg-gray-50' : 'border-gray-200 hover:border-gray-300'"
                                    >
                                        <div 
                                            class="w-5 h-5 rounded-full border border-gray-200"
                                            :style="{ background: localTokens[field.key]?.value || '#1f2937' }"
                                        />
                                        <span class="text-xs font-medium text-gray-700">Color</span>
                                    </button>
                                    
                                    <button
                                        type="button"
                                        @click="handleModeChange(field.key, 'image')"
                                        class="flex flex-col items-center gap-2 p-3 rounded-lg border-2 transition-all duration-200"
                                        :class="localTokens[field.key]?.mode === 'image' ? 'border-gray-800 bg-gray-50' : 'border-gray-200 hover:border-gray-300'"
                                    >
                                        <LuImage class="w-5 h-5 text-gray-400" />
                                        <span class="text-xs font-medium text-gray-700">Image</span>
                                    </button>
                                </div>
                            </div>

                            <!-- Color Input -->
                            <div v-if="localTokens[field.key]?.mode === 'color' || isTextColorOrShadowField(field.key)" class="space-y-3">
                                <label class="text-xs font-medium text-gray-600 uppercase tracking-wide">
                                    {{ isTextColorOrShadowField(field.key) ? 'Color Value' : 'Background Color' }}
                                </label>
                                
                                <div class="flex items-center gap-3">
                                    <div class="relative">
                                        <input
                                            type="color"
                                            class="w-12 h-12 rounded-lg cursor-pointer border border-gray-200 p-1 bg-white"
                                            :value="localTokens[field.key]?.value || (isTextColorOrShadowField(field.key) ? '#111827' : '#1f2937')"
                                            @input="updateToken(field.key, { value: $event.target.value })"
                                        >
                                    </div>
                                    
                                    <div class="flex-1">
                                        <div class="relative">
                                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 font-mono text-sm">#</span>
                                            <input
                                                type="text"
                                                :value="(localTokens[field.key]?.value || '').replace('#', '')"
                                                @input="updateToken(field.key, { value: '#' + $event.target.value })"
                                                class="w-full pl-7 pr-3 py-2.5 text-sm font-mono border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-300 focus:border-transparent"
                                                placeholder="1F2937"
                                                maxlength="6"
                                            >
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Image URL Input -->
                            <div v-if="localTokens[field.key]?.mode === 'image'" class="space-y-2">
                                <label class="text-xs font-medium text-gray-600 uppercase tracking-wide">Image URL</label>
                                <div class="relative">
                                    <LuLink class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                                    <input
                                        v-model="localTokens[field.key].value"
                                        type="url"
                                        placeholder="https://cdn.example.com/background.jpg"
                                        class="w-full pl-10 pr-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-300 focus:border-transparent"
                                    >
                                </div>
                                <p class="text-xs text-gray-500 flex items-center gap-1">
                                    <LuInfo class="w-3 h-3" />
                                    Use a public URL or relative storage path
                                </p>
                            </div>

                            <!-- Text Shadow Input -->
                            <div v-if="field.key === 'form-text-shadow'" class="space-y-2">
                                <label class="text-xs font-medium text-gray-600 uppercase tracking-wide">Shadow Value</label>
                                <input
                                    v-model="localTokens[field.key].value"
                                    type="text"
                                    placeholder="0 1px 2px rgba(0, 0, 0, 0.35)"
                                    class="w-full px-3 py-2.5 text-sm font-mono border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-300 focus:border-transparent"
                                >
                                <p class="text-xs text-gray-500">CSS text-shadow format</p>
                            </div>

                            <!-- Preview & Actions -->
                            <div class="flex items-center justify-between pt-3 border-t border-gray-100">
                                <!-- Live Preview -->
                                <div class="flex items-center gap-3">
                                    <span class="text-xs text-gray-500">Preview:</span>
                                    <div 
                                        class="w-16 h-10 rounded-lg border border-gray-200 shadow-sm flex items-center justify-center text-xs font-medium"
                                        :style="{
                                            backgroundColor: localTokens[field.key]?.mode === 'color' ? localTokens[field.key]?.value : undefined,
                                            backgroundImage: localTokens[field.key]?.mode === 'image' ? `url(${localTokens[field.key]?.value})` : undefined,
                                            backgroundSize: 'cover',
                                            backgroundPosition: 'center',
                                            color: isTextColorOrShadowField(field.key) ? localTokens[field.key]?.value : undefined,
                                            textShadow: field.key === 'form-text-shadow' ? localTokens[field.key]?.value : undefined,
                                        }"
                                    >
                                        <span v-if="!localTokens[field.key]?.value">Aa</span>
                                    </div>
                                </div>

                                <!-- Reset Button -->
                                <button
                                    type="button"
                                    @click="clearToken(field.key)"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-gray-600 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                                >
                                    <LuRotateCcw class="w-3.5 h-3.5" />
                                    Reset
                                </button>
                            </div>
                        </div>
                    </div>
                </Transition>
            </div>
        </div>

        <!-- Global Actions -->
        <div class="flex items-center justify-between pt-4 border-t border-gray-200">
            <div class="text-xs text-gray-500">
                {{ fields.filter(f => isActive(f.key)).length }} of {{ fields.length }} fields customized
            </div>
            <button
                type="button"
                @click="fields.forEach(f => clearToken(f.key))"
                class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium text-gray-600 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors"
            >
                <LuTrash2 class="w-4 h-4" />
                Reset All
            </button>
        </div>
    </div>
</template>

<style scoped>
/* Custom scrollbar for color input */
input[type="color"]::-webkit-color-swatch-wrapper {
    padding: 0;
}
input[type="color"]::-webkit-color-swatch {
    border: none;
    border-radius: 0.5rem;
}

/* Smooth transitions */
.max-h-0 {
    max-height: 0;
}
.max-h-96 {
    max-height: 24rem;
}
</style>