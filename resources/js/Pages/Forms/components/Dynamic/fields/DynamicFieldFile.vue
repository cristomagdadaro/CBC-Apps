<script>
/**
 * DynamicFieldFile.vue - File upload field component
 */
export default {
    name: "DynamicFieldFile",
    props: {
        modelValue: { type: [Object, File, String], default: null },
        field: { type: Object, required: true },
        error: { type: String, default: null },
        required: { type: Boolean, default: false },
    },
    emits: ['update:modelValue'],
    data() {
        return {
            fileName: null,
            isDragging: false,
            localError: null,
        };
    },
    computed: {
        accept() {
            const mimes = this.field.validation_rules?.mimes;
            if (mimes) {
                return mimes.split(',').map(m => `.${m.trim()}`).join(',');
            }
            return this.field.field_config?.accept || '.pdf,.doc,.docx,.jpg,.png';
        },
        maxSize() {
            return this.field.validation_rules?.max || this.field.field_config?.maxSize || 2048;
        },
        maxSizeFormatted() {
            const kb = this.maxSize;
            if (kb >= 1024) {
                return `${(kb / 1024).toFixed(1)} MB`;
            }
            return `${kb} KB`;
        },
    },
    methods: {
        validateFile(file) {
            this.localError = null;

            if (!file) {
                return false;
            }

            const maxBytes = Number(this.maxSize || 0) * 1024;
            if (maxBytes > 0 && file.size > maxBytes) {
                this.localError = `File is too large. Maximum allowed size is ${this.maxSizeFormatted}.`;
                return false;
            }

            return true;
        },
        handleFileChange(event) {
            const file = event.target.files[0];
            if (file && this.validateFile(file)) {
                this.fileName = file.name;
                this.$emit('update:modelValue', file);
            } else {
                this.fileName = null;
                this.$emit('update:modelValue', null);
            }
        },
        handleDrop(event) {
            this.isDragging = false;
            const file = event.dataTransfer.files[0];
            if (file && this.validateFile(file)) {
                this.fileName = file.name;
                this.$emit('update:modelValue', file);
            } else {
                this.fileName = null;
                this.$emit('update:modelValue', null);
            }
        },
        handleDragOver(event) {
            this.isDragging = true;
        },
        handleDragLeave(event) {
            this.isDragging = false;
        },
        clearFile() {
            this.fileName = null;
            this.localError = null;
            this.$emit('update:modelValue', null);
        },
    },
};
</script>

<template>
    <div class="relative">
        <label v-if="field.label" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
            {{ field.label }}<span v-if="required" class="text-red-600 dark:text-red-400">*</span>
        </label>
        
        <div 
            class="border-2 border-dashed rounded-lg p-4 text-center transition-colors cursor-pointer"
            :class="{
                'border-AB bg-blue-50 dark:bg-blue-900/10': isDragging,
                'border-red-500 dark:border-red-600 bg-red-50 dark:bg-red-900/10': error,
                'border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 hover:border-gray-400 dark:hover:border-gray-500': !isDragging && !error
            }"
            @click="$refs.fileInput.click()"
            @drop.prevent="handleDrop"
            @dragover.prevent="handleDragOver"
            @dragleave.prevent="handleDragLeave"
        >
            <input
                ref="fileInput"
                type="file"
                :id="field.field_key"
                :accept="accept"
                class="hidden"
                @change="handleFileChange"
            />
            
            <div v-if="fileName" class="flex items-center justify-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="text-sm text-gray-700 dark:text-gray-200">{{ fileName }}</span>
                <button 
                    type="button" 
                    @click.stop="clearFile" 
                    class="text-red-500 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 ml-2 transition-colors"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            
            <div v-else class="text-gray-500 dark:text-gray-400">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                </svg>
                <p class="text-sm">Click to upload or drag and drop</p>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Max size: {{ maxSizeFormatted }}</p>
            </div>
        </div>
        
        <div v-if="field.description" class="text-xs text-gray-600 dark:text-gray-400 mt-1">{{ field.description }}</div>
        <transition-container type="slide-bottom">
            <InputError v-show="!!(localError || error)" class="mt-1" :message="localError || error" />
        </transition-container>
    </div>
</template>
