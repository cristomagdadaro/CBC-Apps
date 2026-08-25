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
    <div class="mx-auto w-full max-w-3xl space-y-5">
        <!-- Error Alert -->
        <div
            v-if="error"
            class="flex items-center gap-2 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700 shadow-sm dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-400">
            <LuAlertCircle class="h-5 w-5 shrink-0" />
            {{ error }}
        </div>

        <!-- Style Fields List -->
        <div class="space-y-3.5">
            <div
                v-for="field in fields"
                :key="field.key"
                class="group overflow-hidden rounded-xl border bg-white backdrop-blur-sm transition-all duration-200 dark:bg-slate-800/80"
                :class="[isActive(field.key) ? 'border-indigo-300 shadow-sm shadow-indigo-500/5 dark:border-indigo-500/50' : 'border-slate-200 hover:border-slate-300 dark:border-slate-700 dark:hover:border-slate-600', expandedFields[field.key] ? 'shadow-md ring-1 ring-slate-900/5 dark:ring-white/5' : '']">
                <!-- Field Header (Click to expand) -->
                <button
                    type="button"
                    @click="toggleExpand(field.key)"
                    class="flex w-full items-center justify-between p-4 transition-colors hover:bg-slate-50 sm:px-5 dark:hover:bg-slate-800/50">
                    <div class="flex items-center gap-3.5">
                        <!-- Status Indicator -->
                        <div
                            class="h-2.5 w-2.5 rounded-full transition-all duration-300"
                            :class="isActive(field.key) ? 'bg-indigo-500 shadow-[0_0_8px_rgba(79,70,229,0.5)]' : 'bg-slate-200 dark:bg-slate-700'" />

                        <div class="text-left">
                            <p class="text-sm font-bold tracking-wide text-slate-800 dark:text-slate-200">
                                {{ field.label }}
                            </p>
                            <p class="mt-0.5 text-xs font-medium text-slate-500 dark:text-slate-400">
                                {{ field.description }}
                            </p>
                        </div>
                    </div>

                    <div class="flex shrink-0 items-center gap-3">
                        <!-- Active Mode Badge -->
                        <span
                            v-if="localTokens[field.key]?.mode"
                            class="inline-flex items-center rounded-md border px-2.5 py-0.5 text-[0.65rem] font-bold uppercase tracking-widest"
                            :class="localTokens[field.key]?.mode === 'color' ? 'border-blue-200 bg-blue-50 text-blue-700 dark:border-blue-500/30 dark:bg-blue-500/10 dark:text-blue-400' : 'border-purple-200 bg-purple-50 text-purple-700 dark:border-purple-500/30 dark:bg-purple-500/10 dark:text-purple-400'">
                            {{ localTokens[field.key]?.mode }}
                        </span>

                        <!-- Expand Icon -->
                        <div class="rounded-lg bg-slate-100 p-1.5 transition-colors group-hover:bg-slate-200 dark:bg-slate-800 dark:group-hover:bg-slate-700">
                            <LuChevronDown
                                class="h-4 w-4 text-slate-500 transition-transform duration-300 dark:text-slate-400"
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
                        class="border-t border-slate-100 bg-slate-50/50 dark:border-slate-700/50 dark:bg-slate-900/30">
                        <div class="space-y-6 p-4 sm:p-5">
                            <!-- Mode Selector (For Backgrounds) -->
                            <div
                                v-if="!isTextColorOrShadowField(field.key)"
                                class="space-y-2.5">
                                <label class="block text-[0.65rem] font-bold uppercase tracking-widest text-slate-500 dark:text-slate-400">Style Type</label>
                                <div class="grid grid-cols-3 gap-3">
                                    <button
                                        type="button"
                                        @click="handleModeChange(field.key, '')"
                                        class="flex flex-col items-center gap-2 rounded-xl border-2 p-3 transition-all duration-200 active:scale-95"
                                        :class="!localTokens[field.key]?.mode ? 'border-indigo-500 bg-indigo-50 shadow-sm dark:border-indigo-400 dark:bg-indigo-500/10' : 'border-slate-200 bg-white hover:border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:hover:border-slate-600'">
                                        <LuCircle
                                            class="h-5 w-5"
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
                                        class="flex flex-col items-center gap-2 rounded-xl border-2 p-3 transition-all duration-200 active:scale-95"
                                        :class="localTokens[field.key]?.mode === 'color' ? 'border-indigo-500 bg-indigo-50 shadow-sm dark:border-indigo-400 dark:bg-indigo-500/10' : 'border-slate-200 bg-white hover:border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:hover:border-slate-600'">
                                        <div
                                            class="h-5 w-5 rounded-full border border-slate-300 shadow-inner dark:border-slate-600"
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
                                        class="flex flex-col items-center gap-2 rounded-xl border-2 p-3 transition-all duration-200 active:scale-95"
                                        :class="localTokens[field.key]?.mode === 'image' ? 'border-indigo-500 bg-indigo-50 shadow-sm dark:border-indigo-400 dark:bg-indigo-500/10' : 'border-slate-200 bg-white hover:border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:hover:border-slate-600'">
                                        <LuImage
                                            class="h-5 w-5"
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
                                <label class="block text-[0.65rem] font-bold uppercase tracking-widest text-slate-500 dark:text-slate-400">
                                    {{ isTextColorOrShadowField(field.key) ? "Color Value" : "Background Color" }}
                                </label>

                                <div class="flex items-center gap-3">
                                    <div class="relative shrink-0">
                                        <input
                                            type="color"
                                            class="h-12 w-12 cursor-pointer rounded-xl border border-slate-200 bg-white p-1 shadow-sm dark:border-slate-700 dark:bg-slate-800"
                                            :value="localTokens[field.key]?.value || (isTextColorOrShadowField(field.key) ? '#111827' : '#1f2937')"
                                            @input="
                                                updateToken(field.key, {
                                                    value: $event.target.value,
                                                })
                                            " />
                                    </div>

                                    <div class="relative flex-1">
                                        <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 font-mono text-sm font-bold text-slate-400">#</span>
                                        <input
                                            type="text"
                                            :value="(localTokens[field.key]?.value || '').replace('#', '')"
                                            @input="
                                                updateToken(field.key, {
                                                    value: '#' + $event.target.value,
                                                })
                                            "
                                            class="w-full rounded-xl border border-slate-300 bg-white py-3 pl-8 pr-4 font-mono text-sm font-bold text-slate-900 shadow-sm transition-all focus:border-transparent focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                                            placeholder="1F2937"
                                            maxlength="6" />
                                    </div>
                                </div>
                            </div>

                            <!-- Image URL Input -->
                            <div
                                v-if="localTokens[field.key]?.mode === 'image'"
                                class="space-y-2.5">
                                <label class="block text-[0.65rem] font-bold uppercase tracking-widest text-slate-500 dark:text-slate-400">Image URL</label>
                                <div class="relative">
                                    <LuLink class="absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                                    <input
                                        v-model="localTokens[field.key].value"
                                        type="url"
                                        placeholder="https://cdn.example.com/background.jpg"
                                        class="w-full rounded-xl border border-slate-300 bg-white py-3 pl-10 pr-4 text-sm font-medium text-slate-900 shadow-sm transition-all focus:border-transparent focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white" />
                                </div>
                                <p class="mt-2 flex items-center gap-1.5 text-[0.65rem] font-semibold text-slate-500">
                                    <LuInfo class="h-3.5 w-3.5 text-indigo-400" />
                                    Use a public URL or relative storage path
                                </p>
                            </div>

                            <!-- Text Shadow Input -->
                            <div
                                v-if="field.key === 'form-text-shadow'"
                                class="space-y-2.5">
                                <label class="block text-[0.65rem] font-bold uppercase tracking-widest text-slate-500 dark:text-slate-400">Shadow Value</label>
                                <input
                                    v-model="localTokens[field.key].value"
                                    type="text"
                                    placeholder="0 1px 2px rgba(0, 0, 0, 0.35)"
                                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 font-mono text-sm font-medium text-slate-900 shadow-sm transition-all focus:border-transparent focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white" />
                                <p class="text-[0.65rem] font-semibold text-slate-500">Must be a valid CSS text-shadow format.</p>
                            </div>

                            <!-- Mini Preview & Reset -->
                            <div class="mt-2 flex items-center justify-between border-t border-slate-200/60 pt-5 dark:border-slate-700/60">
                                <div class="flex items-center gap-3">
                                    <span class="text-[0.65rem] font-bold uppercase tracking-widest text-slate-500 dark:text-slate-400">Preview</span>
                                    <div
                                        class="relative flex h-10 w-20 items-center justify-center overflow-hidden rounded-lg border border-slate-200 text-sm font-black shadow-inner dark:border-slate-700"
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
                                            class="absolute inset-0 bg-slate-100 opacity-50 dark:bg-slate-800"></div>
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
                                    class="inline-flex items-center gap-1.5 rounded-lg px-3 py-2 text-xs font-bold text-slate-500 transition-colors hover:bg-red-50 hover:text-red-600 dark:text-slate-400 dark:hover:bg-red-500/10 dark:hover:text-red-400">
                                    <LuRotateCcw class="h-3.5 w-3.5" />
                                    Reset
                                </button>
                            </div>
                        </div>
                    </div>
                </Transition>
            </div>
        </div>

        <!-- Global Actions Footer -->
        <div class="flex items-center justify-between border-t border-slate-200 pt-6 dark:border-slate-800">
            <div class="text-[0.65rem] font-bold uppercase tracking-widest text-slate-500 dark:text-slate-400">
                <span class="text-indigo-600 dark:text-indigo-400">
                    {{ fields.filter((f) => isActive(f.key)).length }}
                </span>
                of {{ fields.length }} customized
            </div>
            <button
                type="button"
                @click="fields.forEach((f) => clearToken(f.key))"
                class="inline-flex items-center gap-1.5 rounded-xl px-4 py-2 text-sm font-bold text-red-500 transition-colors hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-500/10 dark:hover:text-red-400">
                <LuTrash2 class="h-4 w-4" />
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
