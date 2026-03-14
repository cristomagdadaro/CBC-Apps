<script>
import {
    Menu,
    ExternalLink
} from 'lucide-vue-next';

export default {
    name: "SocialLinks",
    components: {
        ExternalLink,
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
        };
    },
    methods: {
        toggle() {
            this.open = !this.open;
        },
        close() {
            this.open = false;
        },
    },
};
</script>

<template>
    <!-- Backdrop for mobile when open -->
    <transition
        enter-active-class="transition-opacity duration-300"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition-opacity duration-200"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div
            v-if="open"
            class="fixed inset-0 bg-black/20 backdrop-blur-sm z-[999] md:hidden"
            @click="close"
        />
    </transition>

    <!-- Main Container -->
    <div class="fixed bottom-6 right-6 z-[1000] flex flex-col items-end gap-3">
        <!-- Desktop View: Floating Pill -->
        <div
            class="hidden md:flex items-center gap-1 bg-white dark:bg-gray-800 border border-gray-200/50 dark:border-gray-700/50 rounded-full px-2 py-1.5 shadow-xl shadow-gray-900/10 dark:shadow-black/30 backdrop-blur-md bg-opacity-90 dark:bg-opacity-90 transition-all duration-300 hover:shadow-2xl hover:shadow-gray-900/15 hover:scale-[1.02]"
            @mouseenter="isHovered = true"
            @mouseleave="isHovered = false"
        >
            <!-- Auth Links -->
            <Link
                :href="route('login')"
                class="group relative p-2.5 flex items-center gap-2 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300 transition-all duration-200 hover:text-AB dark:hover:text-white"
                title="Login"
            >
                <LuUser class="w-5 h-5" />
                Login
                <span
                    class="absolute -top-10 left-1/2 -translate-x-1/2 px-2 py-1 bg-gray-900 dark:bg-white text-white dark:text-gray-900 text-xs rounded-md opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap"
                >
                    Login
                </span>
            </Link>

            <Link
                v-if="canRegister"
                :href="route('register')"
                class="group relative p-2.5 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300 transition-all duration-200 hover:text-AB dark:hover:text-white"
                title="Register"
            >
                <LuUser class="w-5 h-5" />
                <span
                    class="absolute -top-10 left-1/2 -translate-x-1/2 px-2 py-1 bg-gray-900 dark:bg-white text-white dark:text-gray-900 text-xs rounded-md opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none"
                >
                    Register
                </span>
            </Link>

            <div class="w-px h-6 bg-gray-300 dark:bg-gray-600 mx-1" />

            <!-- External Links -->
            <a
                href="https://dacbc.philrice.gov.ph/"
                target="_blank"
                rel="noopener noreferrer"
                class="group relative p-2.5 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300 transition-all duration-200 hover:text-AB dark:hover:text-white"
            >
                <LuGlobe class="w-5 h-5" />
                <span
                    class="absolute -top-10 left-1/2 -translate-x-1/2 px-2 py-1 bg-gray-900 dark:bg-white text-white dark:text-gray-900 text-xs rounded-md opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none"
                >
                    Corporate Website
                </span>
            </a>

            <a
                href="https://cbc360tour.philrice.gov.ph/"
                target="_blank"
                rel="noopener noreferrer"
                class="group relative p-2.5 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300 transition-all duration-200 hover:text-AB dark:hover:text-white"
            >
                <LuStar class="w-5 h-5" />
                <span
                    class="absolute -top-10 left-1/2 -translate-x-1/2 px-2 py-1 bg-gray-900 dark:bg-white text-white dark:text-gray-900 text-xs rounded-md opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none"
                >
                    Virtual Tour
                </span>
            </a>

            <a
                href="https://pin.philrice.gov.ph/"
                target="_blank"
                rel="noopener noreferrer"
                class="group relative p-2.5 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300 transition-all duration-200 hover:text-AB dark:hover:text-white"
            >
                <LuMapPin class="w-5 h-5" />
                <span
                    class="absolute -top-10 left-1/2 -translate-x-1/2 px-2 py-1 bg-gray-900 dark:bg-white text-white dark:text-gray-900 text-xs rounded-md opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none"
                >
                    PIN System
                </span>
            </a>

            <a
                href="https://www.facebook.com/DACropBiotechCenter"
                target="_blank"
                rel="noopener noreferrer"
                class="group relative p-2.5 rounded-full hover:bg-blue-50 dark:hover:bg-blue-900/30 text-gray-600 dark:text-gray-300 transition-all duration-200 hover:text-blue-600 dark:hover:text-blue-400"
            >
                <LuFacebook class="w-5 h-5" />
                <span
                    class="absolute -top-10 left-1/2 -translate-x-1/2 px-2 py-1 bg-gray-900 dark:bg-white text-white dark:text-gray-900 text-xs rounded-md opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none"
                >
                    Facebook
                </span>
            </a>

            <a
                href="mailto:cropbiotechcenter@gmail.com"
                class="group relative p-2.5 rounded-full hover:bg-red-50 dark:hover:bg-red-900/30 text-gray-600 dark:text-gray-300 transition-all duration-200 hover:text-red-500 dark:hover:text-red-400"
            >
                <LuMail class="w-5 h-5" />
                <span
                    class="absolute -top-10 left-1/2 -translate-x-1/2 px-2 py-1 bg-gray-900 dark:bg-white text-white dark:text-gray-900 text-xs rounded-md opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none"
                >
                    Email Us
                </span>
            </a>
        </div>

        <!-- Mobile View: Floating Action Button -->
        <div class="md:hidden flex flex-col items-end gap-3">
            <!-- Menu Panel -->
            <transition
                enter-active-class="transition-all duration-300 ease-out"
                enter-from-class="opacity-0 translate-y-8 scale-95"
                enter-to-class="opacity-100 translate-y-0 scale-100"
                leave-active-class="transition-all duration-200 ease-in"
                leave-from-class="opacity-100 translate-y-0 scale-100"
                leave-to-class="opacity-0 translate-y-8 scale-95"
            >
                <div
                    v-if="open"
                    class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-700 overflow-hidden min-w-[280px] mb-2"
                >
                    <!-- Header -->
                    <div
                        class="bg-gradient-to-r from-AB to-AB/80 px-4 py-3 flex items-center justify-between"
                    >
                        <span
                            class="text-white font-semibold text-sm flex items-center gap-2"
                        >
                            <LuGlobe class="w-4 h-4" />
                            Quick Links
                        </span>
                        <button
                            @click="close"
                            class="text-white/80 hover:text-white transition-colors p-1 rounded-full hover:bg-white/20"
                        >
                            <LuX class="w-4 h-4" />
                        </button>
                    </div>

                    <!-- Auth Section -->
                    <div class="p-2 space-y-1">
                        <Link
                            :href="route('login')"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition-all duration-200 group"
                        >
                            <div
                                class="w-8 h-8 rounded-lg bg-gray-100 dark:bg-gray-700 flex items-center justify-center group-hover:bg-AB/10 group-hover:text-AB transition-colors"
                            >
                                <LuUser class="w-4 h-4" />
                            </div>
                            <div class="flex-1">
                                <span class="text-sm font-medium">Login</span>
                                <p
                                    class="text-xs text-gray-500 dark:text-gray-400"
                                >
                                    Access your account
                                </p>
                            </div>
                        </Link>

                        <Link
                            v-if="canRegister"
                            :href="route('register')"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition-all duration-200 group"
                        >
                            <div
                                class="w-8 h-8 rounded-lg bg-gray-100 dark:bg-gray-700 flex items-center justify-center group-hover:bg-AB/10 group-hover:text-AB transition-colors"
                            >
                                <LuUser class="w-4 h-4" />
                            </div>
                            <div class="flex-1">
                                <span class="text-sm font-medium"
                                    >Register</span
                                >
                                <p
                                    class="text-xs text-gray-500 dark:text-gray-400"
                                >
                                    Create new account
                                </p>
                            </div>
                        </Link>
                    </div>

                    <div class="h-px bg-gray-200 dark:bg-gray-700 mx-2" />

                    <!-- External Links -->
                    <div class="p-2 space-y-1">
                        <a
                            href="https://www.facebook.com/DACropBiotechCenter"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-gray-700 dark:text-gray-200 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-all duration-200 group"
                        >
                            <div
                                class="w-8 h-8 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center text-blue-600 group-hover:bg-blue-600 group-hover:text-white transition-all"
                            >
                                <LuFacebook class="w-4 h-4" />
                            </div>
                            <div class="flex-1">
                                <span class="text-sm font-medium"
                                    >Facebook</span
                                >
                                <ExternalLink
                                    class="w-3 h-3 text-gray-400 inline-block ml-1"
                                />
                            </div>
                        </a>

                        <a
                            href="mailto:cropbiotechcenter@gmail.com"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-gray-700 dark:text-gray-200 hover:bg-red-50 dark:hover:bg-red-900/20 transition-all duration-200 group"
                        >
                            <div
                                class="w-8 h-8 rounded-lg bg-red-100 dark:bg-red-900/30 flex items-center justify-center text-red-600 group-hover:bg-red-500 group-hover:text-white transition-all"
                            >
                                <LuMail class="w-4 h-4" />
                            </div>
                            <div class="flex-1">
                                <span class="text-sm font-medium">Email</span>
                                <span
                                    class="text-xs text-gray-500 dark:text-gray-400 block"
                                    >cropbiotechcenter@gmail.com</span
                                >
                            </div>
                        </a>

                        <a
                            href="https://dacbc.philrice.gov.ph/"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition-all duration-200 group"
                        >
                            <div
                                class="w-8 h-8 rounded-lg bg-gray-100 dark:bg-gray-700 flex items-center justify-center group-hover:bg-AB/10 group-hover:text-AB transition-colors"
                            >
                                <LuGlobe class="w-4 h-4" />
                            </div>
                            <div class="flex-1">
                                <span class="text-sm font-medium"
                                    >Corporate Website</span
                                >
                                <ExternalLink
                                    class="w-3 h-3 text-gray-400 inline-block ml-1"
                                />
                            </div>
                        </a>

                        <a
                            href="https://cbc360tour.philrice.gov.ph/"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-gray-700 dark:text-gray-200 hover:bg-amber-50 dark:hover:bg-amber-900/20 transition-all duration-200 group"
                        >
                            <div
                                class="w-8 h-8 rounded-lg bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center text-amber-600 group-hover:bg-amber-500 group-hover:text-white transition-all"
                            >
                                <LuStar class="w-4 h-4" />
                            </div>
                            <div class="flex-1">
                                <span class="text-sm font-medium"
                                    >Virtual Tour</span
                                >
                                <ExternalLink
                                    class="w-3 h-3 text-gray-400 inline-block ml-1"
                                />
                            </div>
                        </a>

                        <a
                            href="https://pin.philrice.gov.ph/"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-gray-700 dark:text-gray-200 hover:bg-green-50 dark:hover:bg-green-900/20 transition-all duration-200 group"
                        >
                            <div
                                class="w-8 h-8 rounded-lg bg-green-100 dark:bg-green-900/30 flex items-center justify-center text-green-600 group-hover:bg-green-500 group-hover:text-white transition-all"
                            >
                                <LuMapPin class="w-4 h-4" />
                            </div>
                            <div class="flex-1">
                                <span class="text-sm font-medium"
                                    >PIN System</span
                                >
                                <p
                                    class="text-xs text-gray-500 dark:text-gray-400"
                                >
                                    Plant Breeders & Innovators Network
                                </p>
                            </div>
                        </a>
                    </div>
                </div>
            </transition>

            <!-- FAB Toggle Button -->
            <button
                type="button"
                @click="toggle"
                class="w-14 h-14 rounded-full bg-AB text-white shadow-lg shadow-AB/30 flex items-center justify-center hover:shadow-xl hover:shadow-AB/40 hover:scale-105 active:scale-95 transition-all duration-300 focus:outline-none focus:ring-4 focus:ring-AB/20"
                :class="{ 'rotate-90': open }"
                aria-label="Toggle quick links menu"
            >
                <Menu v-if="!open" class="w-6 h-6" />
                <LuX v-else class="w-6 h-6" />
            </button>
        </div>
    </div>
</template>

<style scoped>
/* Custom color variable - ensure AB is defined in your Tailwind config */
</style>
