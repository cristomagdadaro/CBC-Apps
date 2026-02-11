<script>
import { ref } from 'vue';
import OverviewTopic from './Topics/OverviewTopic.vue';
import CustomFormTopic from './Topics/CustomFormTopic.vue';
import FESRequestFormTopic from './Topics/FESRequestFormTopic.vue';
import InventorySystemTopic from './Topics/InventorySystemTopic.vue';
import InventoryTransactionsTopic from './Topics/InventoryTransactionsTopic.vue';
import InventoryReportTopic from './Topics/InventoryReportTopic.vue';
import AddSupplierTopic from './Topics/AddSupplierTopic.vue';
import AddItemTopic from './Topics/AddItemTopic.vue';
import ProfilePasswordTopic from './Topics/ProfilePasswordTopic.vue';
import ConsoleLoggerTopic from './Topics/ConsoleLoggerTopic.vue';
import RentalServicesTopic from './Topics/RentalServicesTopic.vue';
import SystemOptionsTopic from './Topics/SystemOptionsTopic.vue';

export default {
    name: 'ManualsIndex',
    components: { 
        OverviewTopic,
        CustomFormTopic,
        FESRequestFormTopic,
        InventorySystemTopic,
        InventoryTransactionsTopic,
        InventoryReportTopic,
        AddSupplierTopic,
        AddItemTopic,
        ProfilePasswordTopic,
        ConsoleLoggerTopic,
        RentalServicesTopic,
        SystemOptionsTopic,
    },
    setup() {
        const activeSection = ref('overview');

        const sections = {
            overview: {
                title: 'Overview',
                icon: '📖',
                component: OverviewTopic,
            },
            addCustomForm: {
                title: 'How to add a new custom form',
                icon: '📝',
                component: CustomFormTopic,
            },
            fesRequestForm: {
                title: 'How to use Facilities, Equipment, and Supplies Request Form',
                icon: '🏛️',
                component: FESRequestFormTopic,
            },
            inventorySystem: {
                title: 'How to use the Inventory System',
                icon: '📦',
                component: InventorySystemTopic,
            },
            inventoryTransactions: {
                title: 'How to add Incoming or Outgoing Transactions',
                icon: '📊',
                component: InventoryTransactionsTopic,
            },
            inventoryReport: {
                title: 'How to file a Report',
                icon: '📄',
                component: InventoryReportTopic,
            },
            addSupplier: {
                title: 'How to add a new Supplier',
                icon: '🤝',
                component: AddSupplierTopic,
            },
            addItem: {
                title: 'How to add a new Item',
                icon: '🏷️',
                component: AddItemTopic,
            },
            profilePassword: {
                title: 'How to update Profile and Password',
                icon: '👤',
                component: ProfilePasswordTopic,
            },
            consoleLogger: {
                title: 'Console Logger (Development)',
                icon: '🖥️',
                component: ConsoleLoggerTopic,
            },
            rentalServices: {
                title: 'Rental Services Module',
                icon: '🚗',
                component: RentalServicesTopic,
            },
            systemOptions: {
                title: 'How to use System Options',
                icon: '⚙️',
                component: SystemOptionsTopic,
            },
        };

        const menuItems = [
            { id: 'overview', label: 'Overview', icon: '📖' },
            { id: 'addCustomForm', label: 'How to add a custom form', icon: '📝' },
            { id: 'fesRequestForm', label: 'How to use FES Request Form', icon: '🏛️' },
            { id: 'inventorySystem', label: 'How to use the Inventory System', icon: '📦' },
            { id: 'inventoryTransactions', label: 'How to add Incoming/Outgoing', icon: '📊' },
            { id: 'rentalServices', label: 'Rental Services Module', icon: '🚗' },
            { id: 'inventoryReport', label: 'How to file a Report', icon: '📄' },
            { id: 'addSupplier', label: 'How to add a new Supplier', icon: '🤝' },
            { id: 'addItem', label: 'How to add a new Item', icon: '🏷️' },
            { id: 'profilePassword', label: 'How to update Profile and Password', icon: '👤' },
            { id: 'consoleLogger', label: 'Console Logger (Development)', icon: '🖥️' },
            { id: 'systemOptions', label: 'How to use System Options', icon: '⚙️' },
        ];

        return {
            activeSection,
            sections,
            menuItems
        };
    },
}
</script>

<template>
    <AppLayout title="System Manuals">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                System Manuals & Guides
            </h2>
        </template>

        <div class="py-6 px-4 sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto">
                <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                    <!-- Sidebar Menu -->
                    <div class="lg:col-span-1">
                        <nav class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 space-y-2 sticky top-20">
                            <button
                                v-for="item in menuItems"
                                :key="item.id"
                                @click="activeSection = item.id"
                                :class="[
                                    'w-full text-left px-4 py-2 rounded-md transition-colors duration-200',
                                    activeSection === item.id
                                        ? 'bg-blue-600 text-white'
                                        : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'
                                ]"
                            >
                                <span class="mr-2">{{ item.icon }}</span>
                                {{ item.label }}
                            </button>
                        </nav>
                    </div>

                    <!-- Content Area -->
                    <div class="lg:col-span-3">
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 sm:p-8">
                            <div class="flex items-center gap-3 mb-6 pb-4 border-b border-gray-200 dark:border-gray-700">
                                <span class="text-3xl">{{ sections[activeSection]?.icon }}</span>
                                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                                    {{ sections[activeSection]?.title }}
                                </h1>
                            </div>

                            <component 
                                :is="sections[activeSection]?.component"
                                class="prose prose-sm dark:prose-invert max-w-none"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
/* Prose styling for better readability */
:deep(code) {
    background-color: #f3f4f6;
    color: #1f2937;
    padding: 0.125rem 0.375rem;
    border-radius: 0.25rem;
    font-family: 'Courier New', monospace;
}

:deep(.dark code) {
    background-color: #374151;
    color: #f3f4f6;
}

:deep(p) {
    margin-bottom: 1rem;
    line-height: 1.6;
}

:deep(h3) {
    margin-top: 1.5rem;
}

:deep(h4) {
    margin-top: 1rem;
    margin-bottom: 0.5rem;
}

:deep(ul) {
    margin: 0.5rem 0;
}

:deep(ol) {
    margin: 0.5rem 0;
}

:deep(li) {
    margin-bottom: 0.5rem;
}
</style>
