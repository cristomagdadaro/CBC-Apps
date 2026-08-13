<script>
import { ExternalLink, Menu } from 'lucide-vue-next'
import {
    LuFacebook,
    LuGlobe,
    LuLayoutGrid,
    LuMail,
    LuMapPin,
    LuShield,
    LuStar,
    LuUser,
    LuX,
} from '@/Components/Icons'

export default {
    name: 'SocialLinks',
    components: {
        ExternalLink,
        LuFacebook,
        LuGlobe,
        LuLayoutGrid,
        LuMail,
        LuMapPin,
        LuShield,
        LuStar,
        LuUser,
        LuX,
        Menu,
    },
    props: {
        canLogin: Boolean,
        canRegister: Boolean,
        laravelVersion: String,
        phpVersion: String,
    },
    data() {
        return {
            open: false,
            isHovered: false,
            showPrivacyNotice: false,
            hasReachedBottom: false,
            isMobileVisible: true,
            inactivityTimer: null,
            privacySections: [
                {
                    heading: '1. Personal Data Collected',
                    paragraphs: [
                        'We collect only the data necessary for the secure and efficient operation of the DA-CBC, including:',
                    ],
                    items: [
                        {
                            label: 'Account and Profile Data',
                            text: 'Full name, institutional affiliation, office address, designation, professional email address, and contact number.',
                        },
                        {
                            label: 'Research and System Data',
                            text: 'Data related to biotechnology research submissions, germplasm requests, or project monitoring as required by CBC protocols.',
                        },
                        {
                            label: 'System Usage Data',
                            text: 'IP addresses, login timestamps, browser types, and audit logs necessary for security monitoring and system integrity.',
                        },
                        {
                            label: 'Communications',
                            text: 'Information provided through helpdesk tickets, technical support requests, or official inquiries.',
                        },
                    ],
                },
                {
                    heading: '2. Purpose and Legal Basis for Processing',
                    paragraphs: [
                        'The DA-CBC processes personal data for the following official purposes:',
                    ],
                    items: [
                        {
                            label: 'Access Management',
                            text: 'To verify identity, provide secure authentication, and manage user roles within the portal.',
                        },
                        {
                            label: 'Service Delivery',
                            text: 'To facilitate the processing of biotechnology-related applications, research tracking, and resource management.',
                        },
                        {
                            label: 'Security and Audit',
                            text: 'To maintain a secure environment, prevent unauthorized access, and fulfill government auditing requirements.',
                        },
                        {
                            label: 'Statutory Compliance',
                            text: 'To comply with Department of Agriculture policies, Executive Orders, and other legal obligations.',
                        },
                    ],
                    closing: 'Processing is based on the fulfillment of a legal mandate, compliance with government requirements, and the legitimate interests of the DA-CBC in advancing agricultural biotechnology research.',
                },
                {
                    heading: '3. Data Sharing and Disclosure',
                    paragraphs: [
                        'We do not sell or lease personal data to third parties. We may share data only under the following circumstances:',
                    ],
                    items: [
                        {
                            label: 'Internal DA Units',
                            text: 'With relevant offices within the Department of Agriculture for official reporting or project verification.',
                        },
                        {
                            label: 'Service Providers',
                            text: 'With authorized ICT service providers or cloud hosting partners (e.g., PhilRice or DICT) under strict confidentiality and security agreements.',
                        },
                        {
                            label: 'Legal Mandate',
                            text: 'With authorized government agencies (e.g., COA, NPC) when required by law or lawful court orders.',
                        },
                    ],
                },
                {
                    heading: '4. Data Retention',
                    paragraphs: [
                        'The DA-CBC retains personal data only for as long as necessary to:',
                    ],
                    items: [
                        {
                            label: 'Purpose Fulfillment',
                            text: 'Fulfill the purposes stated in this notice.',
                        },
                        {
                            label: 'Government Records Compliance',
                            text: 'Comply with the National Archives of the Philippines (NAP) RA 9470 regarding government records.',
                        },
                        {
                            label: 'Audit and Legal Obligations',
                            text: 'Meet specific audit or legal requirements.',
                        },
                    ],
                    closing: 'Data shall be securely disposed of or anonymized once the retention period has lapsed.',
                },
                {
                    heading: '5. Data Security',
                    paragraphs: [
                        'We implement organizational, physical, and technical security measures to protect your data against unauthorized access, alteration, or disclosure. These include:',
                    ],
                    items: [
                        {
                            label: 'Encryption',
                            text: 'Use of SSL/TLS (HTTPS) for data in transit.',
                        },
                        {
                            label: 'Access Controls',
                            text: 'Strict role-based access to the portal database.',
                        },
                        {
                            label: 'Monitoring',
                            text: 'Continuous logging of system activities to detect potential security breaches.',
                        },
                    ],
                },
                {
                    heading: '6. Your Rights as a Data Subject',
                    paragraphs: [
                        'As a registered user of the DA-CBC, you have the right to:',
                    ],
                    items: [
                        {
                            label: 'Be Informed',
                            text: 'Know how your data is being used.',
                        },
                        {
                            label: 'Access',
                            text: 'Request a copy of the personal data we hold about you.',
                        },
                        {
                            label: 'Correct',
                            text: 'Update inaccurate or outdated information in your profile.',
                        },
                        {
                            label: 'Object/Erasure',
                            text: 'Request the suspension or removal of your data, subject to legal and administrative limitations for government records.',
                        },
                        {
                            label: 'Lodge a Complaint',
                            text: 'Contact the National Privacy Commission (NPC) if you feel your rights have been violated.',
                        },
                    ],
                },
                {
                    heading: '7. Changes to this Notice',
                    paragraphs: [
                        'The DA-CBC reserves the right to update this notice to align with new government circulars or system upgrades. All changes will be reflected on this page with an updated "Last Revised" date.',
                    ],
                },
                {
                    heading: '8. Contact Information',
                    paragraphs: [
                        'For privacy concerns, requests for data correction, or complaints regarding the DA-CBC, please contact our Data Protection Officer (DPO):',
                    ],
                },
            ],
        }
    },
    methods: {
        toggle() {
            this.open = !this.open
        },
        close() {
            this.open = false
        },
        openPrivacyNotice() {
            this.showPrivacyNotice = true
        },
        closePrivacyNotice(agree = false) {
            this.showPrivacyNotice = false
            if (agree) {
                const expiry = Date.now() + (30 * 24 * 60 * 60 * 1000)
                localStorage.setItem('privacyNoticeDismissed', expiry)
            }
        },
        handleDisagree() {
            window.close()
            window.location.href = 'about:blank'
        },
        handleScroll() {
            const el = this.$refs.privacyContent

            if (!el) return

            const threshold = 10 // px tolerance

            const isBottom =
                el.scrollTop + el.clientHeight >= el.scrollHeight - threshold

            if (isBottom) {
                this.hasReachedBottom = true
            }
        },
        resetInactivityTimer() {
            this.isMobileVisible = true
            if (this.inactivityTimer) clearTimeout(this.inactivityTimer)

            if (this.open) return

            this.inactivityTimer = setTimeout(() => {
                if (!this.open) {
                    this.isMobileVisible = false
                }
            }, 5000)
        },
    },

    watch: {
        open(isOpen) {
            if (isOpen) {
                this.isMobileVisible = true
                if (this.inactivityTimer) clearTimeout(this.inactivityTimer)
            } else {
                this.resetInactivityTimer()
            }
        },
    },

    mounted() {
        if (!this.$page.props.auth.user) {
            const dismissed = localStorage.getItem('privacyNoticeDismissed')

            if (!dismissed || Date.now() > dismissed) {
                this.showPrivacyNotice = true
            }
        }

        this.resetInactivityTimer()
        window.addEventListener("scroll", this.resetInactivityTimer, { passive: true, capture: true })
    },

    beforeUnmount() {
        if (this.inactivityTimer) clearTimeout(this.inactivityTimer)
        window.removeEventListener("scroll", this.resetInactivityTimer, { capture: true })
    },
}
</script>

