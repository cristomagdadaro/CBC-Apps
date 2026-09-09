<script>
import LocationMixin from "@/Modules/mixins/LocationMixin";
import FieldMixin from "@/Components/Forms/FieldMixin";
import CustomDropdown from "@/Components/CustomDropdown/CustomDropdown.vue";

export default {
    name: "SelectProvince",
    mixins: [LocationMixin, FieldMixin],
    components: { CustomDropdown },
    props: {
        region: {
            type: String,
            default: "",
        },
    },
    computed: {
        provinceOptions() {
            return this.locationProvinces.map((province) => ({ name: province, label: province }));
        },
    },
    watch: {
        region(newRegion, oldRegion) {
            if (newRegion === oldRegion) {
                return;
            }

            this.resetLocationProvinces();

            if (newRegion) {
                this.loadProvinces(newRegion);
            }
        },
    },
    methods: {
        selectOption(value) {
            this.$emit("update:modelValue", value);
        },
    },
    mounted() {
        if (this.region) {
            this.loadProvinces(this.region);
        }
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
        :disabled="disabled || locationLoading || !provinceOptions.length"
        :options="provinceOptions"
        :value="modelValue"
        :searchable="true"
        :with-all-option="false"
        @selectedChange="selectOption">
        <template #label-icon>
            <LuMap class="h-3.5 w-3.5 text-indigo-500 dark:text-indigo-400" />
        </template>
    </CustomDropdown>
</template>
