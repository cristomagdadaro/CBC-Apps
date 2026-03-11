<script>

import {defineComponent} from "vue";
import ItemsHeaderActions from "@/Pages/Inventory/Items/components/ItemsHeaderActions.vue";
import ItemForm from "@/Pages/Inventory/Items/components/ItemForm.vue";

export default defineComponent({
    name: "CreateItem",
    components: { ItemsHeaderActions, ItemForm },
})
</script>

<template>
    <AppLayout title="Register a new Consumable Item">
        <template #header>
            <items-header-actions />
        </template>
        <item-form />
    </AppLayout>
</template>

<style scoped>

</style>
