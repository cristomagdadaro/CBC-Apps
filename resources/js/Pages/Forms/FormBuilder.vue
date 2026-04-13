<script>
/**
 * FormBuilder.vue
 *
 * Page for managing form templates - create new templates or edit existing ones.
 * Uses the DragDropFieldBuilder component for the visual form building experience.
 */
import DragDropFieldBuilder from "@/Pages/Forms/components/Builder/DragDropFieldBuilder.vue";
import FormsHeaderActions from "@/Pages/Forms/components/FormsHeaderActions.vue";
import ApiMixin from "@/Modules/mixins/ApiMixin";
import ConcreteApiService from "@/Modules/infrastructure/ConcreteApiService";

export default {
    name: "FormBuilder",
    mixins: [ApiMixin],
    components: {
        DragDropFieldBuilder,
        FormsHeaderActions,
    },
    props: {
        /**
         * Template ID when editing an existing template
         */
        templateId: {
            type: String,
            default: null,
        },
    },
    data() {
        return {
            activeTab: "templates",
            templates: [],
            loadingTemplates: true,
            selectedTemplate: null,
            isEditing: false,
            isCreatingNew: false,
            saving: false,
            error: null,
            api: new ConcreteApiService(),
        };
    },
    computed: {
        systemTemplates() {
            return this.templates.filter(t => t.is_system);
        },
        customTemplates() {
            return this.templates.filter(t => !t.is_system);
        },
        builderTitle() {
            if (this.isCreatingNew) return 'Create New Template';
            if (this.selectedTemplate) return `Edit: ${this.selectedTemplate.name}`;
            return 'Form Builder';
        },
    },
    mounted() {
        this.loadTemplates();
    },
    methods: {
        async loadTemplates() {
            this.loadingTemplates = true;
            this.error = null;
            try {
                const response = await this.fetchGetApi('api.form-builder.templates.index');
                this.templates = response.data || [];
            } catch (err) {
                this.error = 'Failed to load templates';
                console.error('Error loading templates:', err);
            } finally {
                this.loadingTemplates = false;
            }
        },

        async selectTemplate(template) {
            try {
                const response = await this.fetchGetApi('api.form-builder.templates.show', { routeParams: { id: template.id } });
                this.selectedTemplate = response.data;
                this.isEditing = true;
                this.isCreatingNew = false;
                this.activeTab = "builder";
            } catch (err) {
                this.error = 'Failed to load template details';
                console.error('Error loading template:', err);
            }
        },

        startNewTemplate() {
            this.selectedTemplate = null;
            this.isCreatingNew = true;
            this.isEditing = false;
            this.activeTab = "builder";
        },

        async duplicateTemplate(template) {
            try {
                await this.fetchPostApi('api.form-builder.templates.duplicate', {}, { routeParams: { id: template.id } });
                await this.loadTemplates();
            } catch (err) {
                this.error = 'Failed to duplicate template';
                console.error('Error duplicating template:', err);
            }
        },

        async deleteTemplate(template) {
            if (!confirm(`Are you sure you want to delete "${template.name}"?`)) return;

            try {
                await this.api.delete('api.form-builder.templates.destroy', { id: template.id });
                await this.loadTemplates();
            } catch (err) {
                this.error = 'Failed to delete template';
                console.error('Error deleting template:', err);
            }
        },

        async handleSave(data) {
            this.saving = true;
            this.error = null;

            const payload = {
                name: data.template.name,
                description: data.template.description,
                icon: data.template.icon,
                form_config: data.template.form_config || {},
                fields: data.fields,
            };

            try {
                if (this.isCreatingNew) {
                    await this.fetchPostApi('api.form-builder.templates.store', payload);
                } else if (this.selectedTemplate) {
                    await this.api.put('api.form-builder.templates.update', this.selectedTemplate.id, payload);
                }

                await this.loadTemplates();
                this.handleCancel();
            } catch (err) {
                this.error = err.response?.data?.message || 'Failed to save template';
                console.error('Error saving template:', err);
            } finally {
                this.saving = false;
            }
        },

        handleCancel() {
            this.selectedTemplate = null;
            this.isCreatingNew = false;
            this.isEditing = false;
            this.activeTab = "templates";
        },

        getFieldSchema() {
            if (!this.selectedTemplate?.field_definitions) return [];
            return this.selectedTemplate.field_definitions.map(field => ({
                field_key: field.field_key,
                field_type: field.field_type,
                label: field.label,
                placeholder: field.placeholder,
                description: field.description,
                validation_rules: field.validation_rules || {},
                options: Array.isArray(field.options) ? field.options : [],
                display_config: field.display_config && !Array.isArray(field.display_config) ? field.display_config : {},
                field_config: field.field_config && !Array.isArray(field.field_config) ? field.field_config : {},
            }));
        },
    },
};
</script>

