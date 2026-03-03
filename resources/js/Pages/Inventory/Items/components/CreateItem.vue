<script>

import {defineComponent} from "vue";
import ItemsHeaderActions from "@/Pages/Inventory/Items/components/ItemsHeaderActions.vue";
import CreateItemForm from "@/Pages/Inventory/Items/components/CreateItemForm.vue";

export default defineComponent({
    name: "CreateItem",
    components: { ItemsHeaderActions, CreateItemForm },
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
