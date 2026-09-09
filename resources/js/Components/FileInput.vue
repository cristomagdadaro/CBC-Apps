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
    data() {
        return {
            originalSize: null,
            compressedSize: null,
        };
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
        formatBytes(bytes, decimals = 1) {
            if (!+bytes) return '0 Bytes';
            const k = 1024;
            const dm = decimals < 0 ? 0 : decimals;
            const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return `${parseFloat((bytes / Math.pow(k, i)).toFixed(dm))} ${sizes[i]}`;
        },
        compressImage(dataUrl, callback) {
            const img = new Image();
            img.onload = () => {
                const canvas = document.createElement("canvas");
                const MAX_WIDTH = 1200;
                const scaleSize = MAX_WIDTH / img.width;
                canvas.width = MAX_WIDTH;
                canvas.height = img.height * scaleSize;
                const ctx = canvas.getContext("2d");
                ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
                callback(canvas.toDataURL("image/jpeg", 0.8));
            };
            img.src = dataUrl;
        },
        onChange(e) {
            const file = e.target.files[0];
            if (!file) {
                this.$emit("update:modelValue", "");
                this.originalSize = null;
                this.compressedSize = null;
                return;
            }
            
            this.originalSize = file.size;
            this.compressedSize = null;

            const reader = new FileReader();
            reader.onload = (event) => {
                if (file.type && file.type.startsWith("image/")) {
                    this.compressImage(event.target.result, (compressedDataUrl) => {
                        const base64Data = compressedDataUrl.split(',')[1];
                        if (base64Data) {
                            this.compressedSize = Math.round((base64Data.length * 3) / 4);
                        }
                        this.$emit("update:modelValue", compressedDataUrl);
                    });
                } else {
                    this.$emit("update:modelValue", event.target.result);
                }
            };
            reader.readAsDataURL(file);
        },
        onClear() {
            this.$emit("update:modelValue", "");
            this.originalSize = null;
            this.compressedSize = null;
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
            <LuUpload class="h-3.5 w-3.5 text-indigo-500 dark:text-indigo-400" />
        </template>

        <template #default="{ inputId, isInvalid, isValid, guideId }">
            <div class="relative w-full">
                <input
                    :id="inputId"
                    ref="input"
                    :name="id"
                    :class="['block w-full text-sm text-slate-500 dark:text-slate-400', 'file:mr-4 file:rounded-xl file:border-0 file:px-4 file:py-2.5', 'file:text-sm file:font-semibold', 'overflow-hidden rounded-xl border transition-all duration-200 ease-out', 'hover:file:bg-indigo-100 hover:file:text-indigo-700 dark:hover:file:bg-indigo-500/20 dark:hover:file:text-indigo-300', isInvalid ? 'border-rose-300 bg-rose-50/50 file:bg-rose-100 file:text-rose-700 focus-within:border-rose-500 focus-within:ring-1 focus-within:ring-rose-500 dark:border-rose-700 dark:bg-rose-900/10 dark:file:bg-rose-500/20 dark:file:text-rose-400' : isValid ? 'border-emerald-300 bg-white file:bg-emerald-100 file:text-emerald-700 focus-within:border-emerald-500 focus-within:ring-1 focus-within:ring-emerald-500 dark:border-emerald-700 dark:bg-slate-900 dark:file:bg-emerald-500/20 dark:file:text-emerald-400' : 'border-slate-200 bg-slate-50 file:bg-indigo-50 file:text-indigo-600 focus-within:border-indigo-500 focus-within:bg-white focus-within:ring-1 focus-within:ring-indigo-500 dark:border-slate-700 dark:bg-slate-800/50 dark:file:bg-indigo-500/10 dark:file:text-indigo-400 dark:focus-within:bg-slate-900', disabled ? 'cursor-not-allowed opacity-60 file:cursor-not-allowed' : 'cursor-pointer file:cursor-pointer']"
                    type="file"
                    :disabled="disabled"
                    :accept="acceptOnly"
                    :aria-invalid="isInvalid"
                    :aria-describedby="guideId"
                    @change="onChange" />
                
                <div v-if="originalSize && compressedSize" class="mt-1.5 flex items-center gap-1.5 px-1 text-[11px] font-medium text-emerald-600 dark:text-emerald-400">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    Compressed from {{ formatBytes(originalSize) }} to {{ formatBytes(compressedSize) }}
                </div>
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
