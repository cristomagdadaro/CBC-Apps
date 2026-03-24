<template>
    <base-create-form :forceClose="forceClose">
        <template v-slot:formTitle>
            No Form Found
        </template>
        <template v-slot:formFields>
            Can't find the form or none was assigned.
        </template>
    </base-create-form>
</template>
<script>
import CancelButton from "@/Components/CRCMDatatable/Components/CancelButton.vue";
import CloseIcon from "@/Components/Icons/CloseIcon.vue";
import TextField from "@/Components/Form/TextField.vue";
import BaseCreateForm from "@/Components/Modal/BaseCreateForm.vue";

export default {
    components: {
        BaseCreateForm,
        CancelButton,
        CloseIcon,
        TextField,
    },
    props: {
        errors: {
            type: Object,
            default: () => ({})
        },
        forceClose: {
            type: Boolean,
            default: false
        },
        data: {
            type: Object,
            default: null
        }
    },
    data() {
        return {
            form: {
                name: null,
                phone: null,
                email: null,
                affiliation: null,
                address: null,
            },
        };
    },
    watch: {
        forceClose() {
            this.$emit('close');
        }
    },
    mounted() {
        if (this.data) {
            // make a copy of the data, so that we can reset the form later without affecting the original data
            this.form = Object.assign({}, this.data);
        }
    },
    methods: {
        submitForm() {
            this.$emit('submitForm', this.form);
        },
        close() {
            this.$emit('close');
        }
    }
};
</script>
