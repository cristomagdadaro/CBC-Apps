import Field from "@/Components/Forms/Field.vue";

export default {
    components: {
        Field,
    },
    props: {
        modelValue: { type: [String, Number], default: "" },
        placeholder: { type: String, default: "" },
        error: { type: String, default: "" },
        classes: { type: [String, Array, Object], default: "" },
        id: { type: String, default: "" },
        label: { type: String, default: "" },
        required: { type: Boolean, default: false },
        disabled: { type: Boolean, default: false },
        clearable: { type: Boolean, default: false },
        hint: { type: String, default: null },
        guide: { type: String, default: null },
        datalistId: { type: String, default: null },
        datalistOptions: { type: Array, default: null },
    },
    emits: ["update:modelValue", "clear"],
    data() {
        return {
            isFocused: false,
        };
    },
    mounted() {
        const input = this.$refs.input;
        if (input && input.hasAttribute && input.hasAttribute("autofocus")) {
            input.focus();
        }
    },
    computed: {
        hasValue() {
            return String(this.modelValue || "").length > 0;
        },
    },
    methods: {
        focus() {
            this.$refs.input?.focus();
        },
        onFocus() {
            this.isFocused = true;
        },
        onBlur() {
            this.isFocused = false;
        },
        onInput(e) {
            this.$emit("update:modelValue", e.target.value);
        },
        onClear() {
            this.$emit("update:modelValue", "");
            this.$emit("clear");
            this.focus();
        },
    },
};
