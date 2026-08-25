<script>
import { defineComponent } from "vue";
import ApiMixin from "@/Modules/mixins/ApiMixin";
import Item from "@/Modules/domain/Item";
import ItemsHeaderActions from "@/Pages/Inventory/Items/components/ItemsHeaderActions.vue";
import { Plus, Filter, Image as ImageIcon, Box, Save, Loader2, Link2 } from "lucide-vue-next";

export default defineComponent({
    name: "ItemForm",
    mixins: [ApiMixin],
    components: {
        ItemsHeaderActions,
        Plus,
        Filter,
        ImageIcon,
        Box,
        Save,
        Loader2,
        Link2,
    },
    beforeMount() {
        this.model = new Item();
        this.setFormAction("create");
    },
    methods: {
        async handleFormSubmit() {
            const response = await this.submitCreate();
            if (response && response.data) {
                this.$emit("close"); // Close the side panel if successful
            }
        },
    },
});
</script>

<template>
    <form
        v-if="!!form"
        @submit.prevent="handleFormSubmit"
        class="flex flex-col w-full h-full max-h-full">
        <!-- Scrollable Content Area -->
        <div class="flex-1 overflow-y-auto custom-scrollbar p-5 space-y-6">
            <div class="space-y-4">
                <text-input
                    required
                    label="Name"
                    v-model="form.name"
                    :error="form.errors.name"
                    placeholder="e.g. Precision Balance"
                    class="w-full text-sm font-semibold" />

                <text-input
                    required
                    label="Brand"
                    v-model="form.brand"
                    :error="form.errors.brand"
                    placeholder="e.g. Ohaus"
                    class="w-full text-sm font-semibold" />

                <div class="flex flex-col gap-1">
                    <label class="text-[0.65rem] font-bold uppercase tracking-widest text-slate-500 dark:text-slate-400">Supplier *</label>
                    <div class="flex flex-col sm:flex-row gap-3">
                        <div class="flex-1">
                            <custom-dropdown
                                required
                                searchable
                                :with-all-option="false"
                                :value="form.supplier_id"
                                :options="suppliers"
                                placeholder="Select Supplier"
                                :error="form.errors.supplier_id"
                                @selectedChange="form.supplier_id = $event"
                                class="w-full">
                                <template #icon>
                                    <Filter class="h-4 w-4 text-slate-400" />
                                </template>
                            </custom-dropdown>
                        </div>
                        <div class="sm:w-36 shrink-0 mt-2 sm:mt-0">
                            <Link
                                :href="route('suppliers.create')"
                                target="_blank"
                                class="inline-flex items-center justify-center gap-1.5 w-full py-2.5 px-3 border border-slate-200 dark:border-slate-700 bg-slate-50 hover:bg-slate-100 dark:bg-slate-800 dark:hover:bg-slate-700 text-indigo-600 dark:text-indigo-400 rounded-xl text-xs font-bold transition-colors shadow-sm">
                                <Link2 class="w-3.5 h-3.5" />
                                <span>New Supplier</span>
                            </Link>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col gap-1">
                    <label class="text-[0.65rem] font-bold uppercase tracking-widest text-slate-500 dark:text-slate-400">Category *</label>
                    <custom-dropdown
                        required
                        searchable
                        :with-all-option="false"
                        :value="form.category_id"
                        :options="categories"
                        placeholder="Select Category"
                        :error="form.errors.category_id"
                        @selectedChange="form.category_id = $event">
                        <template #icon>
                            <Filter class="h-4 w-4 text-slate-400" />
                        </template>
                    </custom-dropdown>
                </div>

                <text-input
                    label="Model / Short Physical Description"
                    v-model="form.description"
                    :error="form.errors.description"
                    placeholder="e.g. AX224, analytical, 220g x 0.1mg"
                    class="w-full text-sm font-semibold" />

                <text-input
                    type-input="number"
                    label="Max Simultaneous Users"
                    v-model="form.simultaneous_users"
                    :error="form.errors.simultaneous_users"
                    min="1"
                    placeholder="Default is 1"
                    class="w-full text-sm font-semibold" />

                <div class="flex flex-col gap-1">
                    <text-area
                        label="Detailed Specifications"
                        v-model="form.specifications"
                        :error="form.errors.specifications"
                        placeholder="Enter detailed technical specs or requirements here..."
                        :rows="4"
                        class="w-full text-sm font-medium" />
                </div>

                <div class="flex flex-col gap-1 pb-4">
                    <label class="text-[0.65rem] font-bold uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-1">Item Image</label>
                    <file-input
                        v-model="form.image"
                        file-type="image"
                        :error="form.errors.image" />

                    <div
                        v-if="form.image"
                        class="mt-3 w-full bg-slate-50 dark:bg-slate-800/50 border border-dashed border-slate-300 dark:border-slate-700 rounded-xl p-3 flex justify-center items-center relative overflow-hidden">
                        <div
                            class="absolute inset-0 opacity-[0.03] dark:opacity-10 pointer-events-none"
                            style="background-image: radial-gradient(#000 1px, transparent 1px); background-size: 10px 10px"></div>
                        <img
                            :src="form.image"
                            @click.right.prevent="null"
                            draggable="false"
                            class="max-w-[12rem] max-h-48 object-contain rounded z-10 drop-shadow-md"
                            alt="Uploaded item image" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Sticky Footer Actions -->
        <div class="p-5 border-t border-slate-100 dark:border-slate-800 bg-slate-50/80 dark:bg-slate-900/80 backdrop-blur-md flex justify-end gap-3 sticky bottom-0 z-10 shrink-0 rounded-b-2xl">
            <button
                type="button"
                @click="$emit('close')"
                class="px-5 py-2.5 text-sm font-bold text-slate-600 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors active:scale-95 shadow-sm">
                Cancel
            </button>
            <button
                type="submit"
                :disabled="model.api.processing"
                class="inline-flex items-center justify-center gap-2 px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed text-white text-sm font-bold rounded-xl shadow-md shadow-indigo-600/20 transition-all active:scale-95">
                <Loader2
                    v-if="model.api.processing"
                    class="w-4 h-4 animate-spin" />
                <Save
                    v-else
                    class="w-4 h-4" />
                <span>{{ model.api.processing ? "Saving..." : "Save Item" }}</span>
            </button>
        </div>
    </form>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background-color: rgba(148, 163, 184, 0.4);
    border-radius: 9999px;
}
.dark .custom-scrollbar::-webkit-scrollbar-thumb {
    background-color: rgba(71, 85, 105, 0.4);
}
</style>
