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
    },

    mounted() {
        if (!this.$page.props.auth.user) {
            const dismissed = localStorage.getItem('privacyNoticeDismissed')

            if (!dismissed || Date.now() > dismissed) {
                this.showPrivacyNotice = true
            }
        }
    }
}
</script>

<template>
    <!-- Backdrop for mobile when open -->
    <transition enter-active-class="transition-opacity duration-300" enter-from-class="opacity-0"
        enter-to-class="opacity-100" leave-active-class="transition-opacity duration-200" leave-from-class="opacity-100"
        leave-to-class="opacity-0">
        <div v-if="open" class="fixed inset-0 bg-black/20 backdrop-blur-sm z-[999] md:hidden" @click="close" />
    </transition>

    <!-- Main Container -->
    <div data-guide="social-links" class="fixed bottom-6 right-6 z-[1000] flex flex-col items-end gap-3">
        <!-- Desktop View: Floating Pill -->
        <div class="hidden md:flex items-center gap-1 bg-white dark:bg-gray-800 border border-gray-200/50 dark:border-gray-700/50 rounded-full px-2 py-1.5 shadow-xl shadow-gray-900/10 dark:shadow-black/30 backdrop-blur-md bg-opacity-90 dark:bg-opacity-90 transition-all duration-300 hover:shadow-2xl hover:shadow-gray-900/15 hover:scale-[1.02]"
            @mouseenter="isHovered = true" @mouseleave="isHovered = false">
            <!-- Auth Links -->
            <Link v-if="$page.props.auth.user" data-guide='social-links-dashboard' :href="route('dashboard')"
                class="group relative p-2.5 flex items-center gap-2 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300 transition-all duration-200 hover:text-AB dark:hover:text-white">
            <LuLayoutGrid class="w-5 h-5" />
            Dashboard
            </Link>
            <Link v-else :href="route('login')" data-guide='social-links-login'
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
            <LuUser class="w-5 h-5" />
            <span
                class="absolute -top-10 left-1/2 -translate-x-1/2 px-2 py-1 bg-gray-900 dark:bg-white text-white dark:text-gray-900 text-xs rounded-md opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none">
                Register
            </span>
            </Link>

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
        <div class="md:hidden flex flex-col items-end gap-3">
            <!-- Menu Panel -->
            <transition enter-active-class="transition-all duration-300 ease-out"
                enter-from-class="opacity-0 translate-y-8 scale-95" enter-to-class="opacity-100 translate-y-0 scale-100"
                leave-active-class="transition-all duration-200 ease-in"
                leave-from-class="opacity-100 translate-y-0 scale-100"
                leave-to-class="opacity-0 translate-y-8 scale-95">
                <div v-if="open"
                    class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-700 overflow-hidden min-w-[280px] mb-2">
                    <!-- Header -->
                    <div class="bg-gradient-to-r from-AB to-AB/80 px-4 py-3 flex items-center justify-between">
                        <span class="text-white font-semibold text-sm flex items-center gap-2">
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
                        <Link :href="route('login')" data-guide='social-links-login'
                            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition-all duration-200 group">
                            <div
                                class="w-8 h-8 rounded-lg bg-gray-100 dark:bg-gray-700 flex items-center justify-center group-hover:bg-AB/10 group-hover:text-AB transition-colors">
                                <LuUser class="w-4 h-4" />
                            </div>
                            <div class="flex-1">
                                <span class="text-sm font-medium">Login</span>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    Access your account
                                </p>
                            </div>
                        </Link>

                        <Link v-if="canRegister" :href="route('register')" data-guide='social-links-register'
                            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition-all duration-200 group">
                        <div
                            class="w-8 h-8 rounded-lg bg-gray-100 dark:bg-gray-700 flex items-center justify-center group-hover:bg-AB/10 group-hover:text-AB transition-colors">
                            <LuUser class="w-4 h-4" />
                        </div>
                        <div class="flex-1">
                            <span class="text-sm font-medium">Register</span>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                Create new account
                            </p>
                        </div>
                        </Link>
                    </div>

                    <div class="h-px bg-gray-200 dark:bg-gray-700 mx-2" />

                    <!-- External Links -->
                    <div class="p-2 space-y-1">
                        <a data-guide='social-links-facebook' href="https://www.facebook.com/DACropBiotechCenter" target="_blank" rel="noopener noreferrer"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-gray-700 dark:text-gray-200 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-all duration-200 group">
                            <div
                                class="w-8 h-8 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center text-blue-600 group-hover:bg-blue-600 group-hover:text-white transition-all">
                                <LuFacebook class="w-4 h-4" />
                            </div>
                            <div class="flex-1">
                                <span class="text-sm font-medium">Facebook</span>
                                <ExternalLink class="w-3 h-3 text-gray-400 inline-block ml-1" />
                            </div>
                        </a>

                        <a data-guide='social-links-email' href="mailto:cropbiotechcenter@gmail.com"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-gray-700 dark:text-gray-200 hover:bg-red-50 dark:hover:bg-red-900/20 transition-all duration-200 group">
                            <div
                                class="w-8 h-8 rounded-lg bg-red-100 dark:bg-red-900/30 flex items-center justify-center text-red-600 group-hover:bg-red-500 group-hover:text-white transition-all">
                                <LuMail class="w-4 h-4" />
                            </div>
                            <div class="flex-1">
                                <span class="text-sm font-medium">Email</span>
                                <span
                                    class="text-xs text-gray-500 dark:text-gray-400 block">cropbiotechcenter@gmail.com</span>
                            </div>
                        </a>

                        <a data-guide='social-links-corporate-website' href="https://dacbc.philrice.gov.ph/" target="_blank" rel="noopener noreferrer"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition-all duration-200 group">
                            <div
                                class="w-8 h-8 rounded-lg bg-gray-100 dark:bg-gray-700 flex items-center justify-center group-hover:bg-AB/10 group-hover:text-AB transition-colors">
                                <LuGlobe class="w-4 h-4" />
                            </div>
                            <div class="flex-1">
                                <span class="text-sm font-medium">Corporate Website</span>
                                <ExternalLink class="w-3 h-3 text-gray-400 inline-block ml-1" />
                            </div>
                        </a>

                        <a data-guide='social-links-360tour' href="https://cbc360tour.philrice.gov.ph/" target="_blank" rel="noopener noreferrer"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-gray-700 dark:text-gray-200 hover:bg-amber-50 dark:hover:bg-amber-900/20 transition-all duration-200 group">
                            <div
                                class="w-8 h-8 rounded-lg bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center text-amber-600 group-hover:bg-amber-500 group-hover:text-white transition-all">
                                <LuStar class="w-4 h-4" />
                            </div>
                            <div class="flex-1">
                                <span class="text-sm font-medium">Virtual Tour</span>
                                <ExternalLink class="w-3 h-3 text-gray-400 inline-block ml-1" />
                            </div>
                        </a>

                        <a data-guide='social-links-pin' href="https://pin.philrice.gov.ph/" target="_blank" rel="noopener noreferrer"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-gray-700 dark:text-gray-200 hover:bg-green-50 dark:hover:bg-green-900/20 transition-all duration-200 group">
                            <div
                                class="w-8 h-8 rounded-lg bg-green-100 dark:bg-green-900/30 flex items-center justify-center text-green-600 group-hover:bg-green-500 group-hover:text-white transition-all">
                                <LuMapPin class="w-4 h-4" />
                            </div>
                            <div class="flex-1">
                                <span class="text-sm font-medium">PIN System</span>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    Plant Breeders & Innovators Network
                                </p>
                            </div>
                        </a>

                        <button type="button" @click="openPrivacyNotice" data-guide='social-links-privacy-notice'
                            class="flex w-full items-center gap-3 px-3 py-2.5 rounded-xl text-gray-700 dark:text-gray-200 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 transition-all duration-200 group">
                            <div data-guide='privacy-notice'
                                class="w-8 h-8 rounded-lg bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center text-emerald-600 group-hover:bg-emerald-500 group-hover:text-white transition-all">
                                <LuShield class="w-4 h-4" />
                            </div>
                            <div class="flex-1 text-left">
                                <span class="text-sm font-medium">Data Privacy Notice</span>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    Learn how OneCBC handles personal data
                                </p>
                            </div>
                        </button>
                    </div>
                </div>
            </transition>

            <!-- FAB Toggle Button -->
            <button type="button" @click="toggle"
                data-guide='social-links'
                class="w-14 h-14 rounded-full bg-AB text-white shadow-lg shadow-AB/30 flex items-center justify-center hover:shadow-xl hover:shadow-AB/40 hover:scale-105 active:scale-95 transition-all duration-300 focus:outline-none focus:ring-4 focus:ring-AB/20"
                :class="{ 'rotate-90': open }" aria-label="Toggle quick links menu">
                <Menu v-if="!open" class="w-6 h-6" />
                <LuX v-else class="w-6 h-6" />
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
                    <p class="text-base font-semibold text-gray-900">Data Privacy Notice</p>
                    <p class="text-sm text-gray-500">DA-Crop Biotechnology Center</p>
                </div>
            </div>
        </template>

        <template #content>
            <div ref="privacyContent" @scroll="handleScroll" class="max-h-[70vh] space-y-6 overflow-y-auto px-5 text-sm leading-6 text-gray-700 leading-snug">
                <p>
                    This Data Privacy Notice is issued by the Department of Agriculture – Crop Biotechnology Center
                    (DA-CBC) pursuant to the <a href="https://privacy.gov.ph/data-privacy-act/"
                        class="text-emerald-700 hover:text-emerald-800">Data Privacy Act of 2012 (Republic Act No.
                        10173)</a>, its Implementing Rules and Regulations, and relevant issuances of the National
                    Privacy Commission. This notice explains how the DA-CBC collects, uses, stores, shares, and protects
                    personal data in the course of its operations.
                </p>

                <section v-for="section in privacySections" :key="section.heading" class="space-y-3">
                    <div>
                        <h3 class="text-base font-semibold text-gray-900">{{ section.heading }}</h3>
                    </div>

                    <p v-for="paragraph in section.paragraphs || []" :key="paragraph">
                        {{ paragraph }}
                    </p>

                    <div v-if="section.items?.length" class="space-y-2">
                        <p v-for="item in section.items" :key="`${section.heading}-${item.label}`">
                            <span class="font-semibold text-gray-900">{{ item.label }}:</span>
                            {{ item.text }}
                        </p>
                    </div>

                    <p v-if="section.closing">
                        {{ section.closing }}
                    </p>

                    <div v-if="section.heading === '8. Contact Information'"
                        class="rounded-2xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-gray-800 text-xs leading-tight">
                        <p class="font-semibold text-gray-900">DA-Crop Biotechnology Center</p>
                        <p>Barangay Maligaya, Science City of Muñoz, Nueva Ecija</p>
                        <p>
                            Email:
                            <a href="mailto:cropbiotechcenter@gmail.com"
                                class="font-medium text-emerald-700 hover:text-emerald-800">
                                cropbiotechcenter@gmail.com
                            </a>
                        </p>
                        <p>
                            Website:
                            <a href="https://onecbc.philrice.gov.ph" target="_blank" rel="noopener noreferrer"
                                class="font-medium text-emerald-700 hover:text-emerald-800">
                                https://onecbc.philrice.gov.ph
                            </a>
                        </p>
                    </div>
                </section>

                <div class="pt-4 text-xs font-medium text-gray-300">
                    Last Revised: March 18, 2026
                </div>
            </div>
        </template>

        <template #footer>
            <button type="button" :disabled="!hasReachedBottom" @click="closePrivacyNotice(true)"
                class="rounded-lg px-4 py-2 text-sm font-medium text-white transition-colors select-none" :class="hasReachedBottom
                    ? 'bg-emerald-600 hover:bg-emerald-700'
                    : 'bg-gray-400 cursor-not-allowed'">
                Has Read and Acknowledged
            </button>
        </template>
    </DialogModal>
</template>

<style scoped>
/* Custom color variable - ensure AB is defined in your Tailwind config */
</style>
