<script>
import TopicLayout from '@/Pages/Manuals/Components/TopicLayout.vue';

export default {
    name: 'AddSupplierTopic',
    components: {
        TopicLayout
    },
    props: {
        showDeveloperSections: {
            type: Boolean,
            default: true,
        },
    },
}
</script>

<template>
    <TopicLayout
        title="Inventory Suppliers"
        description="A supplier is a vendor or organization that provides items and supplies to your organization[cite: 8]. Each supplier stores contact and business information[cite: 8]."
        icon="LuStore"
    >
                <!-- For Non-Programmers -->
                <div class="space-y-4">
                    <h3 class="text-[0.65rem] font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400 ml-1">For Non-Programmers (Procurement & Admin)</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        
                        <!-- Steps to Add -->
                        <div class="bg-emerald-50/50 dark:bg-emerald-500/5 border border-emerald-100 dark:border-emerald-500/20 rounded-xl p-5 shadow-sm">
                            <h4 class="text-[0.65rem] font-semibold uppercase tracking-widest text-emerald-600 dark:text-emerald-400 mb-3 flex items-center gap-1.5">
                                <LuListPlus class="w-3.5 h-3.5" /> Steps to Add a New Supplier
                            </h4>
                            <ol class="space-y-2.5 text-xs font-medium text-slate-600 dark:text-slate-300 list-decimal list-inside ml-1">
                                <li>Go to <span class="font-semibold text-slate-800 dark:text-slate-200">Inventory Management &rarr; Suppliers</span>[cite: 8]</li>
                                <li>Click the <span class="font-semibold text-slate-800 dark:text-slate-200">+ New Supplier</span> button[cite: 8]</li>
                                <li>Fill in the supplier information:[cite: 8]
                                    <ul class="list-none space-y-1.5 ml-5 mt-2">
                                        <li><span class="font-semibold text-slate-700 dark:text-slate-300">Name:</span> Company or organization name[cite: 8]</li>
                                        <li><span class="font-semibold text-slate-700 dark:text-slate-300">Email:</span> Contact email address[cite: 8]</li>
                                        <li><span class="font-semibold text-slate-700 dark:text-slate-300">Phone:</span> Contact phone number[cite: 8]</li>
                                        <li><span class="font-semibold text-slate-700 dark:text-slate-300">Address:</span> Business address[cite: 8]</li>
                                        <li><span class="font-semibold text-slate-700 dark:text-slate-300">Description:</span> Additional notes (optional)[cite: 8]</li>
                                    </ul>
                                </li>
                                <li>Click <span class="font-semibold text-slate-800 dark:text-slate-200">Save</span> to create the supplier[cite: 8]</li>
                                <li>The new supplier is now available when creating items or recording transactions[cite: 8]</li>
                            </ol>
                        </div>

                        <!-- Best Practices -->
                        <div class="bg-amber-50/50 dark:bg-amber-500/5 border border-amber-100 dark:border-amber-500/20 rounded-xl p-5 shadow-sm">
                            <h4 class="text-[0.65rem] font-semibold uppercase tracking-widest text-amber-600 dark:text-amber-400 mb-3 flex items-center gap-1.5">
                                <LuAlertTriangle class="w-3.5 h-3.5" /> Best Practices
                            </h4>
                            <ul class="space-y-3 text-xs font-medium text-slate-600 dark:text-slate-300 ml-1">
                                <li class="flex items-start gap-2.5"><LuCheckCircle2 class="w-3.5 h-3.5 text-amber-400 shrink-0 mt-0.5" /> <span class="leading-relaxed">Use consistent naming conventions (avoid duplicates)[cite: 8].</span></li>
                                <li class="flex items-start gap-2.5"><LuCheckCircle2 class="w-3.5 h-3.5 text-amber-400 shrink-0 mt-0.5" /> <span class="leading-relaxed">Always include valid contact information[cite: 8].</span></li>
                                <li class="flex items-start gap-2.5"><LuCheckCircle2 class="w-3.5 h-3.5 text-amber-400 shrink-0 mt-0.5" /> <span class="leading-relaxed">Update supplier details regularly[cite: 8].</span></li>
                                <li class="flex items-start gap-2.5"><LuCheckCircle2 class="w-3.5 h-3.5 text-amber-400 shrink-0 mt-0.5" /> <span class="leading-relaxed">Deactivate or soft-delete unused suppliers (don't permanently delete)[cite: 8].</span></li>
                            </ul>
                        </div>

                    </div>
                </div>

                <!-- For Programmers -->
                <div v-if="showDeveloperSections" class="space-y-4 pt-6 border-t border-slate-100 dark:border-slate-800/60">
                    <h3 class="text-[0.65rem] font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400 ml-1">For Programmers (Implementation)</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        
                        <!-- Fields & Validation -->
                        <div class="bg-slate-50/50 dark:bg-slate-800/30 border border-slate-200/60 dark:border-slate-700/60 rounded-xl p-5 shadow-sm">
                            <h4 class="text-[0.65rem] font-semibold uppercase tracking-widest text-slate-600 dark:text-slate-300 mb-3 flex items-center gap-1.5">
                                <LuDatabase class="w-3.5 h-3.5" /> Supplier Fields
                            </h4>
                            <ul class="space-y-2.5 text-xs font-medium text-slate-600 dark:text-slate-400">
                                <li class="flex items-center"><code class="px-1.5 py-0.5 mr-2 rounded-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.65rem] font-semibold shadow-sm text-indigo-500">id</code> Primary key[cite: 8]</li>
                                <li class="flex items-center"><code class="px-1.5 py-0.5 mr-2 rounded-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.65rem] font-semibold shadow-sm text-indigo-500">name</code> Supplier name (required)[cite: 8]</li>
                                <li class="flex items-center"><code class="px-1.5 py-0.5 mr-2 rounded-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.65rem] font-semibold shadow-sm text-indigo-500">email</code> Contact email[cite: 8]</li>
                                <li class="flex items-center"><code class="px-1.5 py-0.5 mr-2 rounded-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.65rem] font-semibold shadow-sm text-indigo-500">phone</code> Contact phone[cite: 8]</li>
                                <li class="flex items-center"><code class="px-1.5 py-0.5 mr-2 rounded-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.65rem] font-semibold shadow-sm text-indigo-500">address</code> Business address[cite: 8]</li>
                                <li class="flex items-center"><code class="px-1.5 py-0.5 mr-2 rounded-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.65rem] font-semibold shadow-sm text-indigo-500">description</code> Additional info[cite: 8]</li>
                            </ul>
                        </div>

                        <!-- API Endpoints -->
                        <div class="bg-slate-50/50 dark:bg-slate-800/30 border border-slate-200/60 dark:border-slate-700/60 rounded-xl p-5 shadow-sm">
                            <h4 class="text-[0.65rem] font-semibold uppercase tracking-widest text-slate-600 dark:text-slate-300 mb-3 flex items-center gap-1.5">
                                <LuNetwork class="w-3.5 h-3.5" /> API Endpoints
                            </h4>
                            <ul class="space-y-3.5 text-xs font-medium text-slate-600 dark:text-slate-400">
                                <li>
                                    <div class="flex items-center mb-1">
                                        <code class="px-1.5 py-0.5 mr-2 rounded-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.65rem] font-semibold shadow-sm text-sky-500">GET</code>
                                        <code class="font-mono text-[0.7rem] text-slate-800 dark:text-slate-200">/api/inventory/suppliers</code>
                                    </div>
                                    <span class="text-[0.65rem] text-slate-500 ml-10">List all suppliers[cite: 8]</span>
                                </li>
                                <li>
                                    <div class="flex items-center mb-1">
                                        <code class="px-1.5 py-0.5 mr-2 rounded-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.65rem] font-semibold shadow-sm text-emerald-500">POST</code>
                                        <code class="font-mono text-[0.7rem] text-slate-800 dark:text-slate-200">/api/inventory/suppliers</code>
                                    </div>
                                    <span class="text-[0.65rem] text-slate-500 ml-10">Create supplier[cite: 8]</span>
                                </li>
                                <li>
                                    <div class="flex items-center mb-1">
                                        <code class="px-1.5 py-0.5 mr-2 rounded-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.65rem] font-semibold shadow-sm text-amber-500">PUT</code>
                                        <code class="font-mono text-[0.7rem] text-slate-800 dark:text-slate-200">/api/inventory/suppliers/{id}</code>
                                    </div>
                                    <span class="text-[0.65rem] text-slate-500 ml-10">Update supplier[cite: 8]</span>
                                </li>
                                <li>
                                    <div class="flex items-center mb-1">
                                        <code class="px-1.5 py-0.5 mr-2 rounded-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.65rem] font-semibold shadow-sm text-rose-500">DEL</code>
                                        <code class="font-mono text-[0.7rem] text-slate-800 dark:text-slate-200">/api/inventory/suppliers/{id}</code>
                                    </div>
                                    <span class="text-[0.65rem] text-slate-500 ml-10">Delete supplier[cite: 8]</span>
                                </li>
                            </ul>
                        </div>

                    </div>
                </div>

    </TopicLayout>
</template>