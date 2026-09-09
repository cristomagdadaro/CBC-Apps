<script>
import LocationMixin from "@/Modules/mixins/LocationMixin";
import FieldMixin from "@/Components/Forms/FieldMixin";
import CustomDropdown from "@/Components/CustomDropdown/CustomDropdown.vue";

export default {
    name: "SelectRegion",
    mixins: [LocationMixin, FieldMixin],
    components: { CustomDropdown },
    computed: {
        regionOptions() {
            return this.locationRegions.map((region) => ({ name: region, label: region }));
        },
    },
    methods: {
        selectOption(value) {
            this.$emit("update:modelValue", value);
        },
    },
    mounted() {
        this.loadRegions();
    },
};
</script>

<template>
    <CustomDropdown
        :id="id"
        :label="label"
        :placeholder="placeholder"
        :error="error"
        :required="required"
        :hint="hint"
        :guide="guide"
        :disabled="disabled || locationLoading"
        :options="regionOptions"
        :value="modelValue"
        :searchable="true"
        :with-all-option="false"
        @selectedChange="selectOption">
        <template #label-icon>
            <LuMap class="h-3.5 w-3.5 text-indigo-500 dark:text-indigo-400" />
        </template>
    </CustomDropdown>
</template>
