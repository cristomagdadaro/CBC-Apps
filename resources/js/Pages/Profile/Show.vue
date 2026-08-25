<script setup>
import { ref } from "vue";
import { User, Lock, Shield, Monitor, Trash2, ChevronRight, CheckCircle2, AlertCircle, Key, Smartphone, Globe } from "lucide-vue-next";
import DeleteUserForm from "@/Pages/Profile/Partials/DeleteUserForm.vue";
import LogoutOtherBrowserSessionsForm from "@/Pages/Profile/Partials/LogoutOtherBrowserSessionsForm.vue";
import TwoFactorAuthenticationForm from "@/Pages/Profile/Partials/TwoFactorAuthenticationForm.vue";
import UpdatePasswordForm from "@/Pages/Profile/Partials/UpdatePasswordForm.vue";
import UpdateProfileInformationForm from "@/Pages/Profile/Partials/UpdateProfileInformationForm.vue";

defineProps({
    confirmsTwoFactorAuthentication: Boolean,
    sessions: Array,
});

const sections = ref([
    {
        id: "profile",
        title: "Profile Information",
        description: "Update your account's profile information and email address.",
        icon: User,
        color: "blue",
        component: UpdateProfileInformationForm,
        prop: "canUpdateProfileInformation",
        propName: "user",
        propValue: "$page.props.auth.user",
    },
    {
        id: "password",
        title: "Update Password",
        description: "Ensure your account is using a long, random password to stay secure.",
        icon: Lock,
        color: "amber",
        component: UpdatePasswordForm,
        prop: "canUpdatePassword",
    },
    {
        id: "2fa",
        title: "Two-Factor Authentication",
        description: "Add additional security to your account using two-factor authentication.",
        icon: Shield,
        color: "emerald",
        component: TwoFactorAuthenticationForm,
        prop: "canManageTwoFactorAuthentication",
        extraProps: { requiresConfirmation: "confirmsTwoFactorAuthentication" },
    },
    {
        id: "sessions",
        title: "Browser Sessions",
        description: "Manage and log out your active sessions on other browsers and devices.",
        icon: Monitor,
        color: "purple",
        component: LogoutOtherBrowserSessionsForm,
        extraProps: { sessions: "sessions" },
    },
    {
        id: "delete",
        title: "Delete Account",
        description: "Permanently delete your account and all associated data.",
        icon: Trash2,
        color: "rose",
        danger: true,
        prop: "hasAccountDeletionFeatures",
    },
]);
</script>