<template>
    <AppLayout title="Form Builder">
        <template #header>
            <forms-header-actions />
        </template>

        <div class="default-container py-6">
            <!-- Error Alert -->
            <div v-if="error" class="mb-4 p-4 bg-red-100 dark:bg-red-900/30 border border-red-400 dark:border-red-700 text-red-700 dark:text-red-300 rounded-lg flex justify-between items-center">
                <span>{{ error }}</span>
                <button @click="error = null" class="text-red-700 dark:text-red-300 hover:text-red-900">×</button>
            </div>

            <tab-navigation
                v-model="activeTab"
                :tabs="[
                    { key: 'templates', label: 'Templates' },
                    { key: 'builder', label: builderTitle, disabled: !isCreatingNew && !selectedTemplate },
                ]"
            >
                <template #default="{ activeKey }">
                    <!-- Templates List Tab -->
                    <div v-if="activeKey === 'templates'" class="mt-6">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">Form Templates</h2>
                            <button
                                @click="startNewTemplate"
                                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center gap-2"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                Create New Template
                            </button>
                        </div>

                        <!-- Loading State -->
                        <div v-if="loadingTemplates" class="text-center py-12 text-gray-500 dark:text-gray-400">
                            Loading templates...
                        </div>

                        <!-- Templates Grid -->
                        <div v-else>
                            <!-- System Templates -->
                            <div v-if="systemTemplates.length" class="mb-8">
                                <h3 class="text-lg font-medium text-gray-700 dark:text-gray-300 mb-4">System Templates</h3>
                                <p class="my-3 text-sm text-gray-600">Note: Kindly duplicate system templates to attach them to your event form.</p>
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                    <div
                                        v-for="template in systemTemplates"
                                        :key="template.id"
                                        class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:shadow-md transition-shadow"
                                    >
                                        <div class="flex items-start justify-between">
                                            <div class="flex-1">
                                                <div class="flex items-center gap-2">
                                                    <h4 class="font-semibold text-gray-800 dark:text-gray-200">{{ template.name }}</h4>
                                                    <span class="px-2 py-0.5 text-xs bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300 rounded">System</span>
                                                </div>
                                                <span class="text-xs opacity-50">ID: {{ template.id }}</span>
                                                <p v-if="template.description" class="text-sm text-gray-600 dark:text-gray-400">{{ template.description }}</p>
                                                <p class="text-xs text-gray-500 dark:text-gray-500">{{ template.field_definitions_count || 0 }} fields</p>
                                            </div>
                                        </div>
                                        <div class="mt-4 flex gap-2">
                                            <button
                                                @click="selectTemplate(template)"
                                                class="px-3 py-1.5 text-sm bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded hover:bg-gray-200 dark:hover:bg-gray-600"
                                            >
                                                Edit
                                            </button>
                                            <button
                                                @click="duplicateTemplate(template)"
                                                class="px-3 py-1.5 text-sm bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 rounded hover:bg-green-200 dark:hover:bg-green-800/30"
                                            >
                                                Duplicate
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Custom Templates -->
                            <div>
                                <h3 class="text-lg font-medium text-gray-700 dark:text-gray-300 mb-4">Custom Templates</h3>
                                <div v-if="customTemplates.length" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                    <div
                                        v-for="template in customTemplates"
                                        :key="template.id"
                                        class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:shadow-md transition-shadow"
                                    >
                                        <div class="flex items-start justify-between">
                                            <div class="flex-1">
                                                <div class="flex items-center gap-2">
                                                    <h4 class="font-semibold text-gray-800 dark:text-gray-200">{{ template.name }}</h4>
                                                </div>
                                                <span class="text-xs opacity-50">ID: {{ template.id }}</span>
                                                <p v-if="template.description" class="text-sm text-gray-600 dark:text-gray-400">{{ template.description }}</p>
                                                <p class="text-xs text-gray-500 dark:text-gray-500">{{ template.field_definitions_count || 0 }} fields</p>
                                            </div>
                                        </div>
                                        <div class="mt-4 flex gap-2">
                                            <button
                                                @click="selectTemplate(template)"
                                                class="px-3 py-1.5 text-sm bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 rounded hover:bg-blue-200 dark:hover:bg-blue-800/30"
                                            >
                                                Edit
                                            </button>
                                            <button
                                                @click="duplicateTemplate(template)"
                                                class="px-3 py-1.5 text-sm bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 rounded hover:bg-green-200 dark:hover:bg-green-800/30"
                                            >
                                                Duplicate
                                            </button>
                                            <button
                                                @click="deleteTemplate(template)"
                                                class="px-3 py-1.5 text-sm bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300 rounded hover:bg-red-200 dark:hover:bg-red-800/30"
                                            >
                                                Delete
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div v-else class="text-center py-8 text-gray-500 dark:text-gray-400 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg">
                                    <p class="mb-2">No custom templates yet</p>
                                    <button @click="startNewTemplate" class="text-blue-600 dark:text-blue-400 hover:underline">
                                        Create your first template
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Builder Tab -->
                    <div v-else-if="activeKey === 'builder'" class="mt-6">
                        <DragDropFieldBuilder
                            :initial-schema="getFieldSchema()"
                            :template-data="{
                                name: selectedTemplate?.name || '',
                                description: selectedTemplate?.description || '',
                                icon: selectedTemplate?.icon || null,
                                form_config: selectedTemplate?.form_config || {},
                            }"
                            :is-system-template="false"
                            @save="handleSave"
                            @cancel="handleCancel"
                        />

                        <div v-if="saving" class="fixed inset-0 bg-black/30 flex items-center justify-center z-50">
                            <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-xl">
                                <p class="text-gray-700 dark:text-gray-300">Saving template...</p>
                            </div>
                        </div>
                    </div>
                </template>
            </tab-navigation>
        </div>
    </AppLayout>
</template>

<style scoped>
</style>
