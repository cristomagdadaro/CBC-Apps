<script>

import {defineComponent} from "vue";
import {Head, Link} from "@inertiajs/vue3";
import CustomDropdown from "@/Components/CustomDropdown/CustomDropdown.vue";
import LoaderIcon from "@/Components/Icons/LoaderIcon.vue";
import ApiMixin from "@/Modules/mixins/ApiMixin.js";
import Item from "@/Modules/domain/Item";
import SubmitBtn from "@/Components/Buttons/SubmitBtn.vue";
import ResetBtn from "@/Components/Buttons/ResetBtn.vue";
import TextInput from "@/Components/TextInput.vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import FilterIcon from "@/Components/Icons/FilterIcon.vue";
import AddIcon from "@/Components/Icons/AddIcon.vue";
import ItemsHeaderActions from "@/Pages/Inventory/Items/components/ItemsHeaderActions.vue";
import FileInput from "@/Components/FileInput.vue";
import CreateItemForm from "@/Pages/Inventory/Items/components/CreateItemForm.vue";

export default defineComponent({
    components: {
        FileInput,
        ItemsHeaderActions,
        AddIcon,
        FilterIcon,
        AppLayout,
        TextInput,
        ResetBtn, SubmitBtn,
        Link,
        LoaderIcon,
        CreateItemForm,
        CustomDropdown, Head},
    mixins: [ApiMixin],
    beforeMount() {
        this.model = new Item();
        this.setFormAction('create');
    },
    computed: {
        suppliers() {
            return this.$page.props.suppliers.map(supplier => {
                return {
                    name: supplier.id,
                    label: supplier.name,
                }
            });
        },
        categories() {
            return this.$page.props.categories.map(categories => {
                return {
                    name: categories.id,
                    label: categories.name,
                }
            });
        },

    },
})
</script>

<template>
    <AppLayout title="Register a new Consumable Item">
        <template #header>
            <items-header-actions />
        </template>

        <create-item-form />
    </AppLayout>
</template>

<style scoped>

</style>
