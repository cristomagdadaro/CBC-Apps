<script>
import {defineComponent} from "vue";
import ItemsHeaderActions from "@/Pages/Inventory/Items/components/ItemsHeaderActions.vue";
import ItemForm from "./ItemForm.vue";

export default defineComponent({
    name: "CreateItemForm",
    components: { ItemsHeaderActions, ItemForm },
})
</script>

<template>
    <AppLayout title="Create Item">
        <template #header>
            <items-header-actions />
        </template>
        <item-form />
    </AppLayout>
</template>

<style scoped>

</style>
