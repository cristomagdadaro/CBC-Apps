<script>
import TopicLayout from "@/Pages/Manuals/Components/TopicLayout.vue";

export default {
    name: "InventorySystemTopic",
    components: {
        TopicLayout,
    },
    props: {
        showDeveloperSections: {
            type: Boolean,
            default: true,
        },
    },
};
</script>

<template>
    <TopicLayout
        title="Inventory System Overview"
        description="Tracks all items, supplies, equipment, and transactions in your organization. Helps manage stock levels and generate reports."
        icon="LuPackageOpen">
        <div class="space-y-8">
            <!-- For Non-Programmers -->
            <div class="space-y-4">
                <h3 class="text-[0.65rem] font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400 ml-1">For Non-Programmers (Managers & Staff)</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Main Components -->
                    <div class="bg-sky-50/50 dark:bg-sky-500/5 border border-sky-100 dark:border-sky-500/20 rounded-xl p-5 shadow-sm">
                        <h4 class="text-[0.65rem] font-semibold uppercase tracking-widest text-sky-600 dark:text-sky-400 mb-4 flex items-center gap-1.5">
                            <LuLayers class="w-3.5 h-3.5" />
                            Main Components
                        </h4>
                        <ul class="space-y-3 text-xs font-medium text-slate-600 dark:text-slate-300 ml-1">
                            <li class="flex items-start gap-2.5">
                                <LuCheckCircle2 class="w-3.5 h-3.5 text-sky-500 shrink-0 mt-0.5" />
                                <p class="leading-relaxed">
                                    <span class="font-semibold text-slate-800 dark:text-slate-200">Items:</span>
                                    All products and supplies.
                                </p>
                            </li>
                            <li class="flex items-start gap-2.5">
                                <LuCheckCircle2 class="w-3.5 h-3.5 text-sky-500 shrink-0 mt-0.5" />
                                <p class="leading-relaxed">
                                    <span class="font-semibold text-slate-800 dark:text-slate-200">Categories:</span>
                                    Organizational groups.
                                </p>
                            </li>
                            <li class="flex items-start gap-2.5">
                                <LuCheckCircle2 class="w-3.5 h-3.5 text-sky-500 shrink-0 mt-0.5" />
                                <p class="leading-relaxed">
                                    <span class="font-semibold text-slate-800 dark:text-slate-200">Suppliers:</span>
                                    Vendors and item sources.
                                </p>
                            </li>
                            <li class="flex items-start gap-2.5">
                                <LuCheckCircle2 class="w-3.5 h-3.5 text-sky-500 shrink-0 mt-0.5" />
                                <p class="leading-relaxed">
                                    <span class="font-semibold text-slate-800 dark:text-slate-200">Transactions:</span>
                                    Incoming and outgoing records.
                                </p>
                            </li>
                            <li class="flex items-start gap-2.5">
                                <LuCheckCircle2 class="w-3.5 h-3.5 text-sky-500 shrink-0 mt-0.5" />
                                <p class="leading-relaxed">
                                    <span class="font-semibold text-slate-800 dark:text-slate-200">Stock Levels:</span>
                                    Real-time quantity tracking.
                                </p>
                            </li>
                        </ul>
                    </div>

                    <!-- Stock Level Indicators -->
                    <div class="bg-amber-50/50 dark:bg-amber-500/5 border border-amber-100 dark:border-amber-500/20 rounded-xl p-5 shadow-sm">
                        <h4 class="text-[0.65rem] font-semibold uppercase tracking-widest text-amber-600 dark:text-amber-400 mb-4 flex items-center gap-1.5">
                            <LuBarChart2 class="w-3.5 h-3.5" />
                            Stock Level Indicators
                        </h4>
                        <ul class="space-y-4 text-xs font-medium text-slate-600 dark:text-slate-300 ml-1">
                            <li class="flex items-center gap-3">
                                <div class="w-2.5 h-2.5 rounded-full bg-emerald-500 shadow-sm shrink-0"></div>
                                <p class="leading-relaxed">
                                    <span class="font-semibold text-slate-800 dark:text-slate-200">Full:</span>
                                    Sufficient stock (75% - 100%)
                                </p>
                            </li>
                            <li class="flex items-center gap-3">
                                <div class="w-2.5 h-2.5 rounded-full bg-amber-400 shadow-sm shrink-0"></div>
                                <p class="leading-relaxed">
                                    <span class="font-semibold text-slate-800 dark:text-slate-200">Mid:</span>
                                    Moderate stock level (25% - 75%)
                                </p>
                            </li>
                            <li class="flex items-center gap-3">
                                <div class="w-2.5 h-2.5 rounded-full bg-orange-500 shadow-sm shrink-0"></div>
                                <p class="leading-relaxed">
                                    <span class="font-semibold text-slate-800 dark:text-slate-200">Low:</span>
                                    Running low on stock (1% - 25%)
                                </p>
                            </li>
                            <li class="flex items-center gap-3">
                                <div class="w-2.5 h-2.5 rounded-full bg-rose-500 shadow-sm shrink-0"></div>
                                <p class="leading-relaxed">
                                    <span class="font-semibold text-slate-800 dark:text-slate-200">Empty:</span>
                                    Stock depleted (0%)
                                </p>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Accessing the System -->
                <div class="bg-emerald-50/50 dark:bg-emerald-500/5 border border-emerald-100 dark:border-emerald-500/20 rounded-xl p-5 shadow-sm">
                    <h4 class="text-[0.65rem] font-semibold uppercase tracking-widest text-emerald-600 dark:text-emerald-400 mb-4 flex items-center gap-1.5">
                        <LuMousePointerClick class="w-3.5 h-3.5" />
                        Accessing the Inventory System
                    </h4>
                    <ol class="space-y-3.5 text-xs font-medium text-slate-600 dark:text-slate-300 list-decimal list-inside ml-1">
                        <li>
                            Go to
                            <span class="font-semibold text-slate-800 dark:text-slate-200">Inventory Management</span>
                            in the main navigation.
                        </li>
                        <li>
                            Select the section you need:
                            <span class="font-semibold text-slate-800 dark:text-slate-200">Items</span>
                            ,
                            <span class="font-semibold text-slate-800 dark:text-slate-200">Suppliers</span>
                            , or
                            <span class="font-semibold text-slate-800 dark:text-slate-200">Transactions</span>
                            .
                        </li>
                        <li>Use filters to search and sort inventory data.</li>
                        <li>View detailed information by clicking on any item or transaction record.</li>
                    </ol>
                </div>
            </div>

            <!-- For Programmers -->
            <div
                v-if="showDeveloperSections"
                class="space-y-4 pt-6 border-t border-slate-100 dark:border-slate-800/60">
                <h3 class="text-[0.65rem] font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400 ml-1">For Programmers (Architecture)</h3>

                <div class="bg-sky-50/50 dark:bg-sky-500/5 border border-sky-100 dark:border-sky-500/20 rounded-xl p-5 shadow-sm">
                    <h4 class="text-[0.65rem] font-semibold uppercase tracking-widest text-sky-600 dark:text-sky-400 mb-4 flex items-center gap-1.5">
                        <LuLayers class="w-3.5 h-3.5" />
                        Main Components
                    </h4>
                    <ul class="space-y-3 text-xs font-medium text-slate-600 dark:text-slate-300 ml-1">
                        <li class="flex items-start gap-2.5">
                            <LuCheckCircle2 class="w-3.5 h-3.5 text-sky-500 shrink-0 mt-0.5" />
                            <p class="leading-relaxed">
                                <span class="font-semibold text-slate-800 dark:text-slate-200">Items:</span>
                                All products and supplies.
                            </p>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <LuCheckCircle2 class="w-3.5 h-3.5 text-sky-500 shrink-0 mt-0.5" />
                            <p class="leading-relaxed">
                                <span class="font-semibold text-slate-800 dark:text-slate-200">Categories:</span>
                                Organizational groups.
                            </p>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <LuCheckCircle2 class="w-3.5 h-3.5 text-sky-500 shrink-0 mt-0.5" />
                            <p class="leading-relaxed">
                                <span class="font-semibold text-slate-800 dark:text-slate-200">Suppliers:</span>
                                Vendors and item sources.
                            </p>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <LuCheckCircle2 class="w-3.5 h-3.5 text-sky-500 shrink-0 mt-0.5" />
                            <p class="leading-relaxed">
                                <span class="font-semibold text-slate-800 dark:text-slate-200">Transactions:</span>
                                Incoming and outgoing records.
                            </p>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <LuCheckCircle2 class="w-3.5 h-3.5 text-sky-500 shrink-0 mt-0.5" />
                            <p class="leading-relaxed">
                                <span class="font-semibold text-slate-800 dark:text-slate-200">Stock Levels:</span>
                                Real-time quantity tracking.
                            </p>
                        </li>
                    </ul>
                </div>

                <!-- Stock Level Indicators -->
                <div class="bg-amber-50/50 dark:bg-amber-500/5 border border-amber-100 dark:border-amber-500/20 rounded-xl p-5 shadow-sm">
                    <h4 class="text-[0.65rem] font-semibold uppercase tracking-widest text-amber-600 dark:text-amber-400 mb-4 flex items-center gap-1.5">
                        <LuBarChart2 class="w-3.5 h-3.5" />
                        Stock Level Indicators
                    </h4>
                    <ul class="space-y-4 text-xs font-medium text-slate-600 dark:text-slate-300 ml-1">
                        <li class="flex items-center gap-3">
                            <div class="w-2.5 h-2.5 rounded-full bg-emerald-500 shadow-sm shrink-0"></div>
                            <p class="leading-relaxed">
                                <span class="font-semibold text-slate-800 dark:text-slate-200">Full:</span>
                                Sufficient stock (75% - 100%)
                            </p>
                        </li>
                        <li class="flex items-center gap-3">
                            <div class="w-2.5 h-2.5 rounded-full bg-amber-400 shadow-sm shrink-0"></div>
                            <p class="leading-relaxed">
                                <span class="font-semibold text-slate-800 dark:text-slate-200">Mid:</span>
                                Moderate stock level (25% - 75%)
                            </p>
                        </li>
                        <li class="flex items-center gap-3">
                            <div class="w-2.5 h-2.5 rounded-full bg-orange-500 shadow-sm shrink-0"></div>
                            <p class="leading-relaxed">
                                <span class="font-semibold text-slate-800 dark:text-slate-200">Low:</span>
                                Running low on stock (1% - 25%)
                            </p>
                        </li>
                        <li class="flex items-center gap-3">
                            <div class="w-2.5 h-2.5 rounded-full bg-rose-500 shadow-sm shrink-0"></div>
                            <p class="leading-relaxed">
                                <span class="font-semibold text-slate-800 dark:text-slate-200">Empty:</span>
                                Stock depleted (0%)
                            </p>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Accessing the System -->
            <div class="bg-emerald-50/50 dark:bg-emerald-500/5 border border-emerald-100 dark:border-emerald-500/20 rounded-xl p-5 shadow-sm">
                <h4 class="text-[0.65rem] font-semibold uppercase tracking-widest text-emerald-600 dark:text-emerald-400 mb-4 flex items-center gap-1.5">
                    <LuMousePointerClick class="w-3.5 h-3.5" />
                    Accessing the Inventory System
                </h4>
                <ol class="space-y-3.5 text-xs font-medium text-slate-600 dark:text-slate-300 list-decimal list-inside ml-1">
                    <li>
                        Go to
                        <span class="font-semibold text-slate-800 dark:text-slate-200">Inventory Management</span>
                        in the main navigation.
                    </li>
                    <li>
                        Select the section you need:
                        <span class="font-semibold text-slate-800 dark:text-slate-200">Items</span>
                        ,
                        <span class="font-semibold text-slate-800 dark:text-slate-200">Suppliers</span>
                        , or
                        <span class="font-semibold text-slate-800 dark:text-slate-200">Transactions</span>
                        .
                    </li>
                    <li>Use filters to search and sort inventory data.</li>
                    <li>View detailed information by clicking on any item or transaction record.</li>
                </ol>
            </div>
        </div>

        <!-- For Programmers -->
        <div
            v-if="showDeveloperSections"
            class="space-y-4 pt-6 border-t border-slate-100 dark:border-slate-800/60">
            <h3 class="text-[0.65rem] font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400 ml-1">For Programmers (Architecture)</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Models -->
                <div class="bg-indigo-50/50 dark:bg-indigo-500/5 border border-indigo-100 dark:border-indigo-500/20 rounded-xl p-5 shadow-sm">
                    <h4 class="text-[0.65rem] font-semibold uppercase tracking-widest text-indigo-600 dark:text-indigo-400 mb-4 flex items-center gap-1.5">
                        <LuDatabase class="w-3.5 h-3.5" />
                        Key Models & Files
                    </h4>
                    <ul class="space-y-3 text-xs font-medium text-slate-600 dark:text-slate-400">
                        <li class="flex items-center">
                            <code class="px-1.5 py-0.5 mr-2 rounded-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.65rem] font-semibold shadow-sm text-indigo-500">app/Models/Item.php</code>
                            Item model
                        </li>
                        <li class="flex items-center">
                            <code class="px-1.5 py-0.5 mr-2 rounded-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.65rem] font-semibold shadow-sm text-indigo-500">app/Models/Category.php</code>
                            Category model
                        </li>
                        <li class="flex items-center">
                            <code class="px-1.5 py-0.5 mr-2 rounded-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.65rem] font-semibold shadow-sm text-indigo-500">app/Models/Supplier.php</code>
                            Supplier model
                        </li>
                        <li class="flex items-center">
                            <code class="px-1.5 py-0.5 mr-2 rounded-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.65rem] font-semibold shadow-sm text-indigo-500">app/Models/Transaction.php</code>
                            Transaction model
                        </li>
                        <li class="flex items-center">
                            <code class="px-1.5 py-0.5 mr-2 rounded-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.65rem] font-semibold shadow-sm text-indigo-500">routes/inventory.php</code>
                            API routes
                        </li>
                    </ul>
                </div>

                <!-- Frontend -->
                <div class="bg-purple-50/50 dark:bg-purple-500/5 border border-purple-100 dark:border-purple-500/20 rounded-xl p-5 shadow-sm">
                    <h4 class="text-[0.65rem] font-semibold uppercase tracking-widest text-purple-600 dark:text-purple-400 mb-4 flex items-center gap-1.5">
                        <LuMonitorSmartphone class="w-3.5 h-3.5" />
                        Key Frontend Components
                    </h4>
                    <ul class="space-y-3.5 text-xs font-medium text-slate-600 dark:text-slate-400">
                        <li>
                            <span class="block mb-1 text-slate-400 dark:text-slate-500 font-semibold uppercase tracking-wider text-[0.6rem]">Item Management UI</span>
                            <code class="block px-2 py-1.5 rounded-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.65rem] font-semibold shadow-sm text-purple-600 dark:text-purple-400 break-all">resources/js/Pages/Inventory/Items</code>
                        </li>
                        <li>
                            <span class="block mb-1 text-slate-400 dark:text-slate-500 font-semibold uppercase tracking-wider text-[0.6rem]">Transaction UI</span>
                            <code class="block px-2 py-1.5 rounded-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.65rem] font-semibold shadow-sm text-purple-600 dark:text-purple-400 break-all">resources/js/Pages/Inventory/Transactions</code>
                        </li>
                        <li>
                            <span class="block mb-1 text-slate-400 dark:text-slate-500 font-semibold uppercase tracking-wider text-[0.6rem]">Supplier Management UI</span>
                            <code class="block px-2 py-1.5 rounded-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.65rem] font-semibold shadow-sm text-purple-600 dark:text-purple-400 break-all">resources/js/Pages/Inventory/Supplier</code>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </TopicLayout>
</template>
