<script>
import TopicLayout from '@/Pages/Manuals/Components/TopicLayout.vue';

export default {
    name: 'ProfilePasswordTopic',
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
        title="Profile & Password Management"
        description="Instructions for all users on updating personal information, managing passwords, and enhancing account security[cite: 3]."
        icon="LuUserCircle"
    >
        <div class="space-y-4">
            <h3 class="text-[0.65rem] font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400 ml-1">For All Users</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                
                <!-- Accessing Profile -->
                <div class="bg-sky-50/50 dark:bg-sky-500/5 border border-sky-100 dark:border-sky-500/20 rounded-xl p-5 shadow-sm">
                    <h4 class="text-[0.65rem] font-semibold uppercase tracking-widest text-sky-600 dark:text-sky-400 mb-4 flex items-center gap-1.5">
                        <LuUserCircle class="w-3.5 h-3.5" /> Accessing Your Profile
                    </h4>
                    <ol class="space-y-2.5 text-xs font-medium text-slate-600 dark:text-slate-300 list-decimal list-inside ml-1">
                        <li>Click on your <span class="font-semibold text-slate-800 dark:text-slate-200">user avatar or name</span> in the top right corner[cite: 3].</li>
                        <li>Select <span class="font-semibold text-slate-800 dark:text-slate-200">Profile</span> from the dropdown menu[cite: 3].</li>
                        <li>You will see your profile page with various update options[cite: 3].</li>
                    </ol>
                </div>

                <!-- Updating Profile -->
                <div class="bg-emerald-50/50 dark:bg-emerald-500/5 border border-emerald-100 dark:border-emerald-500/20 rounded-xl p-5 shadow-sm">
                    <h4 class="text-[0.65rem] font-semibold uppercase tracking-widest text-emerald-600 dark:text-emerald-400 mb-4 flex items-center gap-1.5">
                        <LuUserCog class="w-3.5 h-3.5" /> Updating Profile Information
                    </h4>
                    <ol class="space-y-2.5 text-xs font-medium text-slate-600 dark:text-slate-300 list-decimal list-inside ml-1">
                        <li>On your profile page, find the <span class="font-semibold text-slate-800 dark:text-slate-200">"Update Profile Information"</span> section[cite: 3].</li>
                        <li>Update the following fields as needed:[cite: 3]
                            <ul class="list-none space-y-1.5 ml-5 mt-2">
                                <li class="flex items-center gap-2"><div class="w-1 h-1 rounded-full bg-emerald-400"></div> Name[cite: 3]</li>
                                <li class="flex items-center gap-2"><div class="w-1 h-1 rounded-full bg-emerald-400"></div> Email address[cite: 3]</li>
                                <li class="flex items-center gap-2"><div class="w-1 h-1 rounded-full bg-emerald-400"></div> Photo / Avatar[cite: 3]</li>
                            </ul>
                        </li>
                        <li>Click <span class="font-semibold text-slate-800 dark:text-slate-200">Save</span> to apply changes[cite: 3].</li>
                        <li>You will see a confirmation message when successfully updated[cite: 3].</li>
                    </ol>
                </div>

                <!-- Updating Password -->
                <div class="bg-amber-50/50 dark:bg-amber-500/5 border border-amber-100 dark:border-amber-500/20 rounded-xl p-5 shadow-sm">
                    <h4 class="text-[0.65rem] font-semibold uppercase tracking-widest text-amber-600 dark:text-amber-400 mb-4 flex items-center gap-1.5">
                        <LuKeyRound class="w-3.5 h-3.5" /> Updating Your Password
                    </h4>
                    <ol class="space-y-2.5 text-xs font-medium text-slate-600 dark:text-slate-300 list-decimal list-inside ml-1">
                        <li>On your profile page, find the <span class="font-semibold text-slate-800 dark:text-slate-200">"Update Password"</span> section[cite: 3].</li>
                        <li>Enter your <span class="font-semibold text-slate-800 dark:text-slate-200">current password</span>[cite: 3].</li>
                        <li>Enter your <span class="font-semibold text-slate-800 dark:text-slate-200">new password</span> (must be at least 8 characters)[cite: 3].</li>
                        <li>Confirm your new password[cite: 3].</li>
                        <li>Click <span class="font-semibold text-slate-800 dark:text-slate-200">Save</span> to update your password[cite: 3].</li>
                        <li>Your password will be updated immediately[cite: 3].</li>
                    </ol>
                </div>

                <!-- Password Security Tips -->
                <div class="bg-rose-50/50 dark:bg-rose-500/5 border border-rose-100 dark:border-rose-500/20 rounded-xl p-5 shadow-sm">
                    <h4 class="text-[0.65rem] font-semibold uppercase tracking-widest text-rose-600 dark:text-rose-400 mb-4 flex items-center gap-1.5">
                        <LuShieldAlert class="w-3.5 h-3.5" /> Password Security Tips
                    </h4>
                    <ul class="space-y-3 text-xs font-medium text-slate-600 dark:text-slate-300 ml-1">
                        <li class="flex items-start gap-2.5"><LuAlertTriangle class="w-3.5 h-3.5 text-rose-400 shrink-0 mt-0.5" /> <span class="leading-relaxed">Use a strong password with uppercase, lowercase, numbers, and symbols[cite: 3].</span></li>
                        <li class="flex items-start gap-2.5"><LuAlertTriangle class="w-3.5 h-3.5 text-rose-400 shrink-0 mt-0.5" /> <span class="leading-relaxed">Never share your password with anyone[cite: 3].</span></li>
                        <li class="flex items-start gap-2.5"><LuAlertTriangle class="w-3.5 h-3.5 text-rose-400 shrink-0 mt-0.5" /> <span class="leading-relaxed">Change your password regularly (at least every 3 months)[cite: 3].</span></li>
                        <li class="flex items-start gap-2.5"><LuAlertTriangle class="w-3.5 h-3.5 text-rose-400 shrink-0 mt-0.5" /> <span class="leading-relaxed">Do not reuse old passwords[cite: 3].</span></li>
                        <li class="flex items-start gap-2.5"><LuAlertTriangle class="w-3.5 h-3.5 text-rose-400 shrink-0 mt-0.5" /> <span class="leading-relaxed">If you suspect unauthorized access, change your password immediately[cite: 3].</span></li>
                    </ul>
                </div>
            </div>

            <!-- Other Options -->
            <div class="bg-purple-50/50 dark:bg-purple-500/5 border border-purple-100 dark:border-purple-500/20 rounded-xl p-5 shadow-sm">
                <h4 class="text-[0.65rem] font-semibold uppercase tracking-widest text-purple-600 dark:text-purple-400 mb-4 flex items-center gap-1.5">
                    <LuMoreHorizontal class="w-3.5 h-3.5" /> Other Account Options
                </h4>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="bg-white/60 dark:bg-slate-800/50 rounded-lg p-3 border border-purple-100 dark:border-purple-500/20">
                        <p class="text-xs font-semibold text-slate-800 dark:text-slate-200 mb-1">Two-Factor Authentication</p>
                        <p class="text-[0.65rem] font-medium text-slate-500 dark:text-slate-400">Enable for extra security[cite: 3].</p>
                    </div>
                    <div class="bg-white/60 dark:bg-slate-800/50 rounded-lg p-3 border border-purple-100 dark:border-purple-500/20">
                        <p class="text-xs font-semibold text-slate-800 dark:text-slate-200 mb-1">Logout Other Sessions</p>
                        <p class="text-[0.65rem] font-medium text-slate-500 dark:text-slate-400">Sign out from other devices[cite: 3].</p>
                    </div>
                    <div class="bg-white/60 dark:bg-slate-800/50 rounded-lg p-3 border border-purple-100 dark:border-purple-500/20">
                        <p class="text-xs font-semibold text-rose-600 dark:text-rose-400 mb-1">Delete Account</p>
                        <p class="text-[0.65rem] font-medium text-slate-500 dark:text-slate-400">Permanently remove your account (careful!)[cite: 3].</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- For Programmers -->
        <div v-if="showDeveloperSections" class="space-y-4 pt-6 border-t border-slate-100 dark:border-slate-800/60">
            <h3 class="text-[0.65rem] font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400 ml-1">For Programmers (Architecture)</h3>
            
                        <div class="bg-sky-50/50 dark:bg-sky-500/5 border border-sky-100 dark:border-sky-500/20 rounded-xl p-5 shadow-sm">
                            <h4 class="text-[0.65rem] font-semibold uppercase tracking-widest text-sky-600 dark:text-sky-400 mb-4 flex items-center gap-1.5">
                                <LuUserCircle class="w-3.5 h-3.5" /> Accessing Your Profile
                            </h4>
                            <ol class="space-y-2.5 text-xs font-medium text-slate-600 dark:text-slate-300 list-decimal list-inside ml-1">
                                <li>Click on your <span class="font-semibold text-slate-800 dark:text-slate-200">user avatar or name</span> in the top right corner[cite: 3].</li>
                                <li>Select <span class="font-semibold text-slate-800 dark:text-slate-200">Profile</span> from the dropdown menu[cite: 3].</li>
                                <li>You will see your profile page with various update options[cite: 3].</li>
                            </ol>
                        </div>

                        <!-- Updating Profile -->
                        <div class="bg-emerald-50/50 dark:bg-emerald-500/5 border border-emerald-100 dark:border-emerald-500/20 rounded-xl p-5 shadow-sm">
                            <h4 class="text-[0.65rem] font-semibold uppercase tracking-widest text-emerald-600 dark:text-emerald-400 mb-4 flex items-center gap-1.5">
                                <LuUserCog class="w-3.5 h-3.5" /> Updating Profile Information
                            </h4>
                            <ol class="space-y-2.5 text-xs font-medium text-slate-600 dark:text-slate-300 list-decimal list-inside ml-1">
                                <li>On your profile page, find the <span class="font-semibold text-slate-800 dark:text-slate-200">"Update Profile Information"</span> section[cite: 3].</li>
                                <li>Update the following fields as needed:[cite: 3]
                                    <ul class="list-none space-y-1.5 ml-5 mt-2">
                                        <li class="flex items-center gap-2"><div class="w-1 h-1 rounded-full bg-emerald-400"></div> Name[cite: 3]</li>
                                        <li class="flex items-center gap-2"><div class="w-1 h-1 rounded-full bg-emerald-400"></div> Email address[cite: 3]</li>
                                        <li class="flex items-center gap-2"><div class="w-1 h-1 rounded-full bg-emerald-400"></div> Photo / Avatar[cite: 3]</li>
                                    </ul>
                                </li>
                                <li>Click <span class="font-semibold text-slate-800 dark:text-slate-200">Save</span> to apply changes[cite: 3].</li>
                                <li>You will see a confirmation message when successfully updated[cite: 3].</li>
                            </ol>
                        </div>

                        <!-- Updating Password -->
                        <div class="bg-amber-50/50 dark:bg-amber-500/5 border border-amber-100 dark:border-amber-500/20 rounded-xl p-5 shadow-sm">
                            <h4 class="text-[0.65rem] font-semibold uppercase tracking-widest text-amber-600 dark:text-amber-400 mb-4 flex items-center gap-1.5">
                                <LuKeyRound class="w-3.5 h-3.5" /> Updating Your Password
                            </h4>
                            <ol class="space-y-2.5 text-xs font-medium text-slate-600 dark:text-slate-300 list-decimal list-inside ml-1">
                                <li>On your profile page, find the <span class="font-semibold text-slate-800 dark:text-slate-200">"Update Password"</span> section[cite: 3].</li>
                                <li>Enter your <span class="font-semibold text-slate-800 dark:text-slate-200">current password</span>[cite: 3].</li>
                                <li>Enter your <span class="font-semibold text-slate-800 dark:text-slate-200">new password</span> (must be at least 8 characters)[cite: 3].</li>
                                <li>Confirm your new password[cite: 3].</li>
                                <li>Click <span class="font-semibold text-slate-800 dark:text-slate-200">Save</span> to update your password[cite: 3].</li>
                                <li>Your password will be updated immediately[cite: 3].</li>
                            </ol>
                        </div>

                        <!-- Password Security Tips -->
                        <div class="bg-rose-50/50 dark:bg-rose-500/5 border border-rose-100 dark:border-rose-500/20 rounded-xl p-5 shadow-sm">
                            <h4 class="text-[0.65rem] font-semibold uppercase tracking-widest text-rose-600 dark:text-rose-400 mb-4 flex items-center gap-1.5">
                                <LuShieldAlert class="w-3.5 h-3.5" /> Password Security Tips
                            </h4>
                            <ul class="space-y-3 text-xs font-medium text-slate-600 dark:text-slate-300 ml-1">
                                <li class="flex items-start gap-2.5"><LuAlertTriangle class="w-3.5 h-3.5 text-rose-400 shrink-0 mt-0.5" /> <span class="leading-relaxed">Use a strong password with uppercase, lowercase, numbers, and symbols[cite: 3].</span></li>
                                <li class="flex items-start gap-2.5"><LuAlertTriangle class="w-3.5 h-3.5 text-rose-400 shrink-0 mt-0.5" /> <span class="leading-relaxed">Never share your password with anyone[cite: 3].</span></li>
                                <li class="flex items-start gap-2.5"><LuAlertTriangle class="w-3.5 h-3.5 text-rose-400 shrink-0 mt-0.5" /> <span class="leading-relaxed">Change your password regularly (at least every 3 months)[cite: 3].</span></li>
                                <li class="flex items-start gap-2.5"><LuAlertTriangle class="w-3.5 h-3.5 text-rose-400 shrink-0 mt-0.5" /> <span class="leading-relaxed">Do not reuse old passwords[cite: 3].</span></li>
                                <li class="flex items-start gap-2.5"><LuAlertTriangle class="w-3.5 h-3.5 text-rose-400 shrink-0 mt-0.5" /> <span class="leading-relaxed">If you suspect unauthorized access, change your password immediately[cite: 3].</span></li>
                            </ul>
                        </div>
                    </div>

                    <!-- Other Options -->
                    <div class="bg-purple-50/50 dark:bg-purple-500/5 border border-purple-100 dark:border-purple-500/20 rounded-xl p-5 shadow-sm">
                        <h4 class="text-[0.65rem] font-semibold uppercase tracking-widest text-purple-600 dark:text-purple-400 mb-4 flex items-center gap-1.5">
                            <LuMoreHorizontal class="w-3.5 h-3.5" /> Other Account Options
                        </h4>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div class="bg-white/60 dark:bg-slate-800/50 rounded-lg p-3 border border-purple-100 dark:border-purple-500/20">
                                <p class="text-xs font-semibold text-slate-800 dark:text-slate-200 mb-1">Two-Factor Authentication</p>
                                <p class="text-[0.65rem] font-medium text-slate-500 dark:text-slate-400">Enable for extra security[cite: 3].</p>
                            </div>
                            <div class="bg-white/60 dark:bg-slate-800/50 rounded-lg p-3 border border-purple-100 dark:border-purple-500/20">
                                <p class="text-xs font-semibold text-slate-800 dark:text-slate-200 mb-1">Logout Other Sessions</p>
                                <p class="text-[0.65rem] font-medium text-slate-500 dark:text-slate-400">Sign out from other devices[cite: 3].</p>
                            </div>
                            <div class="bg-white/60 dark:bg-slate-800/50 rounded-lg p-3 border border-purple-100 dark:border-purple-500/20">
                                <p class="text-xs font-semibold text-rose-600 dark:text-rose-400 mb-1">Delete Account</p>
                                <p class="text-[0.65rem] font-medium text-slate-500 dark:text-slate-400">Permanently remove your account (careful!)[cite: 3].</p>
                            </div>
                        </div>
                    </div>

                <!-- For Programmers -->
                <div v-if="showDeveloperSections" class="space-y-4 pt-6 border-t border-slate-100 dark:border-slate-800/60">
                    <h3 class="text-[0.65rem] font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400 ml-1">For Programmers (Architecture)</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        
                        <!-- Components -->
                        <div class="bg-indigo-50/50 dark:bg-indigo-500/5 border border-indigo-100 dark:border-indigo-500/20 rounded-xl p-5 shadow-sm">
                            <h4 class="text-[0.65rem] font-semibold uppercase tracking-widest text-indigo-600 dark:text-indigo-400 mb-4 flex items-center gap-1.5">
                                <LuLayoutTemplate class="w-3.5 h-3.5" /> Profile Management Components
                            </h4>
                            <ul class="space-y-3.5 text-xs font-medium text-slate-600 dark:text-slate-400">
                                <li>
                                    <span class="block mb-1.5 text-slate-400 dark:text-slate-500 font-semibold uppercase tracking-wider text-[0.6rem]">Main profile page</span>
                                    <code class="block px-2 py-1.5 rounded-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.65rem] font-semibold shadow-sm text-indigo-500 dark:text-indigo-400 break-all">resources/js/Pages/Profile/Show.vue</code>[cite: 3]
                                </li>
                                <li>
                                    <span class="block mb-1.5 text-slate-400 dark:text-slate-500 font-semibold uppercase tracking-wider text-[0.6rem]">Profile update form</span>
                                    <code class="block px-2 py-1.5 rounded-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.65rem] font-semibold shadow-sm text-indigo-500 dark:text-indigo-400 break-all">resources/js/Pages/Profile/Partials/UpdateProfileInformationForm.vue</code>[cite: 3]
                                </li>
                                <li>
                                    <span class="block mb-1.5 text-slate-400 dark:text-slate-500 font-semibold uppercase tracking-wider text-[0.6rem]">Password update form</span>
                                    <code class="block px-2 py-1.5 rounded-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.65rem] font-semibold shadow-sm text-indigo-500 dark:text-indigo-400 break-all">resources/js/Pages/Profile/Partials/UpdatePasswordForm.vue</code>[cite: 3]
                                </li>
                            </ul>
                        </div>

                        <!-- Backend -->
                        <div class="bg-slate-50/50 dark:bg-slate-800/30 border border-slate-200/60 dark:border-slate-700/60 rounded-xl p-5 shadow-sm h-fit">
                            <h4 class="text-[0.65rem] font-semibold uppercase tracking-widest text-slate-600 dark:text-slate-300 mb-4 flex items-center gap-1.5">
                                <LuDatabase class="w-3.5 h-3.5" /> User Model & Authentication
                            </h4>
                            <ul class="space-y-4 text-xs font-medium text-slate-600 dark:text-slate-400">
                                <li>
                                    <span class="block mb-1.5 text-slate-400 dark:text-slate-500 font-semibold uppercase tracking-wider text-[0.6rem]">User model with profile fields</span>
                                    <code class="px-1.5 py-0.5 rounded-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.65rem] font-semibold shadow-sm text-slate-700 dark:text-slate-300 break-all">app/Models/User.php</code>[cite: 3]
                                </li>
                                <li>
                                    <span class="block mb-1.5 text-slate-400 dark:text-slate-500 font-semibold uppercase tracking-wider text-[0.6rem]">Built-in authentication system</span>
                                    <code class="px-1.5 py-0.5 rounded-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.65rem] font-semibold shadow-sm text-slate-700 dark:text-slate-300">Laravel Fortify</code>[cite: 3]
                                </li>
                                <li>
                                    <span class="block mb-1.5 text-slate-400 dark:text-slate-500 font-semibold uppercase tracking-wider text-[0.6rem]">Hashing Algorithm</span>
                                    <p class="leading-relaxed">Passwords are hashed using <code class="px-1.5 py-0.5 mx-0.5 rounded-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.65rem] font-semibold shadow-sm text-slate-700 dark:text-slate-300">bcrypt</code> algorithm[cite: 3].</p>
                                </li>
                            </ul>
                        </div>

                    </div>
                </div>

    </TopicLayout>
</template>