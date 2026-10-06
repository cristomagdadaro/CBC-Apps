<script>
import { defineComponent } from "vue";
import ApiMixin from "@/Modules/mixins/ApiMixin";
import Item from "@/Modules/domain/Item";
import ItemsHeaderActions from "@/Pages/Inventory/Items/components/ItemsHeaderActions.vue";
import DialogModal from "@/Components/DialogModal.vue";
import { Plus, Filter, Image as ImageIcon, Box, Save, Loader2, Link2, X } from "lucide-vue-next";
import axios from "axios";

export default defineComponent({
    name: "ItemForm",
    mixins: [ApiMixin],
    components: {
        ItemsHeaderActions,
        DialogModal,
        Plus,
        Filter,
        ImageIcon,
        Box,
        Save,
        Loader2,
        Link2,
        X,
    },
    props: {
        extractedData: {
            type: Object,
            default: null,
        },
    },
    data() {
        return {
            showNewSupplierForm: false,
            newSupplier: {
                name: "",
                email: "",
                phone: "",
                address: "",
                description: "",
            },
            isCreatingSupplier: false,
            showErrorModal: false,
            errorMessage: "",
        };
    },
    watch: {
        extractedData: {
            handler(val) {
                this.applyExtractedData(val);
            },
            deep: true,
        },
    },
    beforeMount() {
        this.model = new Item();
        this.setFormAction("create");
    },
    mounted() {
        if (this.extractedData) {
            this.applyExtractedData(this.extractedData);
        }
    },
    methods: {
        applyExtractedData(val) {
            if (!val || !this.form) return;
            
            if (val.items && val.items.length > 0) {
                const item = val.items[0];
                this.form.name = item.name || this.form.name;
                this.form.brand = item.brand || this.form.brand;
                this.form.description = item.description || this.form.description;
                this.form.specifications = item.specifications || this.form.specifications;
                if (item.category_id) {
                    this.form.category_id = item.category_id;
                }
            }
            if (val.supplier && val.supplier.name) {
                // Check if supplier already exists in dropdown options
                // The options array from ApiMixin has {name: id, label: 'Company Name'}
                const aiSupplierName = val.supplier.name.toLowerCase().trim();
                const existingSupplier = this.suppliers.find(s => 
                    s.label && s.label.toLowerCase().trim() === aiSupplierName
                );
                
                if (existingSupplier) {
                    this.form.supplier_id = existingSupplier.name;
                    this.showNewSupplierForm = false;
                } else {
                    this.showNewSupplierForm = true;
                    this.newSupplier.name = val.supplier.name || "";
                    this.newSupplier.email = val.supplier.email || "";
                    this.newSupplier.phone = val.supplier.phone || "";
                    this.newSupplier.address = val.supplier.address || "";
                }
            }
        },
        async handleFormSubmit() {
            if (this.showNewSupplierForm) {
                this.isCreatingSupplier = true;
                try {
                    const response = await axios.post('/api/inventory/suppliers', this.newSupplier);
                    if (response.data && response.data.data && response.data.data.id) {
                        this.form.supplier_id = response.data.data.id;
                    }
                } catch (error) {
                    console.error("Failed to create supplier", error);
                    let errMsg = "Failed to create supplier. Please check the fields.";
                    if (error.response?.data?.errors) {
                        errMsg = Object.values(error.response.data.errors).flat().join("\n");
                    } else if (error.response?.data?.message) {
                        errMsg = error.response.data.message;
                    }
                    this.errorMessage = errMsg;
                    this.showErrorModal = true;
                    this.isCreatingSupplier = false;
                    return;
                }
                this.isCreatingSupplier = false;
            }

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
        class="flex h-full max-h-full w-full flex-col">
        <!-- Scrollable Content Area -->
        <div class="custom-scrollbar flex-1 space-y-6 overflow-y-auto p-5">
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
                    <label class="text-[0.65rem] font-bold uppercase text-slate-500 dark:text-slate-400">Supplier *</label>
                    <div v-if="!showNewSupplierForm" class="flex flex-col gap-3 sm:flex-row">
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
                        <div class="mt-2 shrink-0 sm:mt-0 sm:w-36">
                            <button
                                type="button"
                                @click="showNewSupplierForm = true"
                                class="inline-flex w-full items-center justify-center gap-1.5 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs font-bold text-indigo-600 shadow-sm transition-colors hover:bg-slate-100 dark:border-slate-700 dark:bg-slate-800 dark:text-indigo-400 dark:hover:bg-slate-700">
                                <Plus class="h-3.5 w-3.5" />
                                <span>New Supplier</span>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Inline Supplier Form -->
                    <div v-else class="rounded-xl border border-indigo-100 bg-indigo-50/50 p-4 dark:border-indigo-500/20 dark:bg-indigo-500/5">
                        <div class="mb-4 flex items-center justify-between border-b border-indigo-100 pb-3 dark:border-indigo-500/20">
                            <h4 class="text-xs font-bold uppercase text-indigo-700 dark:text-indigo-400">New Supplier Details</h4>
                            <button type="button" @click="showNewSupplierForm = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300">
                                <X class="h-4 w-4" />
                            </button>
                        </div>
                        <div class="flex flex-col gap-3">
                            <text-input required label="Company Name" v-model="newSupplier.name" placeholder="e.g. Acme Corp" class="w-full text-sm" />
                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                <text-input label="Email" type="email" v-model="newSupplier.email" placeholder="contact@acme.com" class="w-full text-sm" />
                                <text-input label="Phone" v-model="newSupplier.phone" placeholder="123-456-7890" class="w-full text-sm" />
                            </div>
                            <text-input label="Address" v-model="newSupplier.address" placeholder="123 Main St..." class="w-full text-sm" />
                        </div>
                    </div>
                </div>

                <div class="flex flex-col gap-1">
                    <custom-dropdown
                        required
                        searchable
                        label="Category"
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
                    <label class="mb-1 text-[0.65rem] font-bold uppercase text-slate-500 dark:text-slate-400">Item Image</label>
                    <file-input
                        v-model="form.image"
                        file-type="image"
                        :error="form.errors.image" />

                    <div
                        v-if="form.image"
                        class="relative mt-3 flex w-full items-center justify-center overflow-hidden rounded-xl border border-dashed border-slate-300 bg-slate-50 p-3 dark:border-slate-700 dark:bg-slate-800/50">
                        <div
                            class="pointer-events-none absolute inset-0 opacity-[0.03] dark:opacity-10"
                            style="background-image: radial-gradient(#000 1px, transparent 1px); background-size: 10px 10px"></div>
                        <img
                            :src="form.image"
                            @click.right.prevent="null"
                            draggable="false"
                            class="z-10 max-h-48 max-w-[12rem] rounded object-contain drop-shadow-md"
                            alt="Uploaded item image" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Sticky Footer Actions -->
        <div class="sticky bottom-0 z-10 flex shrink-0 justify-end gap-3 rounded-b-2xl border-t border-slate-100 bg-slate-50/80 p-5 backdrop-blur-md dark:border-slate-800 dark:bg-slate-900/80">
            <button
                type="button"
                @click="$emit('close')"
                class="rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-bold text-slate-600 shadow-sm transition-colors hover:bg-slate-50 active:scale-95 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700">
                Cancel
            </button>
            <button
                type="submit"
                :disabled="model.api.processing || isCreatingSupplier"
                class="inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-6 py-2.5 text-sm font-bold text-white shadow-md shadow-indigo-600/20 transition-all hover:bg-indigo-700 active:scale-95 disabled:cursor-not-allowed disabled:opacity-50">
                <Loader2
                    v-if="model.api.processing || isCreatingSupplier"
                    class="h-4 w-4 animate-spin" />
                <Save
                    v-else
                    class="h-4 w-4" />
                <span>{{ (model.api.processing || isCreatingSupplier) ? "Saving..." : "Save Item" }}</span>
            </button>
        </div>

        <dialog-modal :show="showErrorModal" @close="showErrorModal = false">
            <template #title>
                Supplier Creation Failed
            </template>
            <template #content>
                <div class="whitespace-pre-wrap text-sm text-red-600 dark:text-red-400">
                    {{ errorMessage }}
                </div>
            </template>
            <template #footer>
                <button
                    type="button"
                    @click="showErrorModal = false"
                    class="rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-bold text-slate-600 shadow-sm transition-colors hover:bg-slate-50 active:scale-95 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700">
                    Close
                </button>
            </template>
        </dialog-modal>
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
