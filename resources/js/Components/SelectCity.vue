<script>
import LocationMixin from "@/Modules/mixins/LocationMixin";
import FieldMixin from "@/Components/Forms/FieldMixin";
import CustomDropdown from "@/Components/CustomDropdown/CustomDropdown.vue";

export default {
    name: "SelectCity",
    mixins: [LocationMixin, FieldMixin],
    components: { CustomDropdown },
    props: {
        region: {
            type: String,
            default: "",
        },
        province: {
            type: String,
            default: "",
        },
    },
    computed: {
        cityOptions() {
            return this.locationCities.map((city) => ({
                name: city.city ?? city,
                label: city.city ?? city,
            }));
        },
    },
    watch: {
        province(newProvince, oldProvince) {
            if (newProvince === oldProvince) {
                return;
            }

            this.resetLocationCities();

            if (newProvince) {
                this.loadCities(newProvince, this.region);
            }
        },
    },
    methods: {
        selectOption(value) {
            this.$emit("update:modelValue", value);
        },
    },
    mounted() {
        if (this.province) {
            this.loadCities(this.province, this.region);
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
        :disabled="disabled || locationLoading || !cityOptions.length"
        :options="cityOptions"
        :value="modelValue"
        :searchable="true"
        :with-all-option="false"
        @selectedChange="selectOption">
        <template #label-icon>
            <LuMapPin class="h-3.5 w-3.5 text-indigo-500 dark:text-indigo-400" />
        </template>
    </CustomDropdown>
</template>
