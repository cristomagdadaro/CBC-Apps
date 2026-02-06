<script>

import {defineComponent} from "vue";
import ApiMixin from "@/Modules/mixins/ApiMixin.js";
import Item from "@/Modules/domain/Item";
import ItemsHeaderActions from "@/Pages/Inventory/Items/components/ItemsHeaderActions.vue";
import CreateItemForm from "@/Pages/Inventory/Items/components/CreateItemForm.vue";

export default defineComponent({
    components: { ItemsHeaderActions, CreateItemForm },
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
            return this.$page.props.categories.map(category => {
                return {
                    name: category.id,
                    label: category.name,
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
