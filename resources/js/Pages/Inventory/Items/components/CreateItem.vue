<script>

import {defineComponent} from "vue";
import {Head, Link} from "@inertiajs/vue3";
import LoaderIcon from "@/Components/Icons/LoaderIcon.vue";
import ApiMixin from "@/Modules/mixins/ApiMixin.js";
import Item from "@/Modules/domain/Item";
import FilterIcon from "@/Components/Icons/FilterIcon.vue";
import AddIcon from "@/Components/Icons/AddIcon.vue";
import ItemsHeaderActions from "@/Pages/Inventory/Items/components/ItemsHeaderActions.vue";
import CreateItemForm from "@/Pages/Inventory/Items/components/CreateItemForm.vue";

export default defineComponent({
    components: {
        ItemsHeaderActions,
        AddIcon,
        FilterIcon,
        Link,
        LoaderIcon,
        CreateItemForm,
        Head},
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
