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
                <h3 class="ml-1 text-[0.65rem] font-semibold uppercase text-slate-500 dark:text-slate-400">For Non-Programmers (Managers & Staff)</h3>

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <!-- Main Components -->
                    <div class="rounded-xl border border-sky-100 bg-sky-50/50 p-5 shadow-sm dark:border-sky-500/20 dark:bg-sky-500/5">
                        <h4 class="mb-4 flex items-center gap-1.5 text-[0.65rem] font-semibold uppercase text-sky-600 dark:text-sky-400">
                            <LuLayers class="h-3.5 w-3.5" />
                            Main Components
                        </h4>
                        <ul class="ml-1 space-y-3 text-xs font-medium text-slate-600 dark:text-slate-300">
                            <li class="flex items-start gap-2.5">
                                <LuCheckCircle2 class="mt-0.5 h-3.5 w-3.5 shrink-0 text-sky-500" />
                                <p class="leading-relaxed">
                                    <span class="font-semibold text-slate-800 dark:text-slate-200">Items:</span>
                                    All products and supplies.
                                </p>
                            </li>
                            <li class="flex items-start gap-2.5">
                                <LuCheckCircle2 class="mt-0.5 h-3.5 w-3.5 shrink-0 text-sky-500" />
                                <p class="leading-relaxed">
                                    <span class="font-semibold text-slate-800 dark:text-slate-200">Categories:</span>
                                    Organizational groups.
                                </p>
                            </li>
                            <li class="flex items-start gap-2.5">
                                <LuCheckCircle2 class="mt-0.5 h-3.5 w-3.5 shrink-0 text-sky-500" />
                                <p class="leading-relaxed">
                                    <span class="font-semibold text-slate-800 dark:text-slate-200">Suppliers:</span>
                                    Vendors and item sources.
                                </p>
                            </li>
                            <li class="flex items-start gap-2.5">
                                <LuCheckCircle2 class="mt-0.5 h-3.5 w-3.5 shrink-0 text-sky-500" />
                                <p class="leading-relaxed">
                                    <span class="font-semibold text-slate-800 dark:text-slate-200">Transactions:</span>
                                    Incoming and outgoing records.
                                </p>
                            </li>
                            <li class="flex items-start gap-2.5">
                                <LuCheckCircle2 class="mt-0.5 h-3.5 w-3.5 shrink-0 text-sky-500" />
                                <p class="leading-relaxed">
                                    <span class="font-semibold text-slate-800 dark:text-slate-200">Stock Levels:</span>
                                    Real-time quantity tracking.
                                </p>
                            </li>
                        </ul>
                    </div>

                    <!-- Stock Level Indicators -->
                    <div class="rounded-xl border border-amber-100 bg-amber-50/50 p-5 shadow-sm dark:border-amber-500/20 dark:bg-amber-500/5">
                        <h4 class="mb-4 flex items-center gap-1.5 text-[0.65rem] font-semibold uppercase text-amber-600 dark:text-amber-400">
                            <LuBarChart2 class="h-3.5 w-3.5" />
                            Stock Level Indicators
                        </h4>
                        <ul class="ml-1 space-y-4 text-xs font-medium text-slate-600 dark:text-slate-300">
                            <li class="flex items-center gap-3">
                                <div class="h-2.5 w-2.5 shrink-0 rounded-full bg-emerald-500 shadow-sm"></div>
                                <p class="leading-relaxed">
                                    <span class="font-semibold text-slate-800 dark:text-slate-200">Full:</span>
                                    Sufficient stock (75% - 100%)
                                </p>
                            </li>
                            <li class="flex items-center gap-3">
                                <div class="h-2.5 w-2.5 shrink-0 rounded-full bg-amber-400 shadow-sm"></div>
                                <p class="leading-relaxed">
                                    <span class="font-semibold text-slate-800 dark:text-slate-200">Mid:</span>
                                    Moderate stock level (25% - 75%)
                                </p>
                            </li>
                            <li class="flex items-center gap-3">
                                <div class="h-2.5 w-2.5 shrink-0 rounded-full bg-orange-500 shadow-sm"></div>
                                <p class="leading-relaxed">
                                    <span class="font-semibold text-slate-800 dark:text-slate-200">Low:</span>
                                    Running low on stock (1% - 25%)
                                </p>
                            </li>
                            <li class="flex items-center gap-3">
                                <div class="h-2.5 w-2.5 shrink-0 rounded-full bg-rose-500 shadow-sm"></div>
                                <p class="leading-relaxed">
                                    <span class="font-semibold text-slate-800 dark:text-slate-200">Empty:</span>
                                    Stock depleted (0%)
                                </p>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Accessing the System -->
                <div class="rounded-xl border border-emerald-100 bg-emerald-50/50 p-5 shadow-sm dark:border-emerald-500/20 dark:bg-emerald-500/5">
                    <h4 class="mb-4 flex items-center gap-1.5 text-[0.65rem] font-semibold uppercase text-emerald-600 dark:text-emerald-400">
                        <LuMousePointerClick class="h-3.5 w-3.5" />
                        Accessing the Inventory System
                    </h4>
                    <ol class="ml-1 list-inside list-decimal space-y-3.5 text-xs font-medium text-slate-600 dark:text-slate-300">
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
                class="space-y-4 border-t border-slate-100 pt-6 dark:border-slate-800/60">
                <h3 class="ml-1 text-[0.65rem] font-semibold uppercase text-slate-500 dark:text-slate-400">For Programmers (Architecture)</h3>

                <div class="rounded-xl border border-sky-100 bg-sky-50/50 p-5 shadow-sm dark:border-sky-500/20 dark:bg-sky-500/5">
                    <h4 class="mb-4 flex items-center gap-1.5 text-[0.65rem] font-semibold uppercase text-sky-600 dark:text-sky-400">
                        <LuLayers class="h-3.5 w-3.5" />
                        Main Components
                    </h4>
                    <ul class="ml-1 space-y-3 text-xs font-medium text-slate-600 dark:text-slate-300">
                        <li class="flex items-start gap-2.5">
                            <LuCheckCircle2 class="mt-0.5 h-3.5 w-3.5 shrink-0 text-sky-500" />
                            <p class="leading-relaxed">
                                <span class="font-semibold text-slate-800 dark:text-slate-200">Items:</span>
                                All products and supplies.
                            </p>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <LuCheckCircle2 class="mt-0.5 h-3.5 w-3.5 shrink-0 text-sky-500" />
                            <p class="leading-relaxed">
                                <span class="font-semibold text-slate-800 dark:text-slate-200">Categories:</span>
                                Organizational groups.
                            </p>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <LuCheckCircle2 class="mt-0.5 h-3.5 w-3.5 shrink-0 text-sky-500" />
                            <p class="leading-relaxed">
                                <span class="font-semibold text-slate-800 dark:text-slate-200">Suppliers:</span>
                                Vendors and item sources.
                            </p>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <LuCheckCircle2 class="mt-0.5 h-3.5 w-3.5 shrink-0 text-sky-500" />
                            <p class="leading-relaxed">
                                <span class="font-semibold text-slate-800 dark:text-slate-200">Transactions:</span>
                                Incoming and outgoing records.
                            </p>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <LuCheckCircle2 class="mt-0.5 h-3.5 w-3.5 shrink-0 text-sky-500" />
                            <p class="leading-relaxed">
                                <span class="font-semibold text-slate-800 dark:text-slate-200">Stock Levels:</span>
                                Real-time quantity tracking.
                            </p>
                        </li>
                    </ul>
                </div>

                <!-- Stock Level Indicators -->
                <div class="rounded-xl border border-amber-100 bg-amber-50/50 p-5 shadow-sm dark:border-amber-500/20 dark:bg-amber-500/5">
                    <h4 class="mb-4 flex items-center gap-1.5 text-[0.65rem] font-semibold uppercase text-amber-600 dark:text-amber-400">
                        <LuBarChart2 class="h-3.5 w-3.5" />
                        Stock Level Indicators
                    </h4>
                    <ul class="ml-1 space-y-4 text-xs font-medium text-slate-600 dark:text-slate-300">
                        <li class="flex items-center gap-3">
                            <div class="h-2.5 w-2.5 shrink-0 rounded-full bg-emerald-500 shadow-sm"></div>
                            <p class="leading-relaxed">
                                <span class="font-semibold text-slate-800 dark:text-slate-200">Full:</span>
                                Sufficient stock (75% - 100%)
                            </p>
                        </li>
                        <li class="flex items-center gap-3">
                            <div class="h-2.5 w-2.5 shrink-0 rounded-full bg-amber-400 shadow-sm"></div>
                            <p class="leading-relaxed">
                                <span class="font-semibold text-slate-800 dark:text-slate-200">Mid:</span>
                                Moderate stock level (25% - 75%)
                            </p>
                        </li>
                        <li class="flex items-center gap-3">
                            <div class="h-2.5 w-2.5 shrink-0 rounded-full bg-orange-500 shadow-sm"></div>
                            <p class="leading-relaxed">
                                <span class="font-semibold text-slate-800 dark:text-slate-200">Low:</span>
                                Running low on stock (1% - 25%)
                            </p>
                        </li>
                        <li class="flex items-center gap-3">
                            <div class="h-2.5 w-2.5 shrink-0 rounded-full bg-rose-500 shadow-sm"></div>
                            <p class="leading-relaxed">
                                <span class="font-semibold text-slate-800 dark:text-slate-200">Empty:</span>
                                Stock depleted (0%)
                            </p>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Accessing the System -->
            <div class="rounded-xl border border-emerald-100 bg-emerald-50/50 p-5 shadow-sm dark:border-emerald-500/20 dark:bg-emerald-500/5">
                <h4 class="mb-4 flex items-center gap-1.5 text-[0.65rem] font-semibold uppercase text-emerald-600 dark:text-emerald-400">
                    <LuMousePointerClick class="h-3.5 w-3.5" />
                    Accessing the Inventory System
                </h4>
                <ol class="ml-1 list-inside list-decimal space-y-3.5 text-xs font-medium text-slate-600 dark:text-slate-300">
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
            class="space-y-4 border-t border-slate-100 pt-6 dark:border-slate-800/60">
            <h3 class="ml-1 text-[0.65rem] font-semibold uppercase text-slate-500 dark:text-slate-400">For Programmers (Architecture)</h3>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <!-- Models -->
                <div class="rounded-xl border border-indigo-100 bg-indigo-50/50 p-5 shadow-sm dark:border-indigo-500/20 dark:bg-indigo-500/5">
                    <h4 class="mb-4 flex items-center gap-1.5 text-[0.65rem] font-semibold uppercase text-indigo-600 dark:text-indigo-400">
                        <LuDatabase class="h-3.5 w-3.5" />
                        Key Models & Files
                    </h4>
                    <ul class="space-y-3 text-xs font-medium text-slate-600 dark:text-slate-400">
                        <li class="flex items-center">
                            <code class="mr-2 rounded-md border border-slate-200 bg-white px-1.5 py-0.5 font-mono text-[0.65rem] font-semibold text-indigo-500 shadow-sm dark:border-slate-700 dark:bg-slate-800">app/Models/Item.php</code>
                            Item model
                        </li>
                        <li class="flex items-center">
                            <code class="mr-2 rounded-md border border-slate-200 bg-white px-1.5 py-0.5 font-mono text-[0.65rem] font-semibold text-indigo-500 shadow-sm dark:border-slate-700 dark:bg-slate-800">app/Models/Category.php</code>
                            Category model
                        </li>
                        <li class="flex items-center">
                            <code class="mr-2 rounded-md border border-slate-200 bg-white px-1.5 py-0.5 font-mono text-[0.65rem] font-semibold text-indigo-500 shadow-sm dark:border-slate-700 dark:bg-slate-800">app/Models/Supplier.php</code>
                            Supplier model
                        </li>
                        <li class="flex items-center">
                            <code class="mr-2 rounded-md border border-slate-200 bg-white px-1.5 py-0.5 font-mono text-[0.65rem] font-semibold text-indigo-500 shadow-sm dark:border-slate-700 dark:bg-slate-800">app/Models/Transaction.php</code>
                            Transaction model
                        </li>
                        <li class="flex items-center">
                            <code class="mr-2 rounded-md border border-slate-200 bg-white px-1.5 py-0.5 font-mono text-[0.65rem] font-semibold text-indigo-500 shadow-sm dark:border-slate-700 dark:bg-slate-800">routes/inventory.php</code>
                            API routes
                        </li>
                    </ul>
                </div>

                <!-- Frontend -->
                <div class="rounded-xl border border-purple-100 bg-purple-50/50 p-5 shadow-sm dark:border-purple-500/20 dark:bg-purple-500/5">
                    <h4 class="mb-4 flex items-center gap-1.5 text-[0.65rem] font-semibold uppercase text-purple-600 dark:text-purple-400">
                        <LuMonitorSmartphone class="h-3.5 w-3.5" />
                        Key Frontend Components
                    </h4>
                    <ul class="space-y-3.5 text-xs font-medium text-slate-600 dark:text-slate-400">
                        <li>
                            <span class="mb-1 block text-[0.6rem] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Item Management UI</span>
                            <code class="block break-all rounded-md border border-slate-200 bg-white px-2 py-1.5 font-mono text-[0.65rem] font-semibold text-purple-600 shadow-sm dark:border-slate-700 dark:bg-slate-800 dark:text-purple-400">resources/js/Pages/Inventory/Items</code>
                        </li>
                        <li>
                            <span class="mb-1 block text-[0.6rem] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Transaction UI</span>
                            <code class="block break-all rounded-md border border-slate-200 bg-white px-2 py-1.5 font-mono text-[0.65rem] font-semibold text-purple-600 shadow-sm dark:border-slate-700 dark:bg-slate-800 dark:text-purple-400">resources/js/Pages/Inventory/Transactions</code>
                        </li>
                        <li>
                            <span class="mb-1 block text-[0.6rem] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Supplier Management UI</span>
                            <code class="block break-all rounded-md border border-slate-200 bg-white px-2 py-1.5 font-mono text-[0.65rem] font-semibold text-purple-600 shadow-sm dark:border-slate-700 dark:bg-slate-800 dark:text-purple-400">resources/js/Pages/Inventory/Supplier</code>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </TopicLayout>
</template>
