<script>
import FieldMixin from "@/Components/Forms/FieldMixin";

export default {
    name: "TextArea",
    mixins: [FieldMixin],
    props: {
        autocomplete: { type: String, default: "" },
        type: { type: String, default: "" },
        rows: { type: Number, default: 4 },
        maxLength: { type: Number, default: null },
        expandable: { type: Boolean, default: false },
    },
    data() {
        return {
            isExpanded: false,
        };
    },
    mounted() {
        this.$nextTick(() => this.adjustHeight());
    },
    watch: {
        modelValue() {
            this.$nextTick(() => this.adjustHeight());
        },
    },
    computed: {
        charCount() {
            return String(this.modelValue || "").length;
        },
        isNearLimit() {
            return this.maxLength && this.charCount > this.maxLength * 0.9 && this.charCount <= this.maxLength;
        },
        isOverLimit() {
            return this.maxLength && this.charCount > this.maxLength;
        },
    },
    methods: {
        adjustHeight() {
            const textarea = this.$refs.input;
            if (!textarea) return;
            const lineHeight = parseFloat(getComputedStyle(textarea).lineHeight) || 24;
            const minHeight = this.rows * lineHeight;
            textarea.style.height = "auto";
            const newHeight = textarea.scrollHeight;
            textarea.style.height = `${Math.max(newHeight, minHeight)}px`;
        },
        onInput(e) {
            this.$emit("update:modelValue", e.target.value);
            this.adjustHeight();
        },
        toggleExpand() {
            this.isExpanded = !this.isExpanded;
            this.$nextTick(() => this.adjustHeight());
        },
    },
};
</script>

<template>
    <div
        class="w-full transition-all duration-300 ease-out"
        :class="isExpanded ? 'fixed inset-4 z-50 flex flex-col rounded-2xl border border-slate-200/60 bg-white/95 p-5 shadow-2xl backdrop-blur-xl sm:inset-10 sm:p-6 dark:border-slate-800 dark:bg-slate-900/95' : ''">
        <Transition name="fade">
            <div
                v-if="isExpanded"
                class="fixed inset-0 -z-10 bg-slate-900/60 backdrop-blur-sm"
                @click="toggleExpand"></div>
        </Transition>

        <Field
            :id="id"
            :label="label"
            :required="required"
            :hint="hint"
            :guide="isExpanded ? null : guide"
            :error="error || (isOverLimit ? 'Character limit exceeded' : null)"
            :clearable="clearable && !isExpanded"
            :has-value="hasValue"
            :disabled="disabled"
            :datalist-id="datalistId"
            :datalist-options="datalistOptions"
            :classes="[classes, isExpanded ? 'flex-1 h-full flex flex-col' : '']"
            @clear="onClear">
            <template #label-icon>
                <LuAlignLeft class="h-3.5 w-3.5 text-indigo-500 dark:text-indigo-400" />
            </template>

            <template #header-actions>
                <span
                    v-if="maxLength"
                    class="text-[0.65rem] font-semibold tracking-wider"
                    :class="{
                        'text-slate-400 dark:text-slate-500': !isNearLimit && !isOverLimit,
                        'text-amber-500': isNearLimit,
                        'text-rose-500': isOverLimit,
                    }">
                    {{ charCount }} / {{ maxLength }}
                </span>

                <button
                    v-if="expandable"
                    type="button"
                    @click="toggleExpand"
                    class="rounded-lg p-1.5 text-slate-400 transition-colors hover:bg-indigo-50 hover:text-indigo-600 dark:hover:bg-indigo-500/10 dark:hover:text-indigo-400"
                    :aria-label="isExpanded ? 'Collapse' : 'Expand'">
                    <LuMinimize2
                        v-if="isExpanded"
                        class="h-3.5 w-3.5" />
                    <LuMaximize2
                        v-else
                        class="h-3.5 w-3.5" />
                </button>
            </template>

            <template #default="{ inputId, isInvalid, isValid, guideId }">
                <textarea
                    :id="inputId"
                    ref="input"
                    :rows="rows"
                    :class="['w-full resize-none overflow-hidden rounded-xl border px-4 py-3 text-sm transition-all duration-200 ease-out', 'placeholder:text-slate-400 dark:placeholder:text-slate-500', isInvalid ? 'border-rose-300 bg-rose-50/50 text-rose-900 focus:border-rose-500 focus:ring-1 focus:ring-rose-500 dark:border-rose-700 dark:bg-rose-900/10 dark:text-rose-100' : isValid ? 'border-emerald-300 bg-white text-slate-900 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 dark:border-emerald-700 dark:bg-slate-900 dark:text-slate-100' : 'border-slate-200 bg-slate-50 text-slate-900 focus:border-indigo-500 focus:bg-white focus:ring-1 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-800/50 dark:text-slate-100 dark:focus:bg-slate-900', disabled ? 'cursor-not-allowed bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400' : '', isExpanded ? 'h-full min-h-[200px] flex-1 shadow-inner' : 'shadow-sm', (clearable || isValid || isInvalid) && !isExpanded ? 'pr-11' : '']"
                    :value="modelValue"
                    :placeholder="placeholder"
                    :disabled="disabled"
                    :list="datalistId"
                    :required="required"
                    :aria-invalid="isInvalid"
                    :aria-describedby="guideId"
                    @input="onInput"
                    @focus="onFocus"
                    @blur="onBlur" />

                <div
                    v-if="isExpanded"
                    class="absolute bottom-4 right-4 flex justify-end">
                    <button
                        type="button"
                        @click="toggleExpand"
                        class="rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-md shadow-indigo-600/20 transition-all hover:bg-indigo-700 active:scale-95">
                        Done
                    </button>
                </div>
            </template>
        </Field>
    </div>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

textarea:focus {
    outline: none;
}

/* Custom autofill styling tailored for slate theme */
textarea:-webkit-autofill,
textarea:-webkit-autofill:hover,
textarea:-webkit-autofill:focus {
    -webkit-box-shadow: 0 0 0px 1000px #f8fafc inset;
    -webkit-text-fill-color: #0f172a;
}

.dark textarea:-webkit-autofill,
.dark textarea:-webkit-autofill:hover,
.dark textarea:-webkit-autofill:focus {
    -webkit-box-shadow: 0 0 0px 1000px #0f172a inset;
    -webkit-text-fill-color: #f1f5f9;
}

textarea {
    transition:
        height 0.2s cubic-bezier(0.4, 0, 0.2, 1),
        border-color 0.2s ease,
        box-shadow 0.2s ease,
        background-color 0.2s ease;
}
</style>