<template>
    <!-- Backdrop for mobile when open -->
    <transition enter-active-class="transition-opacity duration-300" enter-from-class="opacity-0"
        enter-to-class="opacity-100" leave-active-class="transition-opacity duration-200" leave-from-class="opacity-100"
        leave-to-class="opacity-0">
        <div v-if="open" class="fixed inset-0 bg-black/40 z-[999] md:hidden" @click="close" />
    </transition>

    <!-- Main Container -->
    <div data-guide="social-links" class="fixed bottom-3.5 right-3.5 sm:bottom-6 sm:right-6 z-[1000] flex flex-col items-end gap-2 sm:gap-3">
        <!-- Desktop View: Floating Pill -->
        <div class="hidden md:flex items-center gap-1 bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-800 rounded-full px-2 py-1.5 shadow-xl transition-all duration-300 hover:scale-[1.02]"
            @mouseenter="isHovered = true" @mouseleave="isHovered = false">
            <!-- Auth Links -->
            <template v-if="$page.props.auth.user">
                <Link data-guide='social-links-dashboard' :href="route('dashboard')"
                    class="group relative p-2.5 flex items-center gap-2 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300 transition-all duration-200 hover:text-AB dark:hover:text-white"
                    title="Dashboard">
                    <LuLayoutGrid class="w-5 h-5" />
                    Dashboard
                    <span
                        class="absolute -top-10 left-1/2 -translate-x-1/2 px-2 py-1 bg-gray-900 dark:bg-white text-white dark:text-gray-900 text-xs rounded-md opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap">
                        Dashboard
                    </span>
                </Link>
                <Link :href="route('logout')" method="post" as="button"
                    class="group relative p-2.5 flex items-center gap-2 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300 transition-all duration-200 hover:text-AB dark:hover:text-white"
                    title="Logout">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                    Logout
                    <span
                        class="absolute -top-10 left-1/2 -translate-x-1/2 px-2 py-1 bg-gray-900 dark:bg-white text-white dark:text-gray-900 text-xs rounded-md opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap">
                        Logout
                    </span>
                </Link>
            </template>
            <template v-else>
                <Link :href="route('login')" data-guide='social-links-login'
                    class="group relative p-2.5 flex items-center gap-2 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300 transition-all duration-200 hover:text-AB dark:hover:text-white"
                    title="Login">
                    <LuUser class="w-5 h-5" />
                    Login
                    <span
                        class="absolute -top-10 left-1/2 -translate-x-1/2 px-2 py-1 bg-gray-900 dark:bg-white text-white dark:text-gray-900 text-xs rounded-md opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap">
                        Login
                    </span>
                </Link>
                <Link v-if="canRegister" :href="route('register')" data-guide='social-links-register'
                    class="group relative p-2.5 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300 transition-all duration-200 hover:text-AB dark:hover:text-white"
                    title="Register">
                    <div
                        class="absolute -top-10 left-1/2 -translate-x-1/2 px-2 py-1 bg-gray-900 dark:bg-white text-white dark:text-gray-900 text-xs rounded-md opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap">
                        Register
                    </div>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                </Link>
            </template>

            <div class="w-px h-6 bg-gray-300 dark:bg-gray-600 mx-1" />

            <!-- External Links -->
            <a href="https://dacbc.philrice.gov.ph/" target="_blank" rel="noopener noreferrer"
                class="group relative p-2.5 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300 transition-all duration-200 hover:text-AB dark:hover:text-white">
                <LuGlobe data-guide='social-links-corporate-website' class="w-5 h-5" />
                <span
                    class="absolute -top-10 left-1/2 -translate-x-1/2 px-2 py-1 bg-gray-900 dark:bg-white text-white dark:text-gray-900 text-xs rounded-md opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none">
                    Corporate Website
                </span>
            </a>

            <a href="https://cbc360tour.philrice.gov.ph/" target="_blank" rel="noopener noreferrer"
                class="group relative p-2.5 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300 transition-all duration-200 hover:text-AB dark:hover:text-white">
                <LuStar data-guide='social-links-360tour' class="w-5 h-5" />
                <span
                    class="absolute -top-10 left-1/2 -translate-x-1/2 px-2 py-1 bg-gray-900 dark:bg-white text-white dark:text-gray-900 text-xs rounded-md opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none">
                    Virtual Tour
                </span>
            </a>

            <a href="https://pin.philrice.gov.ph/" target="_blank" rel="noopener noreferrer"
                class="group relative p-2.5 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300 transition-all duration-200 hover:text-AB dark:hover:text-white">
                <LuMapPin data-guide='social-links-pin' class="w-5 h-5" />
                <span
                    class="absolute -top-10 left-1/2 -translate-x-1/2 px-2 py-1 bg-gray-900 dark:bg-white text-white dark:text-gray-900 text-xs rounded-md opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none">
                    PIN System
                </span>
            </a>

            <a href="https://www.facebook.com/DACropBiotechCenter" target="_blank" rel="noopener noreferrer"
                class="group relative p-2.5 rounded-full hover:bg-blue-50 dark:hover:bg-blue-900/30 text-gray-600 dark:text-gray-300 transition-all duration-200 hover:text-blue-600 dark:hover:text-blue-400">
                <LuFacebook data-guide='social-links-facebook' class="w-5 h-5" />
                <span
                    class="absolute -top-10 left-1/2 -translate-x-1/2 px-2 py-1 bg-gray-900 dark:bg-white text-white dark:text-gray-900 text-xs rounded-md opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none">
                    Facebook
                </span>
            </a>

            <a href="mailto:cropbiotechcenter@gmail.com"
                class="group relative p-2.5 rounded-full hover:bg-red-50 dark:hover:bg-red-900/30 text-gray-600 dark:text-gray-300 transition-all duration-200 hover:text-red-500 dark:hover:text-red-400">
                <LuMail data-guide='social-links-email' class="w-5 h-5" />
                <span
                    class="absolute -top-10 left-1/2 -translate-x-1/2 px-2 py-1 bg-gray-900 dark:bg-white text-white dark:text-gray-900 text-xs rounded-md opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none">
                    Email Us
                </span>
            </a>

            <button type="button" @click="openPrivacyNotice"
                class="group relative p-2.5 rounded-full hover:bg-emerald-50 dark:hover:bg-emerald-900/30 text-gray-600 dark:text-gray-300 transition-all duration-200 hover:text-emerald-600 dark:hover:text-emerald-400"
                title="Data Privacy Notice">
                <LuShield data-guide='privacy-notice' class="w-5 h-5" />
                <span
                    class="absolute -top-10 left-1/2 -translate-x-1/2 px-2 py-1 bg-gray-900 dark:bg-white text-white dark:text-gray-900 text-xs rounded-md opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap">
                    Data Privacy Notice
                </span>
            </button>
        </div>

        <!-- Mobile View: Floating Action Button -->
        <div 
            class="md:hidden flex flex-col items-end gap-2 transition-all duration-500 ease-in-out"
            :class="isMobileVisible || open ? 'translate-y-0 opacity-100' : 'translate-y-12 opacity-0 pointer-events-none'"
        >
            <!-- Menu Panel -->
            <transition enter-active-class="transition-all duration-300 ease-out"
                enter-from-class="opacity-0 translate-y-8 scale-95" enter-to-class="opacity-100 translate-y-0 scale-100"
                leave-active-class="transition-all duration-200 ease-in"
                leave-from-class="opacity-100 translate-y-0 scale-100"
                leave-to-class="opacity-0 translate-y-8 scale-95">
                <div v-if="open"
                    class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-gray-200 dark:border-slate-800 overflow-hidden max-w-[calc(100vw-2rem)] min-w-[260px] mb-1.5">
                    <!-- Header -->
                    <div class="bg-gradient-to-r from-lime-600 to-emerald-600 px-3.5 py-2.5 flex items-center justify-between">
                        <span class="text-white font-semibold text-xs flex items-center gap-2">
                            <LuGlobe class="w-4 h-4" />
                            Quick Links
                        </span>
                        <button @click="close"
                            class="text-white/80 hover:text-white transition-colors p-1 rounded-full hover:bg-white/20">
                            <LuX class="w-4 h-4" />
                        </button>
                    </div>

                    <!-- Auth Section -->
                    <div class="p-2 space-y-1">
                        <template v-if="$page.props.auth.user">
                            <Link :href="route('dashboard')" data-guide='social-links-dashboard'
                                class="flex items-center gap-2.5 px-2.5 py-2 rounded-xl text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all duration-200 group">
                                <div class="w-7 h-7 rounded-lg bg-slate-100 dark:bg-slate-800 flex items-center justify-center group-hover:bg-lime-500/20 group-hover:text-lime-600 transition-colors">
                                    <LuLayoutGrid class="w-3.5 h-3.5" />
                                </div>
                                <div class="flex-1">
                                    <span class="text-xs font-semibold">Dashboard</span>
                                    <p class="text-[0.68rem] text-slate-500 dark:text-slate-400">
                                        Go to dashboard
                                    </p>
                                </div>
                            </Link>
                            <Link :href="route('logout')" method="post" as="button"
                                class="w-full text-left flex items-center gap-2.5 px-2.5 py-2 rounded-xl text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all duration-200 group">
                                <div class="w-7 h-7 rounded-lg bg-slate-100 dark:bg-slate-800 flex items-center justify-center group-hover:bg-red-500/20 group-hover:text-red-600 transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                                </div>
                                <div class="flex-1">
                                    <span class="text-xs font-semibold">Logout</span>
                                    <p class="text-[0.68rem] text-slate-500 dark:text-slate-400">
                                        Sign out of account
                                    </p>
                                </div>
                            </Link>
                        </template>
                        <template v-else>
                            <Link :href="route('login')" data-guide='social-links-login'
                                class="flex items-center gap-2.5 px-2.5 py-2 rounded-xl text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all duration-200 group">
                                <div
                                    class="w-7 h-7 rounded-lg bg-slate-100 dark:bg-slate-800 flex items-center justify-center group-hover:bg-lime-500/20 group-hover:text-lime-600 transition-colors">
                                    <LuUser class="w-3.5 h-3.5" />
                                </div>
                                <div class="flex-1">
                                    <span class="text-xs font-semibold">Login</span>
                                    <p class="text-[0.68rem] text-slate-500 dark:text-slate-400">
                                        Access your account
                                    </p>
                                </div>
                            </Link>
                            <Link v-if="canRegister" :href="route('register')" data-guide='social-links-register'
                                class="flex items-center gap-2.5 px-2.5 py-2 rounded-xl text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all duration-200 group">
                                <div
                                    class="w-7 h-7 rounded-lg bg-slate-100 dark:bg-slate-800 flex items-center justify-center group-hover:bg-lime-500/20 group-hover:text-lime-600 transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                                </div>
                                <div class="flex-1">
                                    <span class="text-xs font-semibold">Register</span>
                                    <p class="text-[0.68rem] text-slate-500 dark:text-slate-400">
                                        Create a new account
                                    </p>
                                </div>
                            </Link>
                        </template>
                    </div>

                    <div class="h-px bg-slate-200 dark:bg-slate-800 mx-2" />

                    <!-- External Links -->
                    <div class="p-2 space-y-1">
                        <a data-guide='social-links-facebook' href="https://www.facebook.com/DACropBiotechCenter" target="_blank" rel="noopener noreferrer"
                            class="flex items-center gap-2.5 px-2.5 py-2 rounded-xl text-slate-700 dark:text-slate-200 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-all duration-200 group">
                            <div
                                class="w-7 h-7 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center text-blue-600 group-hover:bg-blue-600 group-hover:text-white transition-all">
                                <LuFacebook class="w-3.5 h-3.5" />
                            </div>
                            <div class="flex-1">
                                <span class="text-xs font-semibold">Facebook</span>
                                <ExternalLink class="w-3 h-3 text-slate-400 inline-block ml-1" />
                            </div>
                        </a>

                        <a data-guide='social-links-email' href="mailto:cropbiotechcenter@gmail.com"
                            class="flex items-center gap-2.5 px-2.5 py-2 rounded-xl text-slate-700 dark:text-slate-200 hover:bg-rose-50 dark:hover:bg-rose-900/20 transition-all duration-200 group">
                            <div
                                class="w-7 h-7 rounded-lg bg-rose-100 dark:bg-rose-900/30 flex items-center justify-center text-rose-600 group-hover:bg-rose-500 group-hover:text-white transition-all">
                                <LuMail class="w-3.5 h-3.5" />
                            </div>
                            <div class="flex-1">
                                <span class="text-xs font-semibold">Email</span>
                                <span
                                    class="text-[0.68rem] text-slate-500 dark:text-slate-400 block">cropbiotechcenter@gmail.com</span>
                            </div>
                        </a>

                        <a data-guide='social-links-corporate-website' href="https://dacbc.philrice.gov.ph/" target="_blank" rel="noopener noreferrer"
                            class="flex items-center gap-2.5 px-2.5 py-2 rounded-xl text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all duration-200 group">
                            <div
                                class="w-7 h-7 rounded-lg bg-slate-100 dark:bg-slate-800 flex items-center justify-center group-hover:bg-lime-500/20 group-hover:text-lime-600 transition-colors">
                                <LuGlobe class="w-3.5 h-3.5" />
                            </div>
                            <div class="flex-1">
                                <span class="text-xs font-semibold">Corporate Website</span>
                                <ExternalLink class="w-3 h-3 text-slate-400 inline-block ml-1" />
                            </div>
                        </a>

                        <a data-guide='social-links-360tour' href="https://cbc360tour.philrice.gov.ph/" target="_blank" rel="noopener noreferrer"
                            class="flex items-center gap-2.5 px-2.5 py-2 rounded-xl text-slate-700 dark:text-slate-200 hover:bg-amber-50 dark:hover:bg-amber-900/20 transition-all duration-200 group">
                            <div
                                class="w-7 h-7 rounded-lg bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center text-amber-600 group-hover:bg-amber-500 group-hover:text-white transition-all">
                                <LuStar class="w-3.5 h-3.5" />
                            </div>
                            <div class="flex-1">
                                <span class="text-xs font-semibold">Virtual Tour</span>
                                <ExternalLink class="w-3 h-3 text-slate-400 inline-block ml-1" />
                            </div>
                        </a>

                        <a data-guide='social-links-pin' href="https://pin.philrice.gov.ph/" target="_blank" rel="noopener noreferrer"
                            class="flex items-center gap-2.5 px-2.5 py-2 rounded-xl text-slate-700 dark:text-slate-200 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 transition-all duration-200 group">
                            <div
                                class="w-7 h-7 rounded-lg bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center text-emerald-600 group-hover:bg-emerald-500 group-hover:text-white transition-all">
                                <LuMapPin class="w-3.5 h-3.5" />
                            </div>
                            <div class="flex-1">
                                <span class="text-xs font-semibold">PIN System</span>
                                <p class="text-[0.68rem] text-slate-500 dark:text-slate-400">
                                    Plant Breeders & Innovators Network
                                </p>
                            </div>
                        </a>

                        <button type="button" @click="openPrivacyNotice" data-guide='social-links-privacy-notice'
                            class="flex w-full items-center gap-2.5 px-2.5 py-2 rounded-xl text-slate-700 dark:text-slate-200 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 transition-all duration-200 group">
                            <div data-guide='privacy-notice'
                                class="w-7 h-7 rounded-lg bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center text-emerald-600 group-hover:bg-emerald-500 group-hover:text-white transition-all">
                                <LuShield class="w-3.5 h-3.5" />
                            </div>
                            <div class="flex-1 text-left">
                                <span class="text-xs font-semibold">Terms & Privacy Policy</span>
                                <p class="text-[0.68rem] text-slate-500 dark:text-slate-400">
                                    View DA-CBC official policies
                                </p>
                            </div>
                        </button>
                    </div>
                </div>
            </transition>

            <!-- FAB Toggle Button -->
            <button type="button" @click="toggle"
                data-guide='social-links'
                class="w-10 h-10 sm:w-14 sm:h-14 rounded-full bg-slate-900 dark:bg-slate-800 text-white border border-slate-700 dark:border-slate-700 shadow-md sm:shadow-lg flex items-center justify-center hover:scale-105 active:scale-95 transition-all duration-300 focus:outline-none opacity-85 hover:opacity-100"
                :class="{ 'rotate-90 opacity-100 bg-lime-600 text-white': open }" aria-label="Toggle quick links menu">
                <Menu v-if="!open" class="w-5 h-5 sm:w-6 sm:h-6 text-lime-400" />
                <LuX v-else class="w-5 h-5 sm:w-6 sm:h-6" />
            </button>
        </div>
    </div>

    <DialogModal :show="showPrivacyNotice" max-width="2xl" @close="closePrivacyNotice" :closeable="false">
        <template #title>
            <div class="flex items-center gap-3 px-5 pt-5">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700">
                    <LuShield class="h-5 w-5" />
                </div>
                <div>
                    <p class="text-base font-semibold text-gray-900">Terms & Privacy Policy</p>
                    <p class="text-sm text-gray-500">DA-Crop Biotechnology Center</p>
                </div>
            </div>
        </template>

        <template #content>
            <div class="px-5 pb-2 text-sm leading-6 text-gray-700">
                <p>
                    By continuing to use this platform, you acknowledge that you have read and agreed to the 
                    <a href="https://dacbc.philrice.gov.ph/about-us/privacy-policy/" target="_blank" rel="noopener noreferrer" class="font-medium text-emerald-700 hover:text-emerald-800 underline">Privacy Policy</a> 
                    and 
                    <a href="https://dacbc.philrice.gov.ph/about-us/terms-and-conditions" target="_blank" rel="noopener noreferrer" class="font-medium text-emerald-700 hover:text-emerald-800 underline">Terms & Conditions</a>
                    of the DA-Crop Biotechnology Center.
                </p>
            </div>
        </template>

        <template #footer>
            <div class="flex w-full items-center justify-end gap-3">
                <button type="button" @click="handleDisagree"
                    class="rounded-lg px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 transition-colors select-none">
                    Disagree
                </button>
                <button type="button" @click="closePrivacyNotice(true)"
                    class="rounded-lg px-4 py-2 text-sm font-medium text-white transition-colors select-none bg-emerald-600 hover:bg-emerald-700">
                    I Agree & Continue
                </button>
            </div>
        </template>
    </DialogModal>
</template>

<style scoped>
/* Custom color variable - ensure AB is defined in your Tailwind config */
</style>
