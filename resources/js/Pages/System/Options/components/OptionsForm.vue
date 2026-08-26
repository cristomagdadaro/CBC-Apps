<script>
import { router } from "@inertiajs/vue3";
import { defineAsyncComponent } from "vue";
import ApiMixin from "@/Modules/mixins/ApiMixin";
import Options from "@/Modules/domain/Options";
import { Save, X, Loader2, ListPlus, Settings2, AlertTriangle } from "lucide-vue-next";

export default {
    name: "OptionsForm",
    components: {
        Save,
        X,
        Loader2,
        ListPlus,
        Settings2,
        AlertTriangle,
    },
    mixins: [ApiMixin],
    props: {
        data: {
            type: Object,
            required: false,
            default: null,
        },
    },
    data() {
        return {
            isEdit: !!this.data,
        };
    },
    beforeMount() {
        this.model = new Options();
        if (this.data) {
            this.setFormAction("update");
            if (this.form.options && typeof this.form.options !== "string") {
                this.form.options = JSON.stringify(this.form.options, null, 2);
            }
        } else {
            this.setFormAction("create");
        }
    },
    computed: {
        InputTextOptions() {
            return defineAsyncComponent(() => import("./ValueInputs/TextInput.vue"));
        },
        SelectInputOptions() {
            return defineAsyncComponent(() => import("./ValueInputs/SelectInput.vue"));
        },
        NumberInputOptions() {
            return defineAsyncComponent(() => import("./ValueInputs/NumberInput.vue"));
        },
        TextareaInputOptions() {
            return defineAsyncComponent(() => import("./ValueInputs/TextareaInput.vue"));
        },
        BooleanInputOptions() {
            return defineAsyncComponent(() => import("./ValueInputs/BooleanInput.vue"));
        },
        JsonInputOptions() {
            return defineAsyncComponent(() => import("./ValueInputs/JsonInput.vue"));
        },
        SelectOptionsEditorOptions() {
            return defineAsyncComponent(() => import("./SelectOptionsEditor.vue"));
        },
        selectValueOptions() {
            return this.normalizeSelectOptions(this.form?.options);
        },
        selectOptionsEmpty() {
            return this.form?.type === "select" && this.selectValueOptions.length === 0;
        },
    },
    methods: {
        normalizeSelectOptions(rawOptions) {
            let parsedOptions = rawOptions;

            if (typeof parsedOptions === "string") {
                try {
                    parsedOptions = JSON.parse(parsedOptions);
                } catch (error) {
                    return [];
                }
            }

            if (!Array.isArray(parsedOptions)) {
                return [];
            }

            return parsedOptions
                .map((option, index) => {
                    const value = option?.value ?? option?.name ?? option?.key ?? "";
                    const label = option?.label ?? option?.name ?? option?.value ?? `Option ${index + 1}`;

                    return {
                        value: String(value),
                        label: String(label),
                    };
                })
                .filter((option) => option.value !== "" || option.label !== "");
        },
        normalizePayload() {
            const payload = { ...this.form.data() };

            if (payload.key) {
                payload.key = String(payload.key).toLowerCase().replace(/\s+/g, "_");
            }

            if (payload.type !== "select") {
                payload.options = null;
            } else if (typeof payload.options === "string") {
                payload.options = payload.options.trim() ? payload.options : null;
            }

            if (["boolean", "checkbox"].includes(payload.type)) {
                if (typeof payload.value === "boolean") {
                    payload.value = payload.value ? "true" : "false";
                }
            }

            if (payload.type === "number" && payload.value !== null && payload.value !== "") {
                payload.value = String(payload.value);
            }

            if (["json", "select"].includes(payload.type) && payload.value && typeof payload.value === "object") {
                payload.value = JSON.stringify(payload.value);
            }

            return payload;
        },
        getValueComponent() {
            const map = {
                text: { component: this.InputTextOptions, props: { type: "text" } },
                number: {
                    component: this.NumberInputOptions,
                    props: { type: "number" },
                },
                textarea: { component: this.TextareaInputOptions, props: {} },
                boolean: { component: this.BooleanInputOptions, props: {} },
                select: {
                    component: this.SelectInputOptions,
                    props: { options: this.selectValueOptions },
                },
                checkbox: { component: this.BooleanInputOptions, props: {} },
                json: { component: this.JsonInputOptions, props: {} },
            };

            return map[this.form.type] || map.text;
        },
        async submitProxy() {
            const normalizedPayload = this.normalizePayload();
            Object.keys(normalizedPayload).forEach((key) => {
                this.form[key] = normalizedPayload[key];
            });

            if (this.isEdit) {
                await this.submitUpdate();
            } else {
                await this.submitCreate();
            }
            router.visit(route("system.options.index"));
        },
    },
};
</script>

