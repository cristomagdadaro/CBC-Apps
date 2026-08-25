<script>
import FieldMixin from "@/Components/Forms/FieldMixin";

export default {
    name: "FileInput",
    mixins: [FieldMixin],
    props: {
        autocomplete: String,
        type: String,
        fileType: String,
    },
    computed: {
        acceptOnly() {
            if (this.fileType === "image") {
                return "image/png, image/gif, image/jpeg";
            }
            return null;
        },
    },
    methods: {
        onChange(e) {
            this.$emit("update:modelValue", e.target.value);
        },
        onClear() {
            this.$emit("update:modelValue", "");
            this.$emit("clear");
            if (this.$refs.input) {
                this.$refs.input.value = "";
            }
        },
    },
};
</script>

<template>
    <Field
        :id="id"
        :label="label"
        :required="required"
        :hint="hint"
        :guide="guide"
        :error="error"
        :disabled="disabled"
        :classes="classes"
        :show-valid-indicator="false"
        :clearable="true"
        :has-value="hasValue"
        @clear="onClear">
        <template #label-icon>
            <LuUpload class="w-3.5 h-3.5 text-indigo-500 dark:text-indigo-400" />
        </template>

        <template #default="{ inputId, isInvalid, isValid, guideId }">
            <div class="relative w-full">
                <input
                    :id="inputId"
                    ref="input"
                    :name="id"
                    :class="['block w-full text-sm text-slate-500 dark:text-slate-400', 'file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0', 'file:text-sm file:font-semibold', 'transition-all duration-200 ease-out border rounded-xl overflow-hidden', 'hover:file:bg-indigo-100 dark:hover:file:bg-indigo-500/20 hover:file:text-indigo-700 dark:hover:file:text-indigo-300', isInvalid ? 'border-rose-300 dark:border-rose-700 bg-rose-50/50 dark:bg-rose-900/10 focus-within:border-rose-500 focus-within:ring-1 focus-within:ring-rose-500 file:bg-rose-100 file:text-rose-700 dark:file:bg-rose-500/20 dark:file:text-rose-400' : isValid ? 'border-emerald-300 dark:border-emerald-700 bg-white dark:bg-slate-900 focus-within:border-emerald-500 focus-within:ring-1 focus-within:ring-emerald-500 file:bg-emerald-100 file:text-emerald-700 dark:file:bg-emerald-500/20 dark:file:text-emerald-400' : 'border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 focus-within:bg-white dark:focus-within:bg-slate-900 focus-within:border-indigo-500 focus-within:ring-1 focus-within:ring-indigo-500 file:bg-indigo-50 file:text-indigo-600 dark:file:bg-indigo-500/10 dark:file:text-indigo-400', disabled ? 'opacity-60 cursor-not-allowed file:cursor-not-allowed' : 'cursor-pointer file:cursor-pointer']"
                    type="file"
                    :disabled="disabled"
                    :accept="acceptOnly"
                    :aria-invalid="isInvalid"
                    :aria-describedby="guideId"
                    @change="onChange" />
            </div>
        </template>
    </Field>
</template>

<style scoped>
input[type="file"] {
    /* Ensures the focus ring applies to the whole input nicely */
    outline: none;
}
</style>
