<script>
import CustomDropdown from "@/Components/CustomDropdown/CustomDropdown.vue";
import FilterIcon from "@/Components/Icons/FilterIcon.vue";
import TextInput from "@/Components/TextInput.vue";
import Transaction from "@/Modules/domain/Transaction";
import ApiMixin from "@/Modules/mixins/ApiMixin";
import SubmitBtn from "@/Components/Buttons/SubmitBtn.vue";
import CancelBtn from "@/Components/Buttons/CancelBtn.vue";
import TextArea from "@/Components/TextArea.vue";
import DtoResponse from "@/Modules/dto/DtoResponse";
import DtoError from "@/Modules/dto/DtoError.js";

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
            recentTransactions: [],
            processing: false,
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
        formatDate(value) {
            if (!value) return 'N/A';
            const normalized = typeof value === 'string' && !value.includes('T')
                ? value.replace(' ', 'T')
                : value;
            const date = new Date(normalized);
            if (Number.isNaN(date.getTime())) {
                return value;
            }
            return date.toLocaleString();
        },
        async proxySubmit() {
            if (this.isPublic) {
                //this.form.employee_id = this.employee_id;
                this.form.user_id = null;
                this.form.personnel_id = null;
            }

            const temp = await this.submitCreate();
            if (temp instanceof DtoResponse)
                this.$emit('submitted');
            if (temp instanceof DtoError)
                this.$emit('error');
        },
        getRecentTransactions() {
            this.processing = true;
            this.fetchGetApi('api.inventory.transactions.index.public', {'sort': 'created_at', 'order': 'desc', 'filter': 'id', 'filter_by_parent_column': 'item_id', 'filter_by_parent_id': this.data.item_id, 'per_page': 5})
                .then((response) => {
                    this.recentTransactions = response.data;
                });
            this.processing = false;
        },
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

        this.getRecentTransactions();
    },
    watch: {
        'form.quantity': {
            handler(newVal) {
                if(newVal > Number(this.data?.remaining_quantity))
                    this.form['errors'].quantity = 'Exceeds maximum quantity';
                else
                    this.form['errors'].quantity = null;
            }
        }
    }
}
</script>

<template>
    <div class="grid sm:grid-cols-3 grid-cols-1 sm:p-5 p-3 sm:gap-3">
        <div class="flex flex-col col-span-2 gap-1 bg-white rounded">
            <div  v-if="data" class="flex flex-col select-none justify-between items-center gap-5 py-2 px-1 md:px-4 border-b">
                <div class="flex flex-col leading-none w-full">
                    <span class="font-bold text-base md:text-lg whitespace-nowrap overflow-ellipsis overflow-hidden">
                        {{ data.name }} {{ data.description ? `(${data.description})` : '' }}
                    </span>
                    <span class="text-sm text-gray-500">{{ data.brand }}</span>
                    <span class="text-xs text-gray-500 leading-none" :class="{'text-red-600' : !data.barcode}">{{ data.barcode || 'Warning! NO BARCODE' }}</span>
                </div>
                <div class="flex sm:gap-4 gap-1 w-full justify-evenly">
                    <div class="flex flex-col leading-none md:leading-relaxed">
                        <span class="text-center text-gray-600">
                            {{ formatNumber(data.remaining_quantity) }}
                        </span>
                        <span class="text-xs text-gray-400">
                            Remaining
                        </span>
                    </div>
                    <div class="flex flex-col leading-none md:leading-relaxed">
                        <span class="text-center text-gray-600">
                            {{ formatNumber(data.total_outgoing) }}
                        </span>
                        <span class="text-xs text-gray-400">
                            Consumed
                        </span>
                    </div>
                    <div class="flex flex-col leading-none md:leading-relaxed">
                        <span class="text-center text-gray-600">
                            {{ (100-((data.remaining_quantity/data.total_ingoing) *100)).toFixed(2)  }}%
                        </span>
                        <span class="text-xs text-gray-400">
                            Utilization
                        </span>
                    </div>
                </div>
            </div>
            <div class="flex flex-col sm:gap-3 gap-1 sm:p-3 p-1 rounded">
                <h1 class="text-base md:text-lg font-semibold text-gray-800">Outgoing Transaction</h1>
                <form @submit.prevent="proxySubmit" class="flex flex-col gap-3">
                    <!-- Public access: ask for Employee ID only -->
                    <text-input
                        v-if="isPublic"
                        required
                        label="PhilRice ID"
                        placeholder="XX-XXXX"
                        name="employee_id"
                        id="employee_id"
                        v-model="form.employee_id"
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
                        :label="'Quantity by ' + data?.unit"
                        name="quantity"
                        :placeholder="'How many ' + data?.unit + '(s)?'"
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
                        <cancel-btn @click="resetForm(['barcode','item_id','transac_type','unit'])">
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
        <!-- Transactions Recent Ativities -->
        <div class="bg-white dark:bg-gray-800 sm:rounded-lg md:px-4 w-full">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200 mb-3">Recent Transactions</h3>
            <div class="flex flex-col text-xs gap-1 w-full">
                <div v-if="processing" class="text-center py-3 bg-yellow-200 rounded-lg w-full">
                    <p>
                    Loading ...
                    </p>
                </div>
                <div v-else v-for="transaction in recentTransactions" :key="transaction.id" class="py-2 rounded-md px-2 duration-200 flex flex-col gap-1" :class="transaction.transac_type === 'incoming' ? 'bg-green-200':'bg-red-200'">
                    <div class="flex justify-between items-center leading-tight">
                        <div>   
                            <p>{{ transaction.personnel ? transaction.personnel.fname + ' ' + transaction.personnel.lname : 'Unknown' }}</p>
                            <p>{{ formatDate(transaction.created_at) }}</p>
                        </div>

                        <b class="text-center leading-none">
                            <span v-if="transaction.transac_type === 'incoming'">{{ transaction.quantity }}</span>
                            <span v-else>-{{ transaction.quantity }}</span>
                            {{ transaction.unit }}
                        </b>
                    </div>
                    <div v-if="transaction.remarks" class="border-t pt-1 border-gray-800 leading-none">
                        <span>{{ transaction.remarks }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>

</style>
