<script>
import { router } from "@inertiajs/vue3";
import { defineAsyncComponent } from "vue";
import ApiMixin from "@/Modules/mixins/ApiMixin";
import Options from "@/Modules/domain/Options";

export default {
  name: "OptionsForm",
  mixins: [ApiMixin],
  props: {
    data: {
      type: Object,
      required: false,
      default: null
    }
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
      if (this.form.options && typeof this.form.options !== 'string') {
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
    NumberInputOptions() {
      return defineAsyncComponent(() =>
        import("./ValueInputs/NumberInput.vue")
      );
    },
    TextareaInputOptions() {
      return defineAsyncComponent(() =>
        import("./ValueInputs/TextareaInput.vue")
      );
    },
    BooleanInputOptions() {
      return defineAsyncComponent(() =>
        import("./ValueInputs/BooleanInput.vue")
      );
    },
    JsonInputOptions() {
      return defineAsyncComponent(() => import("./ValueInputs/JsonInput.vue"));
    },
    SelectOptionsEditorOptions() {
      return defineAsyncComponent(() => import("./SelectOptionsEditor.vue"));
    },
  },
  methods: {
    normalizePayload() {
      const payload = { ...this.form.data() };

      if (payload.key) {
        payload.key = String(payload.key).toLowerCase().replace(/\s+/g, '_');
      }

      if (payload.type !== 'select') {
        payload.options = null;
      } else if (typeof payload.options === 'string') {
        payload.options = payload.options.trim() ? payload.options : null;
      }

      if (['boolean', 'checkbox'].includes(payload.type)) {
        if (typeof payload.value === 'boolean') {
          payload.value = payload.value ? 'true' : 'false';
        }
      }

      if (payload.type === 'number' && payload.value !== null && payload.value !== '') {
        payload.value = String(payload.value);
      }

      if (['json', 'select'].includes(payload.type) && payload.value && typeof payload.value === 'object') {
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
        select: { component: this.SelectOptionsEditorOptions, props: {} },
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
    class="space-y-6 rounded-lg p-6 border border-gray-500 bg-white"
  >
    <!-- Key -->
    <text-input
      id="key"
      label="Key"
      v-model="form.key"
      :error="form.errors?.key"
      required
      guide="Unique identifier in snake_case format"
      placeholder="e.g., app_name (snake_case)"
      @input="form.key = form.key.toLowerCase().replace(/\s+/g, '_')"
    />

    <text-input
      id="label"
      label="Label"
      v-model="form.label"
      :error="form.errors?.label"
      required
      guide="Human-readable name for the option"
      placeholder="e.g., Application Name"
    />

    <!-- Description -->
    <text-area
      id="description"
      label="Description"
      v-model="form.description"
      :error="form.errors?.description"
      guide="What is this option used for?"
      placeholder="Provide a brief description of the option's purpose"
    />

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
      guide="Determines the input type and validation for the option value"
    />

    <!-- Group -->
    <text-input
      id="group"
      label="Group"
      v-model="form.group"
      :error="form.errors?.group"
      guide="For organizing related options (e.g., system, forms, inventory)"
      placeholder="e.g., system, forms, inventory"
      datalist-id="group-list"
      :datalist-options="[
        'system',
        'email',
        'inventory',
        'forms',
        'rental',
        'requests',
        'locations',
        'reports',
      ]"
    />

    <!-- Value -->
    <div>
      <label for="value" class="block text-sm font-medium text-gray-900">
        Value <span class="text-red-500">*</span>
      </label>
      <component
        :is="getValueComponent().component"
        v-model="form.value"
        v-bind="getValueComponent().props"
        :errors="form.errors"
      />
      <p v-if="form.errors.value" class="mt-1 text-sm text-red-600">
        {{ form.errors.value }}
      </p>
    </div>

    <!-- Options (for select type) -->
    <!-- Removed duplicate SelectOptionsEditor -->

    <!-- JSON Options (for select type metadata) -->
    <div v-if="form.type === 'select'">
      <label for="options-metadata" class="block text-sm font-medium text-gray-900">
        Options Metadata (JSON)
      </label>
      <textarea
        id="options-metadata"
        v-model="form.options"
        rows="5"
        placeholder='[{"name": "option1", "label": "Option 1"}, {"name": "option2", "label": "Option 2"}]'
        class="mt-2 block w-full font-mono text-sm rounded-lg border border-gray-300 px-4 py-2 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
      ></textarea>
      <p v-if="form.errors.options" class="mt-1 text-sm text-red-600">
        {{ form.errors.options }}
      </p>
      <p class="mt-1 text-xs text-gray-500">JSON array of options for select/dropdown</p>
    </div>

    <!-- Form Actions -->
    <div class="flex gap-3 pt-6">
      <Link
        :href="route('system.options.index')"
        class="flex-1 rounded-lg border border-gray-300 px-4 py-2 text-center text-sm font-medium text-gray-900 hover:bg-gray-50"
      >
        Cancel
      </Link>
      <button
        type="submit"
        :disabled="processing"
        class="flex-1 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50"
      >
        {{ processing ? (isEdit ? "Saving..." : "Creating...") : (isEdit ? "Save Changes" : "Create Option") }}
      </button>
    </div>
  </form>
</template>
