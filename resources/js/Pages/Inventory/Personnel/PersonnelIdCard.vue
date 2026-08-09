<template>
    <section 
        class="personnel-id-card w-[5.4cm] h-[8.6cm] border border-slate-300 bg-white shadow-md flex flex-col overflow-hidden font-sans box-border relative print:border-slate-300 print:shadow-none"
        :style="cardStyle"
    >
        <!-- Background Overlay -->
        <img src="/imgs/Overlay.png" alt="Overlay" class="absolute top-0 right-0 h-[2cm] pointer-events-none z-0 mix-blend-multiply object-cover object-right-top" />

        <!-- Header -->
        <header class="p-[10px_10px_6px_10px] flex items-center gap-1 z-10 relative">
            <img src="/imgs/logo-black.png" alt="CBC Logo" class="w-9 h-9 object-contain shrink-0" />
            <div class="flex flex-col flex-1 leading-tight">
                <div class="text-[6px] text-slate-800 leading-[7px]">
                    Department of Agriculture
                </div>
                <div class="text-[8px] font-bold text-[#4CAF50] uppercase leading-[9px] tracking-tight">
                    CROP BIOTECHNOLOGY CENTER
                </div>
                <div class="text-[5px] text-slate-600 leading-[6px] mt-[1px]">
                    DA-PhilRice Compound, Muñoz, Nueva Ecija
                </div>
            </div>
        </header>

        <!-- Body / Content -->
        <div class="flex-grow flex flex-col items-center relative z-10">
            <!-- Photo Area with Accent -->
            <div class="flex flex-col relative w-full items-center justify-center my-3.5">
                <div class="absolute top-1/2 -translate-y-1/2 bg-[#559E47] w-full h-[1.3cm] z-0"></div>

                <div class="z-10 relative bg-white rounded-full border-[5px] border-white">
                    <img v-if="card.photo_url" :src="card.photo_url" alt="Personnel Photo" class="w-[2.6cm] h-[2.6cm] rounded-full border-[2px] border-[#559E47] object-cover block bg-slate-50" />
                    <div v-else class="w-[2.6cm] h-[2.6cm] rounded-full border-[2px] border-[#559E47] block bg-slate-100 flex items-center justify-center">
                        <svg class="w-8 h-8 text-slate-300" fill="currentColor" viewBox="0 0 24 24"><path d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM16.002 8.999a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                    </div>
                </div>
            </div>

            <!-- Identity Details -->
            <div class="px-[12px] w-full flex flex-col flex-grow">
                <div class="text-center w-full mb-2 flex flex-col items-center shrink-0">
                    <h2 class="text-[12px] font-extrabold text-slate-900 m-0 mb-[3px] uppercase leading-tight break-words line-clamp-2 w-full"
                        style="display: -webkit-box; -webkit-box-orient: vertical; overflow: hidden;"
                        :title="card.full_name">
                        {{ card.full_name }}
                    </h2>
                    
                    <div class="flex flex-col items-center gap-0.5">
                        <span class="inline-block text-[9px] font-bold text-[#4CAF50] bg-green-50 border-[1px] border-green-200 px-2.5 py-[1px] rounded-full uppercase tracking-wider shrink-0">
                            {{ card.employee_id || '—' }}
                        </span>
                    </div>
                </div>
                <div v-if="card.expires_at" class="text-center w-full mb-2 flex flex-col items-center shrink-0 opacity-30 leading-tight my-auto">
                    <span class="text-lg uppercase font-bold">Temporary Access</span>
                    <span class="text-sm font-semibold">Expiry: {{ card.expires_at }}</span>
                </div>
                <!-- Attributes List -->
                <dl class="mt-auto w-full flex flex-col gap-1.5 shrink-0 pb-1.5">
                    <div class="flex justify-between items-end border-b border-dashed border-slate-200 pb-0.5">
                        <dt class="text-[7px] font-bold text-slate-500 uppercase m-0">Type</dt>
                        <dd class="text-[9px] font-bold text-slate-800 m-0 truncate text-right max-w-[60%]" :title="card.registration_type_label">
                            {{ card.registration_type_label || '—' }}
                        </dd>
                    </div>
                    <div v-if="card.affiliation" class="flex justify-between items-end border-b border-dashed border-slate-200 pb-0.5">
                        <dt class="text-[7px] font-bold text-slate-500 uppercase m-0">Affiliation</dt>
                        <dd class="text-[9px] font-bold text-slate-800 m-0 truncate text-right max-w-[65%]" :title="card.affiliation">
                            {{ card.affiliation }}
                        </dd>
                    </div>
                    <div class="flex justify-between items-end pb-0.5" :class="{ 'border-b border-dashed border-slate-200': card.expires_at }">
                        <dt class="text-[7px] font-bold text-slate-500 uppercase m-0">Issued</dt>
                        <dd class="text-[9px] font-bold text-slate-800 m-0 truncate text-right max-w-[60%]">
                            {{ card.date_issued || '—' }}
                        </dd>
                    </div>
                    <div v-if="card.expires_at" class="flex justify-between items-end pb-0.5">
                        <dt class="text-[7px] font-bold text-red-500 uppercase m-0">Expires</dt>
                        <dd class="text-[9px] font-bold text-red-600 m-0 truncate text-right max-w-[60%]">
                            {{ card.expires_at }}
                        </dd>
                    </div>
                </dl>
            </div>
        </div>

        <!-- Footer -->
        <footer class="p-[6px_10px] bg-slate-50 relative z-10 flex flex-col">
            <div class="flex items-center justify-between my-[2px]">
                <div class="flex flex-col gap-[1px]">
                    <h3 class="text-[8px] m-0 font-bold text-[#4CAF50] text-left leading-tight">
                        Biotech for Better Crop for Better Lives
                    </h3>
                    <div class="text-[6px] text-left text-slate-800 opacity-70 leading-[5px] max-w-[80%]">
                        dacbc.philrice.gov.ph
                    </div>
                </div>
                <div class="flex gap-[3px]">
                    <img src="/imgs/da_bpo.png" class="h-[20px] w-auto" />
                    <img src="/imgs/bagong_pilipinas.png" class="h-[20px] w-auto" />
                </div>
            </div>
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
