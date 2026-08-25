<template>
    <section
        class="personnel-id-card relative box-border flex h-[8.6cm] w-[5.4cm] flex-col overflow-hidden border border-slate-300 bg-white font-sans shadow-md print:border-slate-300 print:shadow-none"
        :style="cardStyle">
        <!-- Background Overlay -->
        <img
            src="/imgs/Overlay.png"
            alt="Overlay"
            class="pointer-events-none absolute right-0 top-0 z-0 h-[2cm] object-cover object-right-top mix-blend-multiply" />

        <!-- Header -->
        <header class="relative z-10 flex items-center gap-1 p-[10px_10px_6px_10px]">
            <img
                src="/imgs/logo-black.png"
                alt="CBC Logo"
                class="h-9 w-9 shrink-0 object-contain" />
            <div class="flex flex-1 flex-col leading-tight">
                <div class="text-[6px] leading-[7px] text-slate-800">Department of Agriculture</div>
                <div class="text-[8px] font-bold uppercase leading-[9px] tracking-tight text-[#4CAF50]">CROP BIOTECHNOLOGY CENTER</div>
                <div class="mt-[1px] text-[5px] leading-[6px] text-slate-600">DA-PhilRice Compound, Muñoz, Nueva Ecija</div>
            </div>
        </header>

        <!-- Body / Content -->
        <div class="relative z-10 flex flex-grow flex-col items-center">
            <!-- Photo Area with Accent -->
            <div class="relative my-3.5 flex w-full flex-col items-center justify-center">
                <div class="absolute top-1/2 z-0 h-[1.3cm] w-full -translate-y-1/2 bg-[#559E47]"></div>

                <div class="relative z-10 rounded-full border-[5px] border-white bg-white">
                    <img
                        v-if="card.photo_url"
                        :src="card.photo_url"
                        alt="Personnel Photo"
                        class="block h-[2.6cm] w-[2.6cm] rounded-full border-[2px] border-[#559E47] bg-slate-50 object-cover" />
                    <div
                        v-else
                        class="block flex h-[2.6cm] w-[2.6cm] items-center justify-center rounded-full border-[2px] border-[#559E47] bg-slate-100">
                        <svg
                            class="h-8 w-8 text-slate-300"
                            fill="currentColor"
                            viewBox="0 0 24 24">
                            <path d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM16.002 8.999a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Identity Details -->
            <div class="flex w-full flex-grow flex-col px-[12px]">
                <div class="mb-2 flex w-full shrink-0 flex-col items-center text-center">
                    <h2
                        class="m-0 mb-[3px] line-clamp-2 w-full break-words text-[12px] font-extrabold uppercase leading-tight text-slate-900"
                        style="display: -webkit-box; -webkit-box-orient: vertical; overflow: hidden"
                        :title="card.full_name">
                        {{ card.full_name }}
                    </h2>

                    <div class="flex flex-col items-center gap-0.5">
                        <span class="inline-block shrink-0 rounded-full border-[1px] border-green-200 bg-green-50 px-2.5 py-[1px] text-[9px] font-bold uppercase tracking-wider text-[#4CAF50]">
                            {{ card.employee_id || "—" }}
                        </span>
                    </div>
                </div>
                <div
                    v-if="card.expires_at"
                    class="my-auto mb-2 flex w-full shrink-0 flex-col items-center text-center leading-tight opacity-30">
                    <span class="text-lg font-bold uppercase">Temporary Access</span>
                    <span class="text-sm font-semibold">Expiry: {{ card.expires_at }}</span>
                </div>
                <!-- Attributes List -->
                <dl class="mt-auto flex w-full shrink-0 flex-col gap-1.5 pb-1.5">
                    <div class="flex items-end justify-between border-b border-dashed border-slate-200 pb-0.5">
                        <dt class="m-0 text-[7px] font-bold uppercase text-slate-500">Type</dt>
                        <dd
                            class="m-0 max-w-[60%] truncate text-right text-[9px] font-bold text-slate-800"
                            :title="card.registration_type_label">
                            {{ card.registration_type_label || "—" }}
                        </dd>
                    </div>
                    <div
                        v-if="card.affiliation"
                        class="flex items-end justify-between border-b border-dashed border-slate-200 pb-0.5">
                        <dt class="m-0 text-[7px] font-bold uppercase text-slate-500">Affiliation</dt>
                        <dd
                            class="m-0 max-w-[65%] truncate text-right text-[9px] font-bold text-slate-800"
                            :title="card.affiliation">
                            {{ card.affiliation }}
                        </dd>
                    </div>
                    <div
                        class="flex items-end justify-between pb-0.5"
                        :class="{ 'border-b border-dashed border-slate-200': card.expires_at }">
                        <dt class="m-0 text-[7px] font-bold uppercase text-slate-500">Issued</dt>
                        <dd class="m-0 max-w-[60%] truncate text-right text-[9px] font-bold text-slate-800">
                            {{ card.date_issued || "—" }}
                        </dd>
                    </div>
                    <div
                        v-if="card.expires_at"
                        class="flex items-end justify-between pb-0.5">
                        <dt class="m-0 text-[7px] font-bold uppercase text-red-500">Expires</dt>
                        <dd class="m-0 max-w-[60%] truncate text-right text-[9px] font-bold text-red-600">
                            {{ card.expires_at }}
                        </dd>
                    </div>
                </dl>
            </div>
        </div>

        <!-- Footer -->
        <footer class="relative z-10 flex flex-col bg-slate-50 p-[6px_10px]">
            <div class="my-[2px] flex items-center justify-between">
                <div class="flex flex-col gap-[1px]">
                    <h3 class="m-0 text-left text-[8px] font-bold leading-tight text-[#4CAF50]">Biotech for Better Crop for Better Lives</h3>
                    <div class="max-w-[80%] text-left text-[6px] leading-[5px] text-slate-800 opacity-70">dacbc.philrice.gov.ph</div>
                </div>
                <div class="flex gap-[3px]">
                    <img
                        src="/imgs/da_bpo.png"
                        class="h-[20px] w-auto" />
                    <img
                        src="/imgs/bagong_pilipinas.png"
                        class="h-[20px] w-auto" />
                </div>
            </div>
        </footer>
    </section>
</template>

<script>
export default {
    name: "PersonnelIdCard",

    props: {
        card: {
            type: Object,
            required: true,
        },

        cardStyle: {
            type: [Object, String],
            default() {
                return {};
            },
        },
    },
};
</script>