<template>
    <AppLayout title="Profile">
        <template #header>
            <ActionHeaderLayout
                title="Profile Management"
                subtitle="Update your account's profile information and password." />
        </template>

        <div class="min-h-screen bg-gray-50/50 py-8 dark:bg-gray-900/50">
            <div class="mx-auto space-y-6 px-4 sm:px-6 lg:px-8">
                <!-- Progress Overview -->
                <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="mb-4 flex items-center justify-between">
                        <h2 class="text-sm font-semibold uppercase tracking-wider text-gray-900 dark:text-white">Security Status</h2>
                        <span class="text-xs text-gray-500 dark:text-gray-400">Last updated: Just now</span>
                    </div>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                        <div class="flex items-center gap-3 rounded-xl border border-blue-100 bg-blue-50 p-3 dark:border-blue-800 dark:bg-blue-900/20">
                            <div class="rounded-lg bg-blue-100 p-2 dark:bg-blue-800">
                                <Key class="h-4 w-4 text-blue-600 dark:text-blue-400" />
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Password</p>
                                <p class="text-sm font-semibold text-gray-900 dark:text-white">Strong</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 rounded-xl border border-emerald-100 bg-emerald-50 p-3 dark:border-emerald-800 dark:bg-emerald-900/20">
                            <div class="rounded-lg bg-emerald-100 p-2 dark:bg-emerald-800">
                                <Smartphone class="h-4 w-4 text-emerald-600 dark:text-emerald-400" />
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">2FA Status</p>
                                <p class="text-sm font-semibold text-gray-900 dark:text-white">
                                    {{ $page.props.jetstream.canManageTwoFactorAuthentication ? "Enabled" : "Disabled" }}
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 rounded-xl border border-purple-100 bg-purple-50 p-3 dark:border-purple-800 dark:bg-purple-900/20">
                            <div class="rounded-lg bg-purple-100 p-2 dark:bg-purple-800">
                                <Globe class="h-4 w-4 text-purple-600 dark:text-purple-400" />
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Active Sessions</p>
                                <p class="text-sm font-semibold text-gray-900 dark:text-white">
                                    {{ sessions?.length || 1 }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Profile Information -->
                <div
                    v-if="$page.props.jetstream.canUpdateProfileInformation"
                    class="group overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm transition-all duration-300 hover:shadow-md dark:border-gray-700 dark:bg-gray-800">
                    <div class="border-b border-gray-100 bg-gradient-to-r from-blue-50/50 to-transparent p-6 dark:border-gray-700 dark:from-blue-900/10 dark:to-transparent">
                        <div class="flex items-start gap-4">
                            <div class="rounded-xl bg-blue-100 p-3 transition-transform duration-300 group-hover:scale-110 dark:bg-blue-900/30">
                                <User class="h-6 w-6 text-blue-600 dark:text-blue-400" />
                            </div>
                            <div class="flex-1">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Profile Information</h3>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Update your account's profile information and email address.</p>
                            </div>
                            <ChevronRight class="h-5 w-5 text-gray-400 transition-all group-hover:translate-x-1 group-hover:text-blue-500" />
                        </div>
                    </div>
                    <div class="p-6">
                        <UpdateProfileInformationForm :user="$page.props.auth.user" />
                    </div>
                </div>

                <!-- Update Password -->
                <div
                    v-if="$page.props.jetstream.canUpdatePassword"
                    class="group overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm transition-all duration-300 hover:shadow-md dark:border-gray-700 dark:bg-gray-800">
                    <div class="border-b border-gray-100 bg-gradient-to-r from-amber-50/50 to-transparent p-6 dark:border-gray-700 dark:from-amber-900/10 dark:to-transparent">
                        <div class="flex items-start gap-4">
                            <div class="rounded-xl bg-amber-100 p-3 transition-transform duration-300 group-hover:scale-110 dark:bg-amber-900/30">
                                <Lock class="h-6 w-6 text-amber-600 dark:text-amber-400" />
                            </div>
                            <div class="flex-1">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Update Password</h3>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Ensure your account is using a long, random password to stay secure.</p>
                            </div>
                            <ChevronRight class="h-5 w-5 text-gray-400 transition-all group-hover:translate-x-1 group-hover:text-amber-500" />
                        </div>
                    </div>
                    <div class="p-6">
                        <UpdatePasswordForm />
                    </div>
                </div>

                <!-- Two Factor Authentication -->
                <div
                    v-if="$page.props.jetstream.canManageTwoFactorAuthentication"
                    class="group overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm transition-all duration-300 hover:shadow-md dark:border-gray-700 dark:bg-gray-800">
                    <div class="border-b border-gray-100 bg-gradient-to-r from-emerald-50/50 to-transparent p-6 dark:border-gray-700 dark:from-emerald-900/10 dark:to-transparent">
                        <div class="flex items-start gap-4">
                            <div class="rounded-xl bg-emerald-100 p-3 transition-transform duration-300 group-hover:scale-110 dark:bg-emerald-900/30">
                                <Shield class="h-6 w-6 text-emerald-600 dark:text-emerald-400" />
                            </div>
                            <div class="flex-1">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Two-Factor Authentication</h3>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Add additional security to your account using two-factor authentication.</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <span
                                    class="rounded-full px-2.5 py-1 text-xs font-medium"
                                    :class="confirmsTwoFactorAuthentication ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400'">
                                    {{ confirmsTwoFactorAuthentication ? "Active" : "Inactive" }}
                                </span>
                                <ChevronRight class="h-5 w-5 text-gray-400 transition-all group-hover:translate-x-1 group-hover:text-emerald-500" />
                            </div>
                        </div>
                    </div>
                    <div class="p-6">
                        <TwoFactorAuthenticationForm :requires-confirmation="confirmsTwoFactorAuthentication" />
                    </div>
                </div>

                <!-- Browser Sessions -->
                <div class="group overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm transition-all duration-300 hover:shadow-md dark:border-gray-700 dark:bg-gray-800">
                    <div class="border-b border-gray-100 bg-gradient-to-r from-purple-50/50 to-transparent p-6 dark:border-gray-700 dark:from-purple-900/10 dark:to-transparent">
                        <div class="flex items-start gap-4">
                            <div class="rounded-xl bg-purple-100 p-3 transition-transform duration-300 group-hover:scale-110 dark:bg-purple-900/30">
                                <Monitor class="h-6 w-6 text-purple-600 dark:text-purple-400" />
                            </div>
                            <div class="flex-1">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Browser Sessions</h3>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Manage and log out your active sessions on other browsers and devices.</p>
                            </div>
                            <ChevronRight class="h-5 w-5 text-gray-400 transition-all group-hover:translate-x-1 group-hover:text-purple-500" />
                        </div>
                    </div>
                    <div class="p-6">
                        <LogoutOtherBrowserSessionsForm :sessions="sessions" />
                    </div>
                </div>

                <!-- Delete Account -->
                <div
                    v-if="$page.props.jetstream.hasAccountDeletionFeatures"
                    class="group overflow-hidden rounded-2xl border border-rose-200 bg-white shadow-sm transition-all duration-300 hover:shadow-md dark:border-rose-900/30 dark:bg-gray-800">
                    <div class="border-b border-rose-100 bg-gradient-to-r from-rose-50/50 to-transparent p-6 dark:border-rose-900/20 dark:from-rose-900/10 dark:to-transparent">
                        <div class="flex items-start gap-4">
                            <div class="rounded-xl bg-rose-100 p-3 transition-transform duration-300 group-hover:scale-110 dark:bg-rose-900/30">
                                <Trash2 class="h-6 w-6 text-rose-600 dark:text-rose-400" />
                            </div>
                            <div class="flex-1">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Delete Account</h3>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Permanently delete your account and all associated data.</p>
                            </div>
                            <div class="rounded-lg bg-rose-100 p-1 dark:bg-rose-900/30">
                                <AlertCircle class="h-5 w-5 text-rose-600 dark:text-rose-400" />
                            </div>
                        </div>
                    </div>
                    <div class="p-6">
                        <DeleteUserForm />
                    </div>
                </div>

                <!-- Footer Info -->
                <div class="py-6 text-center">
                    <p class="text-xs text-gray-400 dark:text-gray-500">Need help? Contact support for assistance with your account settings.</p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
