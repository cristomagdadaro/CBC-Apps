<script>
import { Link } from '@inertiajs/vue3';

export default {
    name: 'SocialLinks',
    props: {
        canLogin: Boolean,
        canRegister: Boolean,
        laravelVersion: String,
        phpVersion: String,
    },
    data() {
        return {
            open: false,
        };
    },
    methods: {
        toggle() {
            this.open = !this.open;
        },
    },
};
</script>

<template>
    <!-- Container fixed to bottom-right -->
    <div class="fixed bottom-4 right-4 z-[1000] flex flex-col items-end gap-2">
        <!-- Desktop / md+ view: show all icons -->
        <div class="hidden md:flex items-center gap-2 bg-white/90 dark:bg-gray-900/90 border border-gray-200 dark:border-gray-700 rounded-full px-3 py-2 shadow-lg">
            <Link
                :href="route('login')"
                rel="noopener noreferrer"
                class="p-2 rounded-full hover:bg-gray-100 dark:hover:bg-gray-800 inline-flex"
                title="Login"
            >
                <i class="bi bi-globe text-xl text-AB">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-box-arrow-in-left" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M10 3.5a.5.5 0 0 0-.5-.5h-8a.5.5 0 0 0-.5.5v9a.5.5 0 0 0 .5.5h8a.5.5 0 0 0 .5-.5v-2a.5.5 0 0 1 1 0v2A1.5 1.5 0 0 1 9.5 14h-8A1.5 1.5 0 0 1 0 12.5v-9A1.5 1.5 0 0 1 1.5 2h8A1.5 1.5 0 0 1 11 3.5v2a.5.5 0 0 1-1 0z"/>
                        <path fill-rule="evenodd" d="M4.146 8.354a.5.5 0 0 1 0-.708l3-3a.5.5 0 1 1 .708.708L5.707 7.5H14.5a.5.5 0 0 1 0 1H5.707l2.147 2.146a.5.5 0 0 1-.708.708z"/>
                    </svg>
                </i>
            </Link>
            <Link
                v-if="!!canRegister" 
                :href="route('register')"
                rel="noopener noreferrer"
                class="p-2 rounded-full hover:bg-gray-100 dark:hover:bg-gray-800 inline-flex"
                title="Register"
            >
                <i class="bi bi-globe text-xl text-AB">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-r-circle" viewBox="0 0 16 16">
                        <path d="M1 8a7 7 0 1 0 14 0A7 7 0 0 0 1 8m15 0A8 8 0 1 1 0 8a8 8 0 0 1 16 0M5.5 4.002h3.11c1.71 0 2.741.973 2.741 2.46 0 1.138-.667 1.94-1.495 2.24L11.5 12H9.98L8.52 8.924H6.836V12H5.5zm1.335 1.09v2.777h1.549c.995 0 1.573-.463 1.573-1.36 0-.913-.596-1.417-1.537-1.417z"/>
                    </svg>
                </i>
            </Link>
            <a
                href="https://dacbc.philrice.gov.ph/"
                target="_blank"
                rel="noopener noreferrer"
                class="p-2 rounded-full hover:bg-gray-100 dark:hover:bg-gray-800 inline-flex"
                title="Corporate Website"
            >
                <i class="bi bi-globe text-xl text-AB">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-globe" viewBox="0 0 16 16">
                        <path d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8m7.5-6.923c-.67.204-1.335.82-1.887 1.855A8 8 0 0 0 5.145 4H7.5zM4.09 4a9.3 9.3 0 0 1 .64-1.539 7 7 0 0 1 .597-.933A7.03 7.03 0 0 0 2.255 4zm-.582 3.5c.03-.877.138-1.718.312-2.5H1.674a7 7 0 0 0-.656 2.5zM4.847 5a12.5 12.5 0 0 0-.338 2.5H7.5V5zM8.5 5v2.5h2.99a12.5 12.5 0 0 0-.337-2.5zM4.51 8.5a12.5 12.5 0 0 0 .337 2.5H7.5V8.5zm3.99 0V11h2.653c.187-.765.306-1.608.338-2.5zM5.145 12q.208.58.468 1.068c.552 1.035 1.218 1.65 1.887 1.855V12zm.182 2.472a7 7 0 0 1-.597-.933A9.3 9.3 0 0 1 4.09 12H2.255a7 7 0 0 0 3.072 2.472M3.82 11a13.7 13.7 0 0 1-.312-2.5h-2.49c.062.89.291 1.733.656 2.5zm6.853 3.472A7 7 0 0 0 13.745 12H11.91a9.3 9.3 0 0 1-.64 1.539 7 7 0 0 1-.597.933M8.5 12v2.923c.67-.204 1.335-.82 1.887-1.855q.26-.487.468-1.068zm3.68-1h2.146c.365-.767.594-1.61.656-2.5h-2.49a13.7 13.7 0 0 1-.312 2.5m2.802-3.5a7 7 0 0 0-.656-2.5H12.18c.174.782.282 1.623.312 2.5zM11.27 2.461c.247.464.462.98.64 1.539h1.835a7 7 0 0 0-3.072-2.472c.218.284.418.598.597.933M10.855 4a8 8 0 0 0-.468-1.068C9.835 1.897 9.17 1.282 8.5 1.077V4z"/>
                    </svg>
                </i>
            </a>
            <a
                href="https://cbc360tour.philrice.gov.ph/"
                target="_blank"
                rel="noopener noreferrer"
                class="p-2 rounded-full hover:bg-gray-100 dark:hover:bg-gray-800 inline-flex"
                title="Virtual Tour"
            >
                <i class="bi bi-compass text-xl text-AB">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-compass" viewBox="0 0 16 16">
                        <path d="M8 16.016a7.5 7.5 0 0 0 1.962-14.74A1 1 0 0 0 9 0H7a1 1 0 0 0-.962 1.276A7.5 7.5 0 0 0 8 16.016m6.5-7.5a6.5 6.5 0 1 1-13 0 6.5 6.5 0 0 1 13 0"/>
                        <path d="m6.94 7.44 4.95-2.83-2.83 4.95-4.949 2.83 2.828-4.95z"/>
                    </svg>
                </i>
            </a>
            <a
                href="https://pin.philrice.gov.ph/"
                target="_blank"
                rel="noopener noreferrer"
                class="p-2 rounded-full hover:bg-gray-100 dark:hover:bg-gray-800 inline-flex"
                title="Plant Breeders Map and Innovators Network"
            >
                <i class="bi bi-geo-alt text-xl text-AB">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-geo-alt" viewBox="0 0 16 16">
                        <path d="M12.166 8.94c-.524 1.062-1.234 2.12-1.96 3.07A32 32 0 0 1 8 14.58a32 32 0 0 1-2.206-2.57c-.726-.95-1.436-2.008-1.96-3.07C3.304 7.867 3 6.862 3 6a5 5 0 0 1 10 0c0 .862-.305 1.867-.834 2.94M8 16s6-5.686 6-10A6 6 0 0 0 2 6c0 4.314 6 10 6 10"/>
                        <path d="M8 8a2 2 0 1 1 0-4 2 2 0 0 1 0 4m0 1a3 3 0 1 0 0-6 3 3 0 0 0 0 6"/>
                    </svg>
                </i>
            </a>
            <a
                href="https://www.facebook.com/DACropBiotechCenter"
                target="_blank"
                rel="noopener noreferrer"
                class="p-2 rounded-full hover:bg-gray-100 dark:hover:bg-gray-800 inline-flex"
                title="Facebook"
            >
                <i class="bi bi-facebook text-xl text-AB">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-facebook" viewBox="0 0 16 16">
                        <path d="M16 8.049c0-4.446-3.582-8.05-8-8.05C3.58 0-.002 3.603-.002 8.05c0 4.017 2.926 7.347 6.75 7.951v-5.625h-2.03V8.05H6.75V6.275c0-2.017 1.195-3.131 3.022-3.131.876 0 1.791.157 1.791.157v1.98h-1.009c-.993 0-1.303.621-1.303 1.258v1.51h2.218l-.354 2.326H9.25V16c3.824-.604 6.75-3.934 6.75-7.951"/>
                    </svg>
                </i>
            </a>
            <a
                href="mailto:cropbiotechcenter@gmail.com"
                class="p-2 rounded-full hover:bg-gray-100 dark:hover:bg-gray-800 inline-flex"
                title="Email"
            >
                <i class="bi bi-envelope-fill text-xl text-AB">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-envelope" viewBox="0 0 16 16">
                        <path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2zm2-1a1 1 0 0 0-1 1v.217l7 4.2 7-4.2V4a1 1 0 0 0-1-1zm13 2.383-4.708 2.825L15 11.105zm-.034 6.876-5.64-3.471L8 9.583l-1.326-.795-5.64 3.47A1 1 0 0 0 2 13h12a1 1 0 0 0 .966-.741M1 11.105l4.708-2.897L1 5.383z"/>
                    </svg>
                </i>
            </a>
        </div>

        <!-- Mobile / small view: single button that toggles a collapsible list -->
        <div class="md:hidden flex flex-col items-end gap-2 mr-4">
            <!-- Toggle button -->
            <button
                type="button"
                class="inline-flex items-center justify-center w-11 h-11 rounded-full bg-AB text-white shadow-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-AB"
                @click="toggle"
                aria-label="Open social links"
            >
                <i class="bi bi-share-fill text-xl">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-gear" viewBox="0 0 16 16">
                        <path d="M8 4.754a3.246 3.246 0 1 0 0 6.492 3.246 3.246 0 0 0 0-6.492M5.754 8a2.246 2.246 0 1 1 4.492 0 2.246 2.246 0 0 1-4.492 0"/>
                        <path d="M9.796 1.343c-.527-1.79-3.065-1.79-3.592 0l-.094.319a.873.873 0 0 1-1.255.52l-.292-.16c-1.64-.892-3.433.902-2.54 2.541l.159.292a.873.873 0 0 1-.52 1.255l-.319.094c-1.79.527-1.79 3.065 0 3.592l.319.094a.873.873 0 0 1 .52 1.255l-.16.292c-.892 1.64.901 3.434 2.541 2.54l.292-.159a.873.873 0 0 1 1.255.52l.094.319c.527 1.79 3.065 1.79 3.592 0l.094-.319a.873.873 0 0 1 1.255-.52l.292.16c1.64.893 3.434-.902 2.54-2.541l-.159-.292a.873.873 0 0 1 .52-1.255l.319-.094c1.79-.527 1.79-3.065 0-3.592l-.319-.094a.873.873 0 0 1-.52-1.255l.16-.292c.893-1.64-.902-3.433-2.541-2.54l-.292.159a.873.873 0 0 1-1.255-.52zm-2.633.283c.246-.835 1.428-.835 1.674 0l.094.319a1.873 1.873 0 0 0 2.693 1.115l.291-.16c.764-.415 1.6.42 1.184 1.185l-.159.292a1.873 1.873 0 0 0 1.116 2.692l.318.094c.835.246.835 1.428 0 1.674l-.319.094a1.873 1.873 0 0 0-1.115 2.693l.16.291c.415.764-.42 1.6-1.185 1.184l-.291-.159a1.873 1.873 0 0 0-2.693 1.116l-.094.318c-.246.835-1.428.835-1.674 0l-.094-.319a1.873 1.873 0 0 0-2.692-1.115l-.292.16c-.764.415-1.6-.42-1.184-1.185l.159-.291A1.873 1.873 0 0 0 1.945 8.93l-.319-.094c-.835-.246-.835-1.428 0-1.674l.319-.094A1.873 1.873 0 0 0 3.06 4.377l-.16-.292c-.415-.764.42-1.6 1.185-1.184l.292.159a1.873 1.873 0 0 0 2.692-1.115z"/>
                    </svg>
                </i>
            </button>

            <!-- Collapsible content -->
            <transition
                enter-active-class="transition ease-out duration-150"
                enter-from-class="opacity-0 translate-y-2"
                enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition ease-in duration-100"
                leave-from-class="opacity-100 translate-y-0"
                leave-to-class="opacity-0 translate-y-2"
            >
                <div
                    v-if="open"
                    class="flex flex-col bg-white/95 dark:bg-gray-900/95 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg overflow-hidden min-w-[220px]"
                >
                    <Link
                        :href="route('login')"
                        rel="noopener noreferrer"
                        class="flex items-center gap-2 px-3 py-2 text-xs hover:bg-gray-100 dark:hover:bg-gray-800"
                        title="Login"
                    >
                    <i class="bi bi-globe text-base text-AB"></i>
                        Login
                    </Link>
                    <Link
                        v-if="!!canRegister" :href="route('register')" 
                        rel="noopener noreferrer"
                        class="flex items-center gap-2 px-3 py-2 text-xs hover:bg-gray-100 dark:hover:bg-gray-800"
                        title="Register"
                    >
                    <i class="bi bi-globe text-base text-AB"></i>
                        Register
                    </Link>
                    <button
                        type="button"
                        class="flex items-center justify-between px-3 py-2 text-xs font-semibold bg-AB text-white"
                        @click="toggle"
                    >
                        <span>Connect with us</span>
                        <i class="bi bi-x-lg text-xs"></i>
                    </button>
                    <a
                        href="https://www.facebook.com/DACropBiotechCenter"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="flex items-center gap-2 px-3 py-2 text-xs hover:bg-gray-100 dark:hover:bg-gray-800"
                    >
                        <i class="bi bi-facebook text-base text-blue-600"></i>
                        <span>Facebook</span>
                    </a>
                    <a
                        href="mailto:cropbiotechcenter@gmail.com"
                        class="flex items-center gap-2 px-3 py-2 text-xs hover:bg-gray-100 dark:hover:bg-gray-800"
                    >
                        <i class="bi bi-envelope-fill text-base text-AB"></i>
                        <span>Email</span>
                    </a>
                    <a
                        href="https://dacbc.philrice.gov.ph/"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="flex items-center gap-2 px-3 py-2 text-xs hover:bg-gray-100 dark:hover:bg-gray-800"
                    >
                        <i class="bi bi-globe text-base text-AB"></i>
                        <span>Corporate Website</span>
                    </a>
                    <a
                        href="https://cbc360tour.philrice.gov.ph/"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="flex items-center gap-2 px-3 py-2 text-xs hover:bg-gray-100 dark:hover:bg-gray-800"
                    >
                        <i class="bi bi-compass text-base text-AB"></i>
                        <span>Virtual Tour</span>
                    </a>
                    <a
                        href="https://pin.philrice.gov.ph/"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="flex items-center gap-2 px-3 py-2 text-xs hover:bg-gray-100 dark:hover:bg-gray-800"
                    >
                        <i class="bi bi-geo-alt text-base text-AB"></i>
                        <span>Plant Breeders &amp; Innovators Network System</span>
                    </a>
                </div>
            </transition>
        </div>
    </div>
</template>

<style scoped>
</style>

