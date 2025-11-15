<script>
import CustomDropdown from "@/Components/CustomDropdown/CustomDropdown.vue";
import FilterIcon from "@/Components/Icons/FilterIcon.vue";
import TextInput from "@/Components/TextInput.vue";
import Transaction from "@/Pages/Inventory/Scan/components/model/Transaction";
import ApiMixin from "@/Modules/mixins/ApiMixin";
import SubmitBtn from "@/Components/Buttons/SubmitBtn.vue";
import CancelBtn from "@/Components/Buttons/CancelBtn.vue";
import TextArea from "@/Components/TextArea.vue";

export default {
    name: "OutgoingForm",
    components: {TextArea, CancelBtn, SubmitBtn, TextInput, FilterIcon, CustomDropdown},
    props: {
        personnels: Object,
    },
    mixins: [ApiMixin],
    data() {
        return {
            employee_id: '',
        }
    },
    beforeMount() {
        this.model = new Transaction();
        this.setFormAction('create');
    },
    computed: {
        isAuthenticated() {
            return (this.$page.props.auth && this.$page.props.auth.user);
        },
        isPublic() {
            return !this.isAuthenticated;
        },
    },
    methods: {
        formatNumber(value){
            return value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        },
        proxySubmit() {
            this.form.quantity = this.form.quantity * -1;

            if (this.isPublic) {
                // Public flow: user is not logged in.
                // We treat employee_id as both the identifier for the user
                // and implicitly for "personnel", so we don't require personnel_id here.
                this.form.employee_id = this.employee_id;
                this.form.user_id = null;
                // Ensure personnel_id is not accidentally validated in this context
                this.form.personnel_id = null;
            }

            this.submitCreate();
        }
    },
    mounted() {
        this.form.barcode = this.data.barcode;
        this.form.name = this.data.name;
        this.form.brand = this.data.brand;
        this.form.unit = this.data.unit;
        this.form.item_id = this.data.item_id;

        // For logged-in users, set user_id so we know who inserted the record
        if (this.isAuthenticated) {
            this.form.user_id = this.$page.props.auth.user.id;
        }

        this.form.transac_type = 'outgoing';
    },
    watch: {
        'form.quantity': {
            handler(newVal) {
                if(newVal > Number(this.data.remaining_quantity))
                    this.form['errors'].quantity = 'Exceeds maximum quantity';
                else
                    this.form['errors'].quantity = null;
            }
        }
    }
}
</script>

<template>
    <div class="flex flex-col sm:p-5 p-2 sm:gap-3 gap-1 bg-white shadow rounded">
        <div class="flex select-none justify-between items-center gap-5 py-2 px-4 border-b">
            <div class="flex flex-col leading-none">
                <span class="font-bold text-lg whitespace-nowrap overflow-ellipsis overflow-hidden">
                    {{ data.name }} ({{ data.unit }})
                </span>
                <span class="text-sm text-gray-500">{{ data.brand }}</span>
            </div>
            <div class="flex sm:gap-4 gap-1">
                <div class="flex flex-col">
                    <span class="text-center text-gray-600">
                        {{ formatNumber(data.remaining_quantity) }}
                    </span>
                    <span class="text-xs text-gray-400">
                        Remaining
                    </span>
                </div>
                <div class="flex flex-col">
                    <span class="text-center text-gray-600">
                        {{ formatNumber(data.total_outgoing) }}
                    </span>
                    <span class="text-xs text-gray-400">
                        Consumed
                    </span>
                </div>
                <div class="flex flex-col">
                    <span class="text-center text-gray-600">
                        {{ (100-((data.remaining_quantity/data.total_ingoing) *100)).toFixed(2)  }}%
                    </span>
                    <span class="text-xs text-gray-400">
                        Utilization
                    </span>
                </div>
            </div>
        </div>
        <div class="flex flex-col sm:gap-3 gap-1 border sm:p-3 p-1 rounded bg-gray-100">
            <h1 class="text-lg font-semibold text-gray-800">Outgoing Transaction</h1>
            <form @submit.prevent="proxySubmit" class="flex flex-col gap-3">
                <!-- Public access: ask for Employee ID only -->
                <text-input
                    v-if="isPublic"
                    required
                    label="Employee ID"
                    name="employee_id"
                    id="employee_id"
                    v-model="employee_id"
                    :error="form.errors.employee_id"
                />

                <!-- Logged-in users: allow choosing Personnel (consumer) -->
                <custom-dropdown
                    v-if="isAuthenticated"
                    required
                    searchable
                    :with-all-option="false"
                    :value="form.personnel_id"
                    :options="personnels"
                    placeholder="Select Personnel"
                    label="Personnel"
                    :error="form.errors.personnel_id"
                    @selectedChange="form.personnel_id = $event"
                    class="w-full"
                >
                    <template #icon>
                        <filter-icon class="h-4 w-4" />
                    </template>
                </custom-dropdown>

                <text-input
                    required
                    type-input="number"
                    autocomplete="off"
                    label="Quantity"
                    name="quantity"
                    id="quantity"
                    v-model="form.quantity"
                    :error="form.errors.quantity"
                />

                <text-area
                    required
                    type-input="number"
                    autocomplete="off"
                    label="Purpose"
                    name="purpose"
                    id="purpose"
                    v-model="form.remarks"
                    :error="form.errors.remarks"
                />

                <div class="flex gap-1 justify-between">
                    <cancel-btn @click="resetForm">
                        Reset
                    </cancel-btn>
                    <submit-btn :disabled="model.api.processing">
                        <span v-if="model.api.processing">Saving</span>
                        <span v-else>Save</span>
                    </submit-btn>
                </div>
            </form>
        </div>
    </div>
</template>

<style scoped>

</style>
