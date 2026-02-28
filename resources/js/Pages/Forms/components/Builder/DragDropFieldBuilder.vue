<script>
/**
 * DragDropFieldBuilder.vue
 * 
 * Visual form builder for creating and editing dynamic form templates.
 * Supports drag-and-drop field ordering, field configuration, and validation setup.
 */
import { FIELD_TYPES, FIELD_CATEGORIES, getFieldsByCategory } from "@/Modules/Events/Config/FormFieldTypes";
import FieldConfigModal from "./FieldConfigModal.vue";

export default {
    name: "DragDropFieldBuilder",
    components: {
        FieldConfigModal,
    },
    props: {
        /**
         * Initial field schema for editing existing templates
         */
        initialSchema: {
            type: Array,
            default: () => [],
        },
        /**
         * Template metadata (name, description, etc.)
         */
        templateData: {
            type: Object,
            default: () => ({ name: '', description: '', icon: null }),
        },
        /**
         * Whether the template is a system template (read-only mode)
         */
        isSystemTemplate: {
            type: Boolean,
            default: false,
        },
    },
    emits: ['save', 'cancel'],
    data() {
        return {
            template: {
                name: this.templateData.name || '',
                description: this.templateData.description || '',
                icon: this.templateData.icon || null,
            },
            fields: [...this.initialSchema],
            draggedFieldType: null,
            draggedFieldIndex: null,
            dragOverIndex: null,
            showFieldConfig: false,
            editingFieldIndex: null,
            fieldCategories: FIELD_CATEGORIES,
            fieldTypes: FIELD_TYPES,
            expandedCategories: ['basic', 'choice'],
        };
    },
    computed: {
        fieldsByCategory() {
            return getFieldsByCategory();
        },
        isValid() {
            return this.template.name.trim() !== '' && this.fields.length > 0;
        },
        canEdit() {
            return !this.isSystemTemplate;
        },
    },
    methods: {
        // ====================
        // Field Type Palette
        // ====================
        toggleCategory(categoryKey) {
            const index = this.expandedCategories.indexOf(categoryKey);
            if (index === -1) {
                this.expandedCategories.push(categoryKey);
            } else {
                this.expandedCategories.splice(index, 1);
            }
        },
        
        isCategoryExpanded(categoryKey) {
            return this.expandedCategories.includes(categoryKey);
        },

        // ====================
        // Drag & Drop - New Fields
        // ====================
        onFieldTypeDragStart(event, fieldTypeKey) {
            this.draggedFieldType = fieldTypeKey;
            event.dataTransfer.effectAllowed = 'copy';
            event.dataTransfer.setData('text/plain', fieldTypeKey);
        },

        onFieldTypeDragEnd() {
            this.draggedFieldType = null;
            this.dragOverIndex = null;
        },

        // ====================
        // Drag & Drop - Reordering
        // ====================
        onFieldDragStart(event, index) {
            if (!this.canEdit) return;
            this.draggedFieldIndex = index;
            event.dataTransfer.effectAllowed = 'move';
            event.dataTransfer.setData('text/plain', index.toString());
        },

        onFieldDragEnd() {
            this.draggedFieldIndex = null;
            this.dragOverIndex = null;
        },

        onDropZoneDragOver(event, index) {
            event.preventDefault();
            this.dragOverIndex = index;
        },

        onDropZoneDragLeave() {
            this.dragOverIndex = null;
        },

        onDropZoneDrop(event, dropIndex) {
            event.preventDefault();
            this.dragOverIndex = null;

            if (this.draggedFieldType) {
                // Adding new field from palette
                this.addFieldAtIndex(this.draggedFieldType, dropIndex);
            } else if (this.draggedFieldIndex !== null) {
                // Reordering existing field
                this.moveField(this.draggedFieldIndex, dropIndex);
            }

            this.draggedFieldType = null;
            this.draggedFieldIndex = null;
        },

        // ====================
        // Field Operations
        // ====================
        addFieldAtIndex(fieldTypeKey, index) {
            const fieldConfig = FIELD_TYPES[fieldTypeKey];
            const newField = {
                field_key: this.generateFieldKey(fieldTypeKey),
                field_type: fieldTypeKey,
                label: fieldConfig.label,
                placeholder: '',
                description: '',
                validation_rules: {},
                options: fieldConfig.hasOptions ? [{ value: 'option1', label: 'Option 1' }] : [],
                display_config: {},
                field_config: { ...fieldConfig.defaultConfig },
                sort_order: index,
            };

            this.fields.splice(index, 0, newField);
            this.updateSortOrders();
            
            // Open config modal for new field
            this.editingFieldIndex = index;
            this.showFieldConfig = true;
        },

        addFieldToEnd(fieldTypeKey) {
            this.addFieldAtIndex(fieldTypeKey, this.fields.length);
        },

        moveField(fromIndex, toIndex) {
            if (fromIndex === toIndex) return;
            
            const field = this.fields.splice(fromIndex, 1)[0];
            const adjustedIndex = toIndex > fromIndex ? toIndex - 1 : toIndex;
            this.fields.splice(adjustedIndex, 0, field);
            this.updateSortOrders();
        },

        duplicateField(index) {
            const original = this.fields[index];
            const duplicate = {
                ...JSON.parse(JSON.stringify(original)),
                field_key: this.generateFieldKey(original.field_type),
            };
            this.fields.splice(index + 1, 0, duplicate);
            this.updateSortOrders();
        },

        removeField(index) {
            this.fields.splice(index, 1);
            this.updateSortOrders();
        },

        editField(index) {
            this.editingFieldIndex = index;
            this.showFieldConfig = true;
        },

        saveFieldConfig(updatedField) {
            if (this.editingFieldIndex !== null) {
                this.fields[this.editingFieldIndex] = updatedField;
            }
            this.showFieldConfig = false;
            this.editingFieldIndex = null;
        },

        cancelFieldConfig() {
            this.showFieldConfig = false;
            this.editingFieldIndex = null;
        },

        // ====================
        // Utilities
        // ====================
        generateFieldKey(fieldType) {
            const base = fieldType.replace(/_/g, '');
            const random = Math.random().toString(36).substring(2, 6);
            return `${base}_${random}`;
        },

        updateSortOrders() {
            this.fields.forEach((field, index) => {
                field.sort_order = index;
            });
        },

        getFieldTypeConfig(fieldType) {
            return FIELD_TYPES[fieldType] || { label: fieldType, icon: 'document' };
        },

        // ====================
        // Save & Cancel
        // ====================
        handleSave() {
            if (!this.isValid) return;
            
            this.$emit('save', {
                template: this.template,
                fields: this.fields,
            });
        },

        handleCancel() {
            this.$emit('cancel');
        },
    },
};
</script>

