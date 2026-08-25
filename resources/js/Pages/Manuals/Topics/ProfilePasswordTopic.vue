<script>
import TopicLayout from "@/Pages/Manuals/Components/TopicLayout.vue";

export default {
    name: "ProfilePasswordTopic",
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
        title="Profile & Password Management"
        description="Instructions for all users on updating personal information, managing passwords, and enhancing account security[cite: 3]."
        icon="LuUserCircle">
        <div class="space-y-4">
            <h3 class="ml-1 text-[0.65rem] font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400">For All Users</h3>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <!-- Accessing Profile -->
                <div class="rounded-xl border border-sky-100 bg-sky-50/50 p-5 shadow-sm dark:border-sky-500/20 dark:bg-sky-500/5">
                    <h4 class="mb-4 flex items-center gap-1.5 text-[0.65rem] font-semibold uppercase tracking-widest text-sky-600 dark:text-sky-400">
                        <LuUserCircle class="h-3.5 w-3.5" />
                        Accessing Your Profile
                    </h4>
                    <ol class="ml-1 list-inside list-decimal space-y-2.5 text-xs font-medium text-slate-600 dark:text-slate-300">
                        <li>
                            Click on your
                            <span class="font-semibold text-slate-800 dark:text-slate-200">user avatar or name</span>
                            in the top right corner[cite: 3].
                        </li>
                        <li>
                            Select
                            <span class="font-semibold text-slate-800 dark:text-slate-200">Profile</span>
                            from the dropdown menu[cite: 3].
                        </li>
                        <li>You will see your profile page with various update options[cite: 3].</li>
                    </ol>
                </div>

                <!-- Updating Profile -->
                <div class="rounded-xl border border-emerald-100 bg-emerald-50/50 p-5 shadow-sm dark:border-emerald-500/20 dark:bg-emerald-500/5">
                    <h4 class="mb-4 flex items-center gap-1.5 text-[0.65rem] font-semibold uppercase tracking-widest text-emerald-600 dark:text-emerald-400">
                        <LuUserCog class="h-3.5 w-3.5" />
                        Updating Profile Information
                    </h4>
                    <ol class="ml-1 list-inside list-decimal space-y-2.5 text-xs font-medium text-slate-600 dark:text-slate-300">
                        <li>
                            On your profile page, find the
                            <span class="font-semibold text-slate-800 dark:text-slate-200">"Update Profile Information"</span>
                            section[cite: 3].
                        </li>
                        <li>
                            Update the following fields as needed:[cite: 3]
                            <ul class="ml-5 mt-2 list-none space-y-1.5">
                                <li class="flex items-center gap-2">
                                    <div class="h-1 w-1 rounded-full bg-emerald-400"></div>
                                    Name[cite: 3]
                                </li>
                                <li class="flex items-center gap-2">
                                    <div class="h-1 w-1 rounded-full bg-emerald-400"></div>
                                    Email address[cite: 3]
                                </li>
                                <li class="flex items-center gap-2">
                                    <div class="h-1 w-1 rounded-full bg-emerald-400"></div>
                                    Photo / Avatar[cite: 3]
                                </li>
                            </ul>
                        </li>
                        <li>
                            Click
                            <span class="font-semibold text-slate-800 dark:text-slate-200">Save</span>
                            to apply changes[cite: 3].
                        </li>
                        <li>You will see a confirmation message when successfully updated[cite: 3].</li>
                    </ol>
                </div>

                <!-- Updating Password -->
                <div class="rounded-xl border border-amber-100 bg-amber-50/50 p-5 shadow-sm dark:border-amber-500/20 dark:bg-amber-500/5">
                    <h4 class="mb-4 flex items-center gap-1.5 text-[0.65rem] font-semibold uppercase tracking-widest text-amber-600 dark:text-amber-400">
                        <LuKeyRound class="h-3.5 w-3.5" />
                        Updating Your Password
                    </h4>
                    <ol class="ml-1 list-inside list-decimal space-y-2.5 text-xs font-medium text-slate-600 dark:text-slate-300">
                        <li>
                            On your profile page, find the
                            <span class="font-semibold text-slate-800 dark:text-slate-200">"Update Password"</span>
                            section[cite: 3].
                        </li>
                        <li>
                            Enter your
                            <span class="font-semibold text-slate-800 dark:text-slate-200">current password</span>
                            [cite: 3].
                        </li>
                        <li>
                            Enter your
                            <span class="font-semibold text-slate-800 dark:text-slate-200">new password</span>
                            (must be at least 8 characters)[cite: 3].
                        </li>
                        <li>Confirm your new password[cite: 3].</li>
                        <li>
                            Click
                            <span class="font-semibold text-slate-800 dark:text-slate-200">Save</span>
                            to update your password[cite: 3].
                        </li>
                        <li>Your password will be updated immediately[cite: 3].</li>
                    </ol>
                </div>

                <!-- Password Security Tips -->
                <div class="rounded-xl border border-rose-100 bg-rose-50/50 p-5 shadow-sm dark:border-rose-500/20 dark:bg-rose-500/5">
                    <h4 class="mb-4 flex items-center gap-1.5 text-[0.65rem] font-semibold uppercase tracking-widest text-rose-600 dark:text-rose-400">
                        <LuShieldAlert class="h-3.5 w-3.5" />
                        Password Security Tips
                    </h4>
                    <ul class="ml-1 space-y-3 text-xs font-medium text-slate-600 dark:text-slate-300">
                        <li class="flex items-start gap-2.5">
                            <LuAlertTriangle class="mt-0.5 h-3.5 w-3.5 shrink-0 text-rose-400" />
                            <span class="leading-relaxed">Use a strong password with uppercase, lowercase, numbers, and symbols[cite: 3].</span>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <LuAlertTriangle class="mt-0.5 h-3.5 w-3.5 shrink-0 text-rose-400" />
                            <span class="leading-relaxed">Never share your password with anyone[cite: 3].</span>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <LuAlertTriangle class="mt-0.5 h-3.5 w-3.5 shrink-0 text-rose-400" />
                            <span class="leading-relaxed">Change your password regularly (at least every 3 months)[cite: 3].</span>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <LuAlertTriangle class="mt-0.5 h-3.5 w-3.5 shrink-0 text-rose-400" />
                            <span class="leading-relaxed">Do not reuse old passwords[cite: 3].</span>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <LuAlertTriangle class="mt-0.5 h-3.5 w-3.5 shrink-0 text-rose-400" />
                            <span class="leading-relaxed">If you suspect unauthorized access, change your password immediately[cite: 3].</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Other Options -->
            <div class="rounded-xl border border-purple-100 bg-purple-50/50 p-5 shadow-sm dark:border-purple-500/20 dark:bg-purple-500/5">
                <h4 class="mb-4 flex items-center gap-1.5 text-[0.65rem] font-semibold uppercase tracking-widest text-purple-600 dark:text-purple-400">
                    <LuMoreHorizontal class="h-3.5 w-3.5" />
                    Other Account Options
                </h4>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div class="rounded-lg border border-purple-100 bg-white/60 p-3 dark:border-purple-500/20 dark:bg-slate-800/50">
                        <p class="mb-1 text-xs font-semibold text-slate-800 dark:text-slate-200">Two-Factor Authentication</p>
                        <p class="text-[0.65rem] font-medium text-slate-500 dark:text-slate-400">Enable for extra security[cite: 3].</p>
                    </div>
                    <div class="rounded-lg border border-purple-100 bg-white/60 p-3 dark:border-purple-500/20 dark:bg-slate-800/50">
                        <p class="mb-1 text-xs font-semibold text-slate-800 dark:text-slate-200">Logout Other Sessions</p>
                        <p class="text-[0.65rem] font-medium text-slate-500 dark:text-slate-400">Sign out from other devices[cite: 3].</p>
                    </div>
                    <div class="rounded-lg border border-purple-100 bg-white/60 p-3 dark:border-purple-500/20 dark:bg-slate-800/50">
                        <p class="mb-1 text-xs font-semibold text-rose-600 dark:text-rose-400">Delete Account</p>
                        <p class="text-[0.65rem] font-medium text-slate-500 dark:text-slate-400">Permanently remove your account (careful!)[cite: 3].</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- For Programmers -->
        <div
            v-if="showDeveloperSections"
            class="space-y-4 border-t border-slate-100 pt-6 dark:border-slate-800/60">
            <h3 class="ml-1 text-[0.65rem] font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400">For Programmers (Architecture)</h3>

            <div class="rounded-xl border border-sky-100 bg-sky-50/50 p-5 shadow-sm dark:border-sky-500/20 dark:bg-sky-500/5">
                <h4 class="mb-4 flex items-center gap-1.5 text-[0.65rem] font-semibold uppercase tracking-widest text-sky-600 dark:text-sky-400">
                    <LuUserCircle class="h-3.5 w-3.5" />
                    Accessing Your Profile
                </h4>
                <ol class="ml-1 list-inside list-decimal space-y-2.5 text-xs font-medium text-slate-600 dark:text-slate-300">
                    <li>
                        Click on your
                        <span class="font-semibold text-slate-800 dark:text-slate-200">user avatar or name</span>
                        in the top right corner[cite: 3].
                    </li>
                    <li>
                        Select
                        <span class="font-semibold text-slate-800 dark:text-slate-200">Profile</span>
                        from the dropdown menu[cite: 3].
                    </li>
                    <li>You will see your profile page with various update options[cite: 3].</li>
                </ol>
            </div>

            <!-- Updating Profile -->
            <div class="rounded-xl border border-emerald-100 bg-emerald-50/50 p-5 shadow-sm dark:border-emerald-500/20 dark:bg-emerald-500/5">
                <h4 class="mb-4 flex items-center gap-1.5 text-[0.65rem] font-semibold uppercase tracking-widest text-emerald-600 dark:text-emerald-400">
                    <LuUserCog class="h-3.5 w-3.5" />
                    Updating Profile Information
                </h4>
                <ol class="ml-1 list-inside list-decimal space-y-2.5 text-xs font-medium text-slate-600 dark:text-slate-300">
                    <li>
                        On your profile page, find the
                        <span class="font-semibold text-slate-800 dark:text-slate-200">"Update Profile Information"</span>
                        section[cite: 3].
                    </li>
                    <li>
                        Update the following fields as needed:[cite: 3]
                        <ul class="ml-5 mt-2 list-none space-y-1.5">
                            <li class="flex items-center gap-2">
                                <div class="h-1 w-1 rounded-full bg-emerald-400"></div>
                                Name[cite: 3]
                            </li>
                            <li class="flex items-center gap-2">
                                <div class="h-1 w-1 rounded-full bg-emerald-400"></div>
                                Email address[cite: 3]
                            </li>
                            <li class="flex items-center gap-2">
                                <div class="h-1 w-1 rounded-full bg-emerald-400"></div>
                                Photo / Avatar[cite: 3]
                            </li>
                        </ul>
                    </li>
                    <li>
                        Click
                        <span class="font-semibold text-slate-800 dark:text-slate-200">Save</span>
                        to apply changes[cite: 3].
                    </li>
                    <li>You will see a confirmation message when successfully updated[cite: 3].</li>
                </ol>
            </div>

            <!-- Updating Password -->
            <div class="rounded-xl border border-amber-100 bg-amber-50/50 p-5 shadow-sm dark:border-amber-500/20 dark:bg-amber-500/5">
                <h4 class="mb-4 flex items-center gap-1.5 text-[0.65rem] font-semibold uppercase tracking-widest text-amber-600 dark:text-amber-400">
                    <LuKeyRound class="h-3.5 w-3.5" />
                    Updating Your Password
                </h4>
                <ol class="ml-1 list-inside list-decimal space-y-2.5 text-xs font-medium text-slate-600 dark:text-slate-300">
                    <li>
                        On your profile page, find the
                        <span class="font-semibold text-slate-800 dark:text-slate-200">"Update Password"</span>
                        section[cite: 3].
                    </li>
                    <li>
                        Enter your
                        <span class="font-semibold text-slate-800 dark:text-slate-200">current password</span>
                        [cite: 3].
                    </li>
                    <li>
                        Enter your
                        <span class="font-semibold text-slate-800 dark:text-slate-200">new password</span>
                        (must be at least 8 characters)[cite: 3].
                    </li>
                    <li>Confirm your new password[cite: 3].</li>
                    <li>
                        Click
                        <span class="font-semibold text-slate-800 dark:text-slate-200">Save</span>
                        to update your password[cite: 3].
                    </li>
                    <li>Your password will be updated immediately[cite: 3].</li>
                </ol>
            </div>

            <!-- Password Security Tips -->
            <div class="rounded-xl border border-rose-100 bg-rose-50/50 p-5 shadow-sm dark:border-rose-500/20 dark:bg-rose-500/5">
                <h4 class="mb-4 flex items-center gap-1.5 text-[0.65rem] font-semibold uppercase tracking-widest text-rose-600 dark:text-rose-400">
                    <LuShieldAlert class="h-3.5 w-3.5" />
                    Password Security Tips
                </h4>
                <ul class="ml-1 space-y-3 text-xs font-medium text-slate-600 dark:text-slate-300">
                    <li class="flex items-start gap-2.5">
                        <LuAlertTriangle class="mt-0.5 h-3.5 w-3.5 shrink-0 text-rose-400" />
                        <span class="leading-relaxed">Use a strong password with uppercase, lowercase, numbers, and symbols[cite: 3].</span>
                    </li>
                    <li class="flex items-start gap-2.5">
                        <LuAlertTriangle class="mt-0.5 h-3.5 w-3.5 shrink-0 text-rose-400" />
                        <span class="leading-relaxed">Never share your password with anyone[cite: 3].</span>
                    </li>
                    <li class="flex items-start gap-2.5">
                        <LuAlertTriangle class="mt-0.5 h-3.5 w-3.5 shrink-0 text-rose-400" />
                        <span class="leading-relaxed">Change your password regularly (at least every 3 months)[cite: 3].</span>
                    </li>
                    <li class="flex items-start gap-2.5">
                        <LuAlertTriangle class="mt-0.5 h-3.5 w-3.5 shrink-0 text-rose-400" />
                        <span class="leading-relaxed">Do not reuse old passwords[cite: 3].</span>
                    </li>
                    <li class="flex items-start gap-2.5">
                        <LuAlertTriangle class="mt-0.5 h-3.5 w-3.5 shrink-0 text-rose-400" />
                        <span class="leading-relaxed">If you suspect unauthorized access, change your password immediately[cite: 3].</span>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Other Options -->
        <div class="rounded-xl border border-purple-100 bg-purple-50/50 p-5 shadow-sm dark:border-purple-500/20 dark:bg-purple-500/5">
            <h4 class="mb-4 flex items-center gap-1.5 text-[0.65rem] font-semibold uppercase tracking-widest text-purple-600 dark:text-purple-400">
                <LuMoreHorizontal class="h-3.5 w-3.5" />
                Other Account Options
            </h4>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div class="rounded-lg border border-purple-100 bg-white/60 p-3 dark:border-purple-500/20 dark:bg-slate-800/50">
                    <p class="mb-1 text-xs font-semibold text-slate-800 dark:text-slate-200">Two-Factor Authentication</p>
                    <p class="text-[0.65rem] font-medium text-slate-500 dark:text-slate-400">Enable for extra security[cite: 3].</p>
                </div>
                <div class="rounded-lg border border-purple-100 bg-white/60 p-3 dark:border-purple-500/20 dark:bg-slate-800/50">
                    <p class="mb-1 text-xs font-semibold text-slate-800 dark:text-slate-200">Logout Other Sessions</p>
                    <p class="text-[0.65rem] font-medium text-slate-500 dark:text-slate-400">Sign out from other devices[cite: 3].</p>
                </div>
                <div class="rounded-lg border border-purple-100 bg-white/60 p-3 dark:border-purple-500/20 dark:bg-slate-800/50">
                    <p class="mb-1 text-xs font-semibold text-rose-600 dark:text-rose-400">Delete Account</p>
                    <p class="text-[0.65rem] font-medium text-slate-500 dark:text-slate-400">Permanently remove your account (careful!)[cite: 3].</p>
                </div>
            </div>
        </div>

        <!-- For Programmers -->
        <div
            v-if="showDeveloperSections"
            class="space-y-4 border-t border-slate-100 pt-6 dark:border-slate-800/60">
            <h3 class="ml-1 text-[0.65rem] font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400">For Programmers (Architecture)</h3>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <!-- Components -->
                <div class="rounded-xl border border-indigo-100 bg-indigo-50/50 p-5 shadow-sm dark:border-indigo-500/20 dark:bg-indigo-500/5">
                    <h4 class="mb-4 flex items-center gap-1.5 text-[0.65rem] font-semibold uppercase tracking-widest text-indigo-600 dark:text-indigo-400">
                        <LuLayoutTemplate class="h-3.5 w-3.5" />
                        Profile Management Components
                    </h4>
                    <ul class="space-y-3.5 text-xs font-medium text-slate-600 dark:text-slate-400">
                        <li>
                            <span class="mb-1.5 block text-[0.6rem] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Main profile page</span>
                            <code class="block break-all rounded-md border border-slate-200 bg-white px-2 py-1.5 font-mono text-[0.65rem] font-semibold text-indigo-500 shadow-sm dark:border-slate-700 dark:bg-slate-800 dark:text-indigo-400">resources/js/Pages/Profile/Show.vue</code>
                            [cite: 3]
                        </li>
                        <li>
                            <span class="mb-1.5 block text-[0.6rem] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Profile update form</span>
                            <code class="block break-all rounded-md border border-slate-200 bg-white px-2 py-1.5 font-mono text-[0.65rem] font-semibold text-indigo-500 shadow-sm dark:border-slate-700 dark:bg-slate-800 dark:text-indigo-400">resources/js/Pages/Profile/Partials/UpdateProfileInformationForm.vue</code>
                            [cite: 3]
                        </li>
                        <li>
                            <span class="mb-1.5 block text-[0.6rem] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Password update form</span>
                            <code class="block break-all rounded-md border border-slate-200 bg-white px-2 py-1.5 font-mono text-[0.65rem] font-semibold text-indigo-500 shadow-sm dark:border-slate-700 dark:bg-slate-800 dark:text-indigo-400">resources/js/Pages/Profile/Partials/UpdatePasswordForm.vue</code>
                            [cite: 3]
                        </li>
                    </ul>
                </div>

                <!-- Backend -->
                <div class="h-fit rounded-xl border border-slate-200/60 bg-slate-50/50 p-5 shadow-sm dark:border-slate-700/60 dark:bg-slate-800/30">
                    <h4 class="mb-4 flex items-center gap-1.5 text-[0.65rem] font-semibold uppercase tracking-widest text-slate-600 dark:text-slate-300">
                        <LuDatabase class="h-3.5 w-3.5" />
                        User Model & Authentication
                    </h4>
                    <ul class="space-y-4 text-xs font-medium text-slate-600 dark:text-slate-400">
                        <li>
                            <span class="mb-1.5 block text-[0.6rem] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">User model with profile fields</span>
                            <code class="break-all rounded-md border border-slate-200 bg-white px-1.5 py-0.5 font-mono text-[0.65rem] font-semibold text-slate-700 shadow-sm dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300">app/Models/User.php</code>
                            [cite: 3]
                        </li>
                        <li>
                            <span class="mb-1.5 block text-[0.6rem] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Built-in authentication system</span>
                            <code class="rounded-md border border-slate-200 bg-white px-1.5 py-0.5 font-mono text-[0.65rem] font-semibold text-slate-700 shadow-sm dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300">Laravel Fortify</code>
                            [cite: 3]
                        </li>
                        <li>
                            <span class="mb-1.5 block text-[0.6rem] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Hashing Algorithm</span>
                            <p class="leading-relaxed">
                                Passwords are hashed using
                                <code class="mx-0.5 rounded-md border border-slate-200 bg-white px-1.5 py-0.5 font-mono text-[0.65rem] font-semibold text-slate-700 shadow-sm dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300">bcrypt</code>
                                algorithm[cite: 3].
                            </p>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </TopicLayout>
</template>
