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
                this.$emit("update:modelValue", value);
            },
            deep: true,
        },
    },
    methods: {
        isTextColorOrShadowField(key) {
            return key.includes("-text-color") || key === "form-text-shadow";
        },
        handleModeChange(key, rawValue) {
            const mode = rawValue === "" ? null : rawValue;
            const current = this.localTokens[key] || { mode: null, value: null };
            let value = current.value;

            if (!mode) {
                value = null;
            } else if (mode === "color" && !value) {
                value = "#1f2937"; // Default fallback color
            } else if (mode === "image" && !value) {
                value = "";
            }

            this.updateToken(key, { mode, value });
        },
        updateToken(key, patch) {
            if ((key.includes("-text-color") || this.fields.find((f) => f.key === key)) && patch.hasOwnProperty("value") && typeof patch.value === "string" && patch.value.startsWith("#")) {
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
    <div class="w-full max-w-3xl mx-auto space-y-5">
        <!-- Error Alert -->
        <div
            v-if="error"
            class="flex items-center gap-2 text-sm font-semibold text-red-700 dark:text-red-400 bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/30 px-4 py-3 rounded-xl shadow-sm">
            <LuAlertCircle class="w-5 h-5 shrink-0" />
            {{ error }}
        </div>

        <!-- Style Fields List -->
        <div class="space-y-3.5">
            <div
                v-for="field in fields"
                :key="field.key"
                class="group bg-white dark:bg-slate-800/80 backdrop-blur-sm border rounded-xl overflow-hidden transition-all duration-200"
                :class="[isActive(field.key) ? 'border-indigo-300 dark:border-indigo-500/50 shadow-sm shadow-indigo-500/5' : 'border-slate-200 dark:border-slate-700 hover:border-slate-300 dark:hover:border-slate-600', expandedFields[field.key] ? 'ring-1 ring-slate-900/5 dark:ring-white/5 shadow-md' : '']">
                <!-- Field Header (Click to expand) -->
                <button
                    type="button"
                    @click="toggleExpand(field.key)"
                    class="w-full flex items-center justify-between p-4 sm:px-5 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                    <div class="flex items-center gap-3.5">
                        <!-- Status Indicator -->
                        <div
                            class="w-2.5 h-2.5 rounded-full transition-all duration-300"
                            :class="isActive(field.key) ? 'bg-indigo-500 shadow-[0_0_8px_rgba(79,70,229,0.5)]' : 'bg-slate-200 dark:bg-slate-700'" />

                        <div class="text-left">
                            <p class="font-bold text-slate-800 dark:text-slate-200 text-sm tracking-wide">
                                {{ field.label }}
                            </p>
                            <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-0.5">
                                {{ field.description }}
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 shrink-0">
                        <!-- Active Mode Badge -->
                        <span
                            v-if="localTokens[field.key]?.mode"
                            class="inline-flex items-center px-2.5 py-0.5 rounded-md text-[0.65rem] font-bold uppercase tracking-widest border"
                            :class="localTokens[field.key]?.mode === 'color' ? 'bg-blue-50 dark:bg-blue-500/10 text-blue-700 dark:text-blue-400 border-blue-200 dark:border-blue-500/30' : 'bg-purple-50 dark:bg-purple-500/10 text-purple-700 dark:text-purple-400 border-purple-200 dark:border-purple-500/30'">
                            {{ localTokens[field.key]?.mode }}
                        </span>

                        <!-- Expand Icon -->
                        <div class="p-1.5 rounded-lg bg-slate-100 dark:bg-slate-800 group-hover:bg-slate-200 dark:group-hover:bg-slate-700 transition-colors">
                            <LuChevronDown
                                class="w-4 h-4 text-slate-500 dark:text-slate-400 transition-transform duration-300"
                                :class="expandedFields[field.key] ? 'rotate-180' : ''" />
                        </div>
                    </div>
                </button>

                <!-- Field Content Configuration Area -->
                <Transition
                    enter-active-class="transition-all duration-300 ease-out"
                    enter-from-class="opacity-0 max-h-0"
                    enter-to-class="opacity-100 max-h-96"
                    leave-active-class="transition-all duration-200 ease-in"
                    leave-from-class="opacity-100 max-h-96"
                    leave-to-class="opacity-0 max-h-0">
                    <div
                        v-show="expandedFields[field.key]"
                        class="border-t border-slate-100 dark:border-slate-700/50 bg-slate-50/50 dark:bg-slate-900/30">
                        <div class="p-4 sm:p-5 space-y-6">
                            <!-- Mode Selector (For Backgrounds) -->
                            <div
                                v-if="!isTextColorOrShadowField(field.key)"
                                class="space-y-2.5">
                                <label class="text-[0.65rem] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest block">Style Type</label>
                                <div class="grid grid-cols-3 gap-3">
                                    <button
                                        type="button"
                                        @click="handleModeChange(field.key, '')"
                                        class="flex flex-col items-center gap-2 p-3 rounded-xl border-2 transition-all duration-200 active:scale-95"
                                        :class="!localTokens[field.key]?.mode ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-500/10 dark:border-indigo-400 shadow-sm' : 'border-slate-200 dark:border-slate-700 hover:border-slate-300 dark:hover:border-slate-600 bg-white dark:bg-slate-800'">
                                        <LuCircle
                                            class="w-5 h-5"
                                            :class="!localTokens[field.key]?.mode ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-400'" />
                                        <span
                                            class="text-xs font-bold"
                                            :class="!localTokens[field.key]?.mode ? 'text-indigo-700 dark:text-indigo-300' : 'text-slate-600 dark:text-slate-300'">
                                            Default
                                        </span>
                                    </button>

                                    <button
                                        type="button"
                                        @click="handleModeChange(field.key, 'color')"
                                        class="flex flex-col items-center gap-2 p-3 rounded-xl border-2 transition-all duration-200 active:scale-95"
                                        :class="localTokens[field.key]?.mode === 'color' ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-500/10 dark:border-indigo-400 shadow-sm' : 'border-slate-200 dark:border-slate-700 hover:border-slate-300 dark:hover:border-slate-600 bg-white dark:bg-slate-800'">
                                        <div
                                            class="w-5 h-5 rounded-full border border-slate-300 dark:border-slate-600 shadow-inner"
                                            :style="{
                                                background: localTokens[field.key]?.value || '#1f2937',
                                            }" />
                                        <span
                                            class="text-xs font-bold"
                                            :class="localTokens[field.key]?.mode === 'color' ? 'text-indigo-700 dark:text-indigo-300' : 'text-slate-600 dark:text-slate-300'">
                                            Solid Color
                                        </span>
                                    </button>

                                    <button
                                        type="button"
                                        @click="handleModeChange(field.key, 'image')"
                                        class="flex flex-col items-center gap-2 p-3 rounded-xl border-2 transition-all duration-200 active:scale-95"
                                        :class="localTokens[field.key]?.mode === 'image' ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-500/10 dark:border-indigo-400 shadow-sm' : 'border-slate-200 dark:border-slate-700 hover:border-slate-300 dark:hover:border-slate-600 bg-white dark:bg-slate-800'">
                                        <LuImage
                                            class="w-5 h-5"
                                            :class="localTokens[field.key]?.mode === 'image' ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-400'" />
                                        <span
                                            class="text-xs font-bold"
                                            :class="localTokens[field.key]?.mode === 'image' ? 'text-indigo-700 dark:text-indigo-300' : 'text-slate-600 dark:text-slate-300'">
                                            Image URL
                                        </span>
                                    </button>
                                </div>
                            </div>

                            <!-- Color & Hex Input -->
                            <div
                                v-if="localTokens[field.key]?.mode === 'color' || isTextColorOrShadowField(field.key)"
                                class="space-y-2.5">
                                <label class="text-[0.65rem] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest block">
                                    {{ isTextColorOrShadowField(field.key) ? "Color Value" : "Background Color" }}
                                </label>

                                <div class="flex items-center gap-3">
                                    <div class="relative shrink-0">
                                        <input
                                            type="color"
                                            class="w-12 h-12 rounded-xl cursor-pointer border border-slate-200 dark:border-slate-700 p-1 bg-white dark:bg-slate-800 shadow-sm"
                                            :value="localTokens[field.key]?.value || (isTextColorOrShadowField(field.key) ? '#111827' : '#1f2937')"
                                            @input="
                                                updateToken(field.key, {
                                                    value: $event.target.value,
                                                })
                                            " />
                                    </div>

                                    <div class="flex-1 relative">
                                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 font-mono text-sm font-bold pointer-events-none">#</span>
                                        <input
                                            type="text"
                                            :value="(localTokens[field.key]?.value || '').replace('#', '')"
                                            @input="
                                                updateToken(field.key, {
                                                    value: '#' + $event.target.value,
                                                })
                                            "
                                            class="w-full pl-8 pr-4 py-3 text-sm font-mono font-bold bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent shadow-sm transition-all"
                                            placeholder="1F2937"
                                            maxlength="6" />
                                    </div>
                                </div>
                            </div>

                            <!-- Image URL Input -->
                            <div
                                v-if="localTokens[field.key]?.mode === 'image'"
                                class="space-y-2.5">
                                <label class="text-[0.65rem] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest block">Image URL</label>
                                <div class="relative">
                                    <LuLink class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                                    <input
                                        v-model="localTokens[field.key].value"
                                        type="url"
                                        placeholder="https://cdn.example.com/background.jpg"
                                        class="w-full pl-10 pr-4 py-3 text-sm font-medium bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent shadow-sm transition-all" />
                                </div>
                                <p class="text-[0.65rem] font-semibold text-slate-500 flex items-center gap-1.5 mt-2">
                                    <LuInfo class="w-3.5 h-3.5 text-indigo-400" />
                                    Use a public URL or relative storage path
                                </p>
                            </div>

                            <!-- Text Shadow Input -->
                            <div
                                v-if="field.key === 'form-text-shadow'"
                                class="space-y-2.5">
                                <label class="text-[0.65rem] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest block">Shadow Value</label>
                                <input
                                    v-model="localTokens[field.key].value"
                                    type="text"
                                    placeholder="0 1px 2px rgba(0, 0, 0, 0.35)"
                                    class="w-full px-4 py-3 text-sm font-mono font-medium bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent shadow-sm transition-all" />
                                <p class="text-[0.65rem] font-semibold text-slate-500">Must be a valid CSS text-shadow format.</p>
                            </div>

                            <!-- Mini Preview & Reset -->
                            <div class="flex items-center justify-between pt-5 mt-2 border-t border-slate-200/60 dark:border-slate-700/60">
                                <div class="flex items-center gap-3">
                                    <span class="text-[0.65rem] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">Preview</span>
                                    <div
                                        class="w-20 h-10 rounded-lg border border-slate-200 dark:border-slate-700 shadow-inner flex items-center justify-center text-sm font-black overflow-hidden relative"
                                        :style="{
                                            backgroundColor: localTokens[field.key]?.mode === 'color' ? localTokens[field.key]?.value : undefined,
                                            backgroundImage: localTokens[field.key]?.mode === 'image' ? `url(${localTokens[field.key]?.value})` : undefined,
                                            backgroundSize: 'cover',
                                            backgroundPosition: 'center',
                                            color: isTextColorOrShadowField(field.key) ? localTokens[field.key]?.value : undefined,
                                            textShadow: field.key === 'form-text-shadow' ? localTokens[field.key]?.value : undefined,
                                        }">
                                        <div
                                            v-if="!localTokens[field.key]?.value"
                                            class="absolute inset-0 bg-slate-100 dark:bg-slate-800 opacity-50"></div>
                                        <span
                                            class="relative z-10"
                                            :class="!localTokens[field.key]?.value ? 'text-slate-400' : ''">
                                            Aa
                                        </span>
                                    </div>
                                </div>

                                <button
                                    type="button"
                                    @click="clearToken(field.key)"
                                    class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-bold text-slate-500 dark:text-slate-400 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/10 rounded-lg transition-colors">
                                    <LuRotateCcw class="w-3.5 h-3.5" />
                                    Reset
                                </button>
                            </div>
                        </div>
                    </div>
                </Transition>
            </div>
        </div>

        <!-- Global Actions Footer -->
        <div class="flex items-center justify-between pt-6 border-t border-slate-200 dark:border-slate-800">
            <div class="text-[0.65rem] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">
                <span class="text-indigo-600 dark:text-indigo-400">
                    {{ fields.filter((f) => isActive(f.key)).length }}
                </span>
                of {{ fields.length }} customized
            </div>
            <button
                type="button"
                @click="fields.forEach((f) => clearToken(f.key))"
                class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-bold text-red-500 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/10 rounded-xl transition-colors">
                <LuTrash2 class="w-4 h-4" />
                Reset All Fields
            </button>
        </div>
    </div>
</template>

<style scoped>
/* Custom scrollbar for color input wrapper removal */
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
