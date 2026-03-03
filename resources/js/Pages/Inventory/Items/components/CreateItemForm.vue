<script>

import {defineComponent} from "vue";
import ApiMixin from "@/Modules/mixins/ApiMixin";
import Item from "@/Modules/domain/Item";
import ItemsHeaderActions from "@/Pages/Inventory/Items/components/ItemsHeaderActions.vue";

export default defineComponent({
    name: "CreateItemForm",
    components: { ItemsHeaderActions },
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
                    label: categories.label,
                }
            });
        },

    },
})
</script>

<template>
    <form v-if="!!form" @submit.prevent="submitCreate" class="py-12 max-w-xl mx-auto">
        <div class="flex flex-col gap-2 w-full mx-auto sm:p-2 lg:p-4 bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
            <div class="flex flex-col">
                <h2 class="font-bold uppercase leading-none py-2 mb-1 border-b">New Consumable Item Form</h2>
                <p>Fill out this form to document and track a new consumable item.</p>
            </div>
            <text-input required label="Name" v-model="form.name" :error="form.errors.name" />
            <text-input required label="Brand" v-model="form.brand" :error="form.errors.brand" />
            <div class="flex flex-row gap-2">
                <custom-dropdown
                    required
                    searchable
                    :with-all-option="false"
                    :value="form.supplier_id"
                    :options="suppliers"
                    placeholder="Select Supplier"
                    label="Supplier"
                    class="w-3/4"
                    :error="form.errors.supplier_id"
                    @selectedChange="form.supplier_id = $event"
                >
                    <template #icon>
                        <filter-icon class="h-4 w-4" />
                    </template>
                </custom-dropdown>
                <div class="flex items-end border-gray-700">
                    <Link :href="route('suppliers.create')" class="h-fit w-full py-2.5 border border-gray-700 flex items-center justify-center bg-white text-gray-600 rounded gap-1 text-sm px-2">
                        <add-icon class="h-5 w-5" />
                        <span class="whitespace-nowrap">New Supplier</span>
                    </Link>
                </div>
            </div>
            <custom-dropdown
                required
                searchable
                :with-all-option="false"
                :value="form.category_id"
                :options="categories"
                placeholder="Select Category"
                label="Category"
                :error="form.errors.category_id"
                @selectedChange="form.category_id = $event"
            >
                <template #icon>
                    <filter-icon class="h-4 w-4" />
                </template>
            </custom-dropdown>
            <text-input label="Description" v-model="form.description" :error="form.errors.description" />
            <text-area label="Specifications" v-model="form.specifications" :error="form.errors.specifications" />
            <file-input label="Image" v-model="form.image" file-type="image" :error="form.errors.image" />
            <div v-if="form.image" class="w-full  shadow bg-white focus:border-indigo-500 focus:ring-indigo-500 rounded-md border p-2 justify-center flex">
                <img :src="form.image" @click.right.prevent="null" draggable="false" class="max-w-80 w-1/2 bg-transparent" alt="image">
            </div>
            <div class="flex gap-1 justify-end">
                <submit-btn :disabled="model.api.processing">
                    <span v-if="model.api.processing">Saving</span>
                    <span v-else>Save</span>
                </submit-btn>
            </div>
        </div>
    </form>
</template>

<style scoped>

</style>
