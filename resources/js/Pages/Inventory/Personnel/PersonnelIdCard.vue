<template>
        <section class="personnel-id-card w-[5.4cm] h-[8.6cm] border border-slate-200 rounded-lg bg-white shadow-md flex flex-col overflow-hidden font-sans box-border"
                :style="cardStyle">
                <header class="p-[10px_8px] flex items-center gap-2">
                        <img src="/imgs/logo-black.png" alt="CBC Logo" class="w-6 h-6 object-contain" />
                        <div class="flex flex-col">
                                <h1
                                        class="text-[9px] font-extrabold text-[#14532d] uppercase m-0 leading-tight tracking-[0.3px]">
                                        DA-Crop Biotechnology Center
                                </h1>
                        </div>
                </header>

                <div class="flex-grow flex flex-col items-center">
                        <div class="flex flex-col relative w-full items-center justify-center">
                                <div class="absolute top-1/2 -translate-y-1/2 bg-[#14532d] w-full h-[1cm] z-0"></div>

                                <div class="mb-2 z-10 relative">
                                        <img v-if="card.photo_url" :src="card.photo_url" alt="Personnel Photo"
                                                class="w-[2.8cm] h-[2.8cm] rounded-full border-2 border-[#14532d] object-cover block bg-slate-50 shadow-sm" />
                                        <div v-else
                                                class="w-[2.8cm] h-[2.8cm] rounded-full border-2 border-[#14532d] block bg-slate-200 shadow-sm">
                                        </div>
                                </div>
                        </div>

                        <div class="px-[12px] w-full flex flex-col flex-grow pb-2">
                                <div class="text-center w-full mb-2 flex flex-col items-center shrink-0">
                                <h2
                                        class="text-[11px] font-bold text-slate-900 m-0 mb-1 uppercase leading-tight break-words line-clamp-3 w-full"
                                        style="display: -webkit-box; -webkit-box-orient: vertical; overflow: hidden;"
                                        :title="card.full_name">
                                        {{ card.full_name }}
                                </h2>
                                <span
                                        class="inline-block text-[8px] font-bold text-[#14532d] bg-green-100 px-2 py-0.5 rounded-full uppercase tracking-wider shrink-0">
                                        {{ card.employee_id || '—' }}
                                </span>
                        </div>

                        <dl class="mt-auto w-full flex flex-col gap-1.5 shrink-0">
                                <div class="flex flex-col border-b border-dashed border-slate-200 pb-1">
                                        <dt class="text-[7.5px] font-semibold text-slate-500 uppercase m-0">
                                                Registration Type
                                        </dt>
                                        <dd class="text-[9px] font-semibold text-slate-700 m-0 mt-px truncate" :title="card.registration_type_label">
                                                {{ card.registration_type_label || '—' }}
                                        </dd>
                                </div>

                                <div class="flex flex-col pb-0">
                                        <dt class="text-[7.5px] font-semibold text-slate-500 uppercase m-0">
                                                Date Issued
                                        </dt>
                                        <dd class="text-[9px] font-semibold text-slate-700 m-0 mt-px truncate">
                                                {{ card.date_issued || '—' }}
                                        </dd>
                                </div>
                        </dl>
                        </div>
                </div>

                <footer
                        class="text-[6px] text-center text-slate-400 p-[6px_10px] bg-slate-50 border-t border-slate-100 leading-normal">
                        This ID is system-generated for authorized DA-CBC access.
                </footer>
        </section>
</template>

<script>
export default {
        name: 'PersonnelIdCard',

        props: {
                card: {
                        type: Object,
                        required: true
                },

                cardStyle: {
                        type: [Object, String],
                        default() {
                                return {};
                        }
                }
        }
};
</script>