<template>
    <div class="flex h-full bg-gray-100">
        <!-- Field Type Palette (Left Sidebar) -->
        <div class="w-64 bg-white border-r border-gray-200 overflow-y-auto flex-shrink-0">
            <div class="p-4 border-b border-gray-200">
                <h3 class="font-semibold text-gray-800">Add Fields</h3>
                <p class="text-xs text-gray-500 mt-1">Drag fields to the form area</p>
            </div>
            
            <div class="p-2">
                <template v-for="(fields, categoryKey) in fieldsByCategory" :key="categoryKey">
                    <div class="mb-2">
                        <button 
                            @click="toggleCategory(categoryKey)"
                            class="w-full flex items-center justify-between p-2 text-left text-sm font-medium text-gray-700 hover:bg-gray-50 rounded"
                        >
                            <span>{{ fieldCategories[categoryKey]?.label || categoryKey }}</span>
                            <svg 
                                class="w-4 h-4 transition-transform"
                                :class="{ 'rotate-180': isCategoryExpanded(categoryKey) }"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        
                        <div v-show="isCategoryExpanded(categoryKey)" class="mt-1 space-y-1">
                            <div
                                v-for="fieldType in fields"
                                :key="fieldType.key"
                                draggable="true"
                                @dragstart="onFieldTypeDragStart($event, fieldType.key)"
                                @dragend="onFieldTypeDragEnd"
                                @click="addFieldToEnd(fieldType.key)"
                                class="flex items-center gap-2 p-2 text-sm bg-gray-50 hover:bg-gray-100 rounded cursor-grab active:cursor-grabbing"
                            >
                                <span class="text-gray-500">✦</span>
                                <span>{{ fieldType.label }}</span>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Form Builder (Main Area) -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Form Metadata Header -->
            <div class="bg-white border-b border-gray-200 p-4">
                <div class="max-w-3xl mx-auto space-y-3">
                    <input
                        v-model="template.name"
                        type="text"
                        placeholder="Form Template Name*"
                        class="w-full text-xl font-semibold border-0 border-b-2 border-gray-200 focus:border-AB focus:ring-0 px-0"
                        :disabled="!canEdit"
                    />
                    <textarea
                        v-model="template.description"
                        placeholder="Form description (optional)"
                        rows="2"
                        class="w-full text-sm text-gray-600 border-0 border-b border-gray-100 focus:border-AB focus:ring-0 px-0 resize-none"
                        :disabled="!canEdit"
                    ></textarea>
                </div>
            </div>

            <!-- Fields Canvas -->
            <div class="flex-1 overflow-y-auto p-4">
                <div class="max-w-3xl mx-auto">
                    <!-- Empty State -->
                    <div 
                        v-if="fields.length === 0"
                        class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center"
                        @dragover.prevent="dragOverIndex = 0"
                        @dragleave="dragOverIndex = null"
                        @drop="onDropZoneDrop($event, 0)"
                        :class="{ 'border-AB bg-blue-50': dragOverIndex === 0 }"
                    >
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        <p class="mt-2 text-gray-600">Drag fields here or click on a field type</p>
                    </div>

                    <!-- Field List -->
                    <div v-else class="space-y-2">
                        <template v-for="(field, index) in fields" :key="field.field_key">
                            <!-- Drop Zone Before Field -->
                            <div
                                class="h-2 -my-1 transition-all"
                                :class="{ 'h-12 bg-blue-100 border-2 border-dashed border-AB rounded': dragOverIndex === index }"
                                @dragover="onDropZoneDragOver($event, index)"
                                @dragleave="onDropZoneDragLeave"
                                @drop="onDropZoneDrop($event, index)"
                            ></div>

                            <!-- Field Card -->
                            <div
                                :draggable="canEdit"
                                @dragstart="onFieldDragStart($event, index)"
                                @dragend="onFieldDragEnd"
                                class="bg-white border border-gray-200 rounded-lg shadow-sm group hover:shadow-md transition-shadow"
                                :class="{ 'opacity-50': draggedFieldIndex === index }"
                            >
                                <div class="flex items-start gap-3 p-4">
                                    <!-- Drag Handle -->
                                    <div 
                                        v-if="canEdit"
                                        class="flex-shrink-0 text-gray-400 cursor-grab active:cursor-grabbing pt-1"
                                    >
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M7 2a2 2 0 1 0 .001 4.001A2 2 0 0 0 7 2zm0 6a2 2 0 1 0 .001 4.001A2 2 0 0 0 7 8zm0 6a2 2 0 1 0 .001 4.001A2 2 0 0 0 7 14zm6-8a2 2 0 1 0-.001-4.001A2 2 0 0 0 13 6zm0 2a2 2 0 1 0 .001 4.001A2 2 0 0 0 13 8zm0 6a2 2 0 1 0 .001 4.001A2 2 0 0 0 13 14z" />
                                        </svg>
                                    </div>

                                    <!-- Field Content -->
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2 mb-1">
                                            <span class="text-xs px-2 py-0.5 bg-gray-100 rounded text-gray-600">
                                                {{ getFieldTypeConfig(field.field_type).label }}
                                            </span>
                                            <span v-if="field.validation_rules?.required" class="text-red-500 text-xs">Required</span>
                                        </div>
                                        <h4 class="font-medium text-gray-800">{{ field.label }}</h4>
                                        <p v-if="field.description" class="text-sm text-gray-500 mt-1">{{ field.description }}</p>
                                        <p class="text-xs text-gray-400 mt-1">Key: {{ field.field_key }}</p>
                                    </div>

                                    <!-- Actions -->
                                    <div v-if="canEdit" class="flex-shrink-0 flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <button 
                                            @click="editField(index)"
                                            class="p-1.5 text-gray-500 hover:text-AB hover:bg-gray-100 rounded"
                                            title="Edit field"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>
                                        <button 
                                            @click="duplicateField(index)"
                                            class="p-1.5 text-gray-500 hover:text-AB hover:bg-gray-100 rounded"
                                            title="Duplicate field"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                            </svg>
                                        </button>
                                        <button 
                                            @click="removeField(index)"
                                            class="p-1.5 text-gray-500 hover:text-red-500 hover:bg-red-50 rounded"
                                            title="Remove field"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <!-- Drop Zone After Last Field -->
                        <div
                            class="h-2 transition-all"
                            :class="{ 'h-12 bg-blue-100 border-2 border-dashed border-AB rounded': dragOverIndex === fields.length }"
                            @dragover="onDropZoneDragOver($event, fields.length)"
                            @dragleave="onDropZoneDragLeave"
                            @drop="onDropZoneDrop($event, fields.length)"
                        ></div>
                    </div>
                </div>
            </div>

            <!-- Footer Actions -->
            <div class="bg-white border-t border-gray-200 p-4">
                <div class="max-w-3xl mx-auto flex justify-between items-center">
                    <span class="text-sm text-gray-500">
                        {{ fields.length }} field{{ fields.length !== 1 ? 's' : '' }}
                    </span>
                    <div class="flex gap-3">
                        <button
                            @click="handleCancel"
                            class="px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-md transition-colors"
                        >
                            Cancel
                        </button>
                        <button
                            @click="handleSave"
                            :disabled="!isValid || !canEdit"
                            class="px-4 py-2 bg-AB text-white rounded-md hover:bg-AB/90 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            Save Template
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Field Configuration Modal -->
        <FieldConfigModal
            v-if="showFieldConfig && editingFieldIndex !== null"
            :field="fields[editingFieldIndex]"
            :field-types="fieldTypes"
            @save="saveFieldConfig"
            @cancel="cancelFieldConfig"
        />
    </div>
</template>

<style scoped>
.rotate-180 {
    transform: rotate(180deg);
}
</style>
