<script>
import TopicLayout from "@/Pages/Manuals/Components/TopicLayout.vue";

export default {
    name: "AddSupplierTopic",
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
        title="Inventory Suppliers"
        description="A supplier is a vendor or organization that provides items and supplies to your organization[cite: 8]. Each supplier stores contact and business information[cite: 8]."
        icon="LuStore">
        <!-- For Non-Programmers -->
        <div class="space-y-4">
            <h3 class="ml-1 text-[0.65rem] font-semibold uppercase text-slate-500 dark:text-slate-400">For Non-Programmers (Procurement & Admin)</h3>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <!-- Steps to Add -->
                <div class="rounded-xl border border-emerald-100 bg-emerald-50/50 p-5 shadow-sm dark:border-emerald-500/20 dark:bg-emerald-500/5">
                    <h4 class="mb-3 flex items-center gap-1.5 text-[0.65rem] font-semibold uppercase text-emerald-600 dark:text-emerald-400">
                        <LuListPlus class="h-3.5 w-3.5" />
                        Steps to Add a New Supplier
                    </h4>
                    <ol class="ml-1 list-inside list-decimal space-y-2.5 text-xs font-medium text-slate-600 dark:text-slate-300">
                        <li>
                            Go to
                            <span class="font-semibold text-slate-800 dark:text-slate-200">Inventory Management &rarr; Suppliers</span>
                            [cite: 8]
                        </li>
                        <li>
                            Click the
                            <span class="font-semibold text-slate-800 dark:text-slate-200">+ New Supplier</span>
                            button[cite: 8]
                        </li>
                        <li>
                            Fill in the supplier information:[cite: 8]
                            <ul class="ml-5 mt-2 list-none space-y-1.5">
                                <li>
                                    <span class="font-semibold text-slate-700 dark:text-slate-300">Name:</span>
                                    Company or organization name[cite: 8]
                                </li>
                                <li>
                                    <span class="font-semibold text-slate-700 dark:text-slate-300">Email:</span>
                                    Contact email address[cite: 8]
                                </li>
                                <li>
                                    <span class="font-semibold text-slate-700 dark:text-slate-300">Phone:</span>
                                    Contact phone number[cite: 8]
                                </li>
                                <li>
                                    <span class="font-semibold text-slate-700 dark:text-slate-300">Address:</span>
                                    Business address[cite: 8]
                                </li>
                                <li>
                                    <span class="font-semibold text-slate-700 dark:text-slate-300">Description:</span>
                                    Additional notes (optional)[cite: 8]
                                </li>
                            </ul>
                        </li>
                        <li>
                            Click
                            <span class="font-semibold text-slate-800 dark:text-slate-200">Save</span>
                            to create the supplier[cite: 8]
                        </li>
                        <li>The new supplier is now available when creating items or recording transactions[cite: 8]</li>
                    </ol>
                </div>

                <!-- Best Practices -->
                <div class="rounded-xl border border-amber-100 bg-amber-50/50 p-5 shadow-sm dark:border-amber-500/20 dark:bg-amber-500/5">
                    <h4 class="mb-3 flex items-center gap-1.5 text-[0.65rem] font-semibold uppercase text-amber-600 dark:text-amber-400">
                        <LuAlertTriangle class="h-3.5 w-3.5" />
                        Best Practices
                    </h4>
                    <ul class="ml-1 space-y-3 text-xs font-medium text-slate-600 dark:text-slate-300">
                        <li class="flex items-start gap-2.5">
                            <LuCheckCircle2 class="mt-0.5 h-3.5 w-3.5 shrink-0 text-amber-400" />
                            <span class="leading-relaxed">Use consistent naming conventions (avoid duplicates)[cite: 8].</span>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <LuCheckCircle2 class="mt-0.5 h-3.5 w-3.5 shrink-0 text-amber-400" />
                            <span class="leading-relaxed">Always include valid contact information[cite: 8].</span>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <LuCheckCircle2 class="mt-0.5 h-3.5 w-3.5 shrink-0 text-amber-400" />
                            <span class="leading-relaxed">Update supplier details regularly[cite: 8].</span>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <LuCheckCircle2 class="mt-0.5 h-3.5 w-3.5 shrink-0 text-amber-400" />
                            <span class="leading-relaxed">Deactivate or soft-delete unused suppliers (don't permanently delete)[cite: 8].</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- For Programmers -->
        <div
            v-if="showDeveloperSections"
            class="space-y-4 border-t border-slate-100 pt-6 dark:border-slate-800/60">
            <h3 class="ml-1 text-[0.65rem] font-semibold uppercase text-slate-500 dark:text-slate-400">For Programmers (Implementation)</h3>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <!-- Fields & Validation -->
                <div class="rounded-xl border border-slate-200/60 bg-slate-50/50 p-5 shadow-sm dark:border-slate-700/60 dark:bg-slate-800/30">
                    <h4 class="mb-3 flex items-center gap-1.5 text-[0.65rem] font-semibold uppercase text-slate-600 dark:text-slate-300">
                        <LuDatabase class="h-3.5 w-3.5" />
                        Supplier Fields
                    </h4>
                    <ul class="space-y-2.5 text-xs font-medium text-slate-600 dark:text-slate-400">
                        <li class="flex items-center">
                            <code class="mr-2 rounded-md border border-slate-200 bg-white px-1.5 py-0.5 font-mono text-[0.65rem] font-semibold text-indigo-500 shadow-sm dark:border-slate-700 dark:bg-slate-800">id</code>
                            Primary key[cite: 8]
                        </li>
                        <li class="flex items-center">
                            <code class="mr-2 rounded-md border border-slate-200 bg-white px-1.5 py-0.5 font-mono text-[0.65rem] font-semibold text-indigo-500 shadow-sm dark:border-slate-700 dark:bg-slate-800">name</code>
                            Supplier name (required)[cite: 8]
                        </li>
                        <li class="flex items-center">
                            <code class="mr-2 rounded-md border border-slate-200 bg-white px-1.5 py-0.5 font-mono text-[0.65rem] font-semibold text-indigo-500 shadow-sm dark:border-slate-700 dark:bg-slate-800">email</code>
                            Contact email[cite: 8]
                        </li>
                        <li class="flex items-center">
                            <code class="mr-2 rounded-md border border-slate-200 bg-white px-1.5 py-0.5 font-mono text-[0.65rem] font-semibold text-indigo-500 shadow-sm dark:border-slate-700 dark:bg-slate-800">phone</code>
                            Contact phone[cite: 8]
                        </li>
                        <li class="flex items-center">
                            <code class="mr-2 rounded-md border border-slate-200 bg-white px-1.5 py-0.5 font-mono text-[0.65rem] font-semibold text-indigo-500 shadow-sm dark:border-slate-700 dark:bg-slate-800">address</code>
                            Business address[cite: 8]
                        </li>
                        <li class="flex items-center">
                            <code class="mr-2 rounded-md border border-slate-200 bg-white px-1.5 py-0.5 font-mono text-[0.65rem] font-semibold text-indigo-500 shadow-sm dark:border-slate-700 dark:bg-slate-800">description</code>
                            Additional info[cite: 8]
                        </li>
                    </ul>
                </div>

                <!-- API Endpoints -->
                <div class="rounded-xl border border-slate-200/60 bg-slate-50/50 p-5 shadow-sm dark:border-slate-700/60 dark:bg-slate-800/30">
                    <h4 class="mb-3 flex items-center gap-1.5 text-[0.65rem] font-semibold uppercase text-slate-600 dark:text-slate-300">
                        <LuNetwork class="h-3.5 w-3.5" />
                        API Endpoints
                    </h4>
                    <ul class="space-y-3.5 text-xs font-medium text-slate-600 dark:text-slate-400">
                        <li>
                            <div class="mb-1 flex items-center">
                                <code class="mr-2 rounded-md border border-slate-200 bg-white px-1.5 py-0.5 font-mono text-[0.65rem] font-semibold text-sky-500 shadow-sm dark:border-slate-700 dark:bg-slate-800">GET</code>
                                <code class="font-mono text-[0.7rem] text-slate-800 dark:text-slate-200">/api/inventory/suppliers</code>
                            </div>
                            <span class="ml-10 text-[0.65rem] text-slate-500">List all suppliers[cite: 8]</span>
                        </li>
                        <li>
                            <div class="mb-1 flex items-center">
                                <code class="mr-2 rounded-md border border-slate-200 bg-white px-1.5 py-0.5 font-mono text-[0.65rem] font-semibold text-emerald-500 shadow-sm dark:border-slate-700 dark:bg-slate-800">POST</code>
                                <code class="font-mono text-[0.7rem] text-slate-800 dark:text-slate-200">/api/inventory/suppliers</code>
                            </div>
                            <span class="ml-10 text-[0.65rem] text-slate-500">Create supplier[cite: 8]</span>
                        </li>
                        <li>
                            <div class="mb-1 flex items-center">
                                <code class="mr-2 rounded-md border border-slate-200 bg-white px-1.5 py-0.5 font-mono text-[0.65rem] font-semibold text-amber-500 shadow-sm dark:border-slate-700 dark:bg-slate-800">PUT</code>
                                <code class="font-mono text-[0.7rem] text-slate-800 dark:text-slate-200">/api/inventory/suppliers/{id}</code>
                            </div>
                            <span class="ml-10 text-[0.65rem] text-slate-500">Update supplier[cite: 8]</span>
                        </li>
                        <li>
                            <div class="mb-1 flex items-center">
                                <code class="mr-2 rounded-md border border-slate-200 bg-white px-1.5 py-0.5 font-mono text-[0.65rem] font-semibold text-rose-500 shadow-sm dark:border-slate-700 dark:bg-slate-800">DEL</code>
                                <code class="font-mono text-[0.7rem] text-slate-800 dark:text-slate-200">/api/inventory/suppliers/{id}</code>
                            </div>
                            <span class="ml-10 text-[0.65rem] text-slate-500">Delete supplier[cite: 8]</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </TopicLayout>
</template>