<template>
    <form
        @submit.prevent="submitProxy"
        class="space-y-6 rounded-2xl border border-slate-200/60 bg-white/80 p-6 shadow-sm backdrop-blur-xl md:p-8 dark:border-slate-800 dark:bg-slate-900/80">
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <!-- Key -->
            <text-input
                id="key"
                label="Key"
                v-model="form.key"
                :error="form.errors?.key"
                required
                guide="Unique identifier in snake_case format"
                placeholder="e.g., app_name (snake_case)"
                @input="form.key = form.key.toLowerCase().replace(/\s+/g, '_')" />

            <!-- Label -->
            <text-input
                id="label"
                label="Label"
                v-model="form.label"
                :error="form.errors?.label"
                required
                guide="Human-readable name for the option"
                placeholder="e.g., Application Name" />
        </div>

        <!-- Description -->
        <text-area
            id="description"
            label="Description"
            v-model="form.description"
            :error="form.errors?.description"
            guide="What is this option used for?"
            placeholder="Provide a brief description of the option's purpose" />

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <!-- Type -->
            <custom-dropdown
                id="type"
                label="Type"
                required
                :value="form.type"
                @selectedChange="form.type = $event"
                :error="form.errors?.type"
                :options="[
                    { name: 'text', label: 'Text' },
                    { name: 'number', label: 'Number' },
                    { name: 'textarea', label: 'Textarea' },
                    { name: 'boolean', label: 'Boolean' },
                    { name: 'select', label: 'Select (with choices)' },
                    { name: 'checkbox', label: 'Checkbox' },
                    { name: 'json', label: 'JSON' },
                ]"
                :withAllOption="false"
                placeholder="Select a type"
                guide="Determines the input type and validation for the option value" />

            <!-- Group -->
            <text-input
                id="group"
                label="Group"
                v-model="form.group"
                :error="form.errors?.group"
                guide="For organizing related options (e.g., system, forms, inventory)"
                placeholder="e.g., system, forms, inventory"
                datalist-id="group-list"
                :datalist-options="['system', 'email', 'inventory', 'forms', 'rental', 'requests', 'locations', 'reports']" />
        </div>

        <!-- Value Section -->
        <div class="rounded-xl border border-slate-200/60 bg-slate-50/50 p-5 shadow-sm dark:border-slate-700/60 dark:bg-slate-800/30">
            <label
                for="value"
                class="mb-3 block flex items-center gap-1.5 text-[0.65rem] font-semibold uppercase text-slate-500 dark:text-slate-400">
                <Settings2 class="h-3.5 w-3.5" />
                Value
                <span class="ml-0.5 text-rose-500">*</span>
            </label>

            <component
                :is="getValueComponent().component"
                v-model="form.value"
                v-bind="getValueComponent().props"
                :errors="form.errors" />

            <p
                v-if="form.errors?.value"
                class="mt-2 text-xs font-semibold text-rose-600 dark:text-rose-400">
                {{ form.errors.value }}
            </p>
            <div
                v-else-if="selectOptionsEmpty"
                class="mt-2.5 flex items-center gap-1.5 text-amber-600 dark:text-amber-400">
                <AlertTriangle class="h-3.5 w-3.5 shrink-0" />
                <p class="text-[0.65rem] font-bold uppercase tracking-widest">Add select choices below before choosing the default stored value.</p>
            </div>
        </div>

        <!-- Select Options Metadata -->
        <div
            v-if="form.type === 'select'"
            class="rounded-xl border border-indigo-100 bg-indigo-50/50 p-5 shadow-sm dark:border-indigo-500/20 dark:bg-indigo-500/5">
            <label
                for="options-metadata"
                class="mb-3 block flex items-center gap-1.5 text-[0.65rem] font-semibold uppercase text-indigo-600 dark:text-indigo-400">
                <ListPlus class="h-3.5 w-3.5" />
                Select Choices
            </label>

            <div
                id="options-metadata"
                class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-900/50">
                <component
                    :is="SelectOptionsEditorOptions"
                    v-model="form.options" />
            </div>

            <p
                v-if="form.errors?.options"
                class="mt-2 text-xs font-semibold text-rose-600 dark:text-rose-400">
                {{ form.errors.options }}
            </p>
            <p class="mt-2.5 text-xs font-medium leading-relaxed text-slate-500 dark:text-slate-400">Define the available stored values and labels for this select option. The Value field above uses these choices.</p>
        </div>

        <!-- Form Actions -->
        <div class="flex flex-col gap-3 border-t border-slate-100 pt-6 sm:flex-row dark:border-slate-800/60">
            <Link
                :href="route('system.options.index')"
                class="flex flex-1 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-5 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition-all hover:bg-slate-100 active:scale-95 dark:border-slate-700 dark:bg-slate-800/50 dark:text-slate-300 dark:hover:bg-slate-800">
                <X class="h-4 w-4" />
                Cancel
            </Link>
            <button
                type="submit"
                :disabled="processing"
                class="flex flex-[2] items-center justify-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition-all hover:bg-indigo-700 active:scale-95 disabled:pointer-events-none disabled:opacity-70">
                <Loader2
                    v-if="processing"
                    class="h-4 w-4 animate-spin" />
                <Save
                    v-else
                    class="h-4 w-4" />
                {{ processing ? (isEdit ? "Saving..." : "Creating...") : isEdit ? "Save Changes" : "Create Option" }}
            </button>
        </div>
    </form>
</template>
