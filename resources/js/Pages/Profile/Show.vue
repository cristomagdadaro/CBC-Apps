<script setup>
import { ref } from 'vue';
import { 
  User, 
  Lock, 
  Shield, 
  Monitor, 
  Trash2, 
  ChevronRight, 
  CheckCircle2,
  AlertCircle,
  Key,
  Smartphone,
  Globe
} from 'lucide-vue-next';
import DeleteUserForm from '@/Pages/Profile/Partials/DeleteUserForm.vue';
import LogoutOtherBrowserSessionsForm from '@/Pages/Profile/Partials/LogoutOtherBrowserSessionsForm.vue';
import TwoFactorAuthenticationForm from '@/Pages/Profile/Partials/TwoFactorAuthenticationForm.vue';
import UpdatePasswordForm from '@/Pages/Profile/Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from '@/Pages/Profile/Partials/UpdateProfileInformationForm.vue';

defineProps({
  confirmsTwoFactorAuthentication: Boolean,
  sessions: Array,
});

const sections = ref([
  {
    id: 'profile',
    title: 'Profile Information',
    description: 'Update your account\'s profile information and email address.',
    icon: User,
    color: 'blue',
    component: UpdateProfileInformationForm,
    prop: 'canUpdateProfileInformation',
    propName: 'user',
    propValue: '$page.props.auth.user'
  },
  {
    id: 'password',
    title: 'Update Password',
    description: 'Ensure your account is using a long, random password to stay secure.',
    icon: Lock,
    color: 'amber',
    component: UpdatePasswordForm,
    prop: 'canUpdatePassword'
  },
  {
    id: '2fa',
    title: 'Two-Factor Authentication',
    description: 'Add additional security to your account using two-factor authentication.',
    icon: Shield,
    color: 'emerald',
    component: TwoFactorAuthenticationForm,
    prop: 'canManageTwoFactorAuthentication',
    extraProps: { requiresConfirmation: 'confirmsTwoFactorAuthentication' }
  },
  {
    id: 'sessions',
    title: 'Browser Sessions',
    description: 'Manage and log out your active sessions on other browsers and devices.',
    icon: Monitor,
    color: 'purple',
    component: LogoutOtherBrowserSessionsForm,
    extraProps: { sessions: 'sessions' }
  },
  {
    id: 'delete',
    title: 'Delete Account',
    description: 'Permanently delete your account and all associated data.',
    icon: Trash2,
    color: 'rose',
    danger: true,
    prop: 'hasAccountDeletionFeatures'
  }
]);
</script>

<template>
  <AppLayout title="Profile">
    <template #header>
        <ActionHeaderLayout title="Profile Management" subtitle="Update your account's profile information and password." />
    </template>

    <div class="min-h-screen bg-gray-50/50 dark:bg-gray-900/50 py-8">
      <div class="mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        
        <!-- Progress Overview -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
          <div class="flex items-center justify-between mb-4">
            <h2 class="text-sm font-semibold text-gray-900 dark:text-white uppercase tracking-wider">Security Status</h2>
            <span class="text-xs text-gray-500 dark:text-gray-400">Last updated: Just now</span>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="flex items-center gap-3 p-3 rounded-xl bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-800">
              <div class="p-2 bg-blue-100 dark:bg-blue-800 rounded-lg">
                <Key class="w-4 h-4 text-blue-600 dark:text-blue-400" />
              </div>
              <div>
                <p class="text-xs text-gray-500 dark:text-gray-400">Password</p>
                <p class="text-sm font-semibold text-gray-900 dark:text-white">Strong</p>
              </div>
            </div>
            <div class="flex items-center gap-3 p-3 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-100 dark:border-emerald-800">
              <div class="p-2 bg-emerald-100 dark:bg-emerald-800 rounded-lg">
                <Smartphone class="w-4 h-4 text-emerald-600 dark:text-emerald-400" />
              </div>
              <div>
                <p class="text-xs text-gray-500 dark:text-gray-400">2FA Status</p>
                <p class="text-sm font-semibold text-gray-900 dark:text-white">
                  {{ $page.props.jetstream.canManageTwoFactorAuthentication ? 'Enabled' : 'Disabled' }}
                </p>
              </div>
            </div>
            <div class="flex items-center gap-3 p-3 rounded-xl bg-purple-50 dark:bg-purple-900/20 border border-purple-100 dark:border-purple-800">
              <div class="p-2 bg-purple-100 dark:bg-purple-800 rounded-lg">
                <Globe class="w-4 h-4 text-purple-600 dark:text-purple-400" />
              </div>
              <div>
                <p class="text-xs text-gray-500 dark:text-gray-400">Active Sessions</p>
                <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ sessions?.length || 1 }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Profile Information -->
        <div 
          v-if="$page.props.jetstream.canUpdateProfileInformation"
          class="group bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden hover:shadow-md transition-all duration-300"
        >
          <div class="p-6 border-b border-gray-100 dark:border-gray-700 bg-gradient-to-r from-blue-50/50 to-transparent dark:from-blue-900/10 dark:to-transparent">
            <div class="flex items-start gap-4">
              <div class="p-3 bg-blue-100 dark:bg-blue-900/30 rounded-xl group-hover:scale-110 transition-transform duration-300">
                <User class="w-6 h-6 text-blue-600 dark:text-blue-400" />
              </div>
              <div class="flex-1">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Profile Information</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Update your account's profile information and email address.</p>
              </div>
              <ChevronRight class="w-5 h-5 text-gray-400 group-hover:text-blue-500 group-hover:translate-x-1 transition-all" />
            </div>
          </div>
          <div class="p-6">
            <UpdateProfileInformationForm :user="$page.props.auth.user" />
          </div>
        </div>

        <!-- Update Password -->
        <div 
          v-if="$page.props.jetstream.canUpdatePassword"
          class="group bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden hover:shadow-md transition-all duration-300"
        >
          <div class="p-6 border-b border-gray-100 dark:border-gray-700 bg-gradient-to-r from-amber-50/50 to-transparent dark:from-amber-900/10 dark:to-transparent">
            <div class="flex items-start gap-4">
              <div class="p-3 bg-amber-100 dark:bg-amber-900/30 rounded-xl group-hover:scale-110 transition-transform duration-300">
                <Lock class="w-6 h-6 text-amber-600 dark:text-amber-400" />
              </div>
              <div class="flex-1">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Update Password</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Ensure your account is using a long, random password to stay secure.</p>
              </div>
              <ChevronRight class="w-5 h-5 text-gray-400 group-hover:text-amber-500 group-hover:translate-x-1 transition-all" />
            </div>
          </div>
          <div class="p-6">
            <UpdatePasswordForm />
          </div>
        </div>

        <!-- Two Factor Authentication -->
        <div 
          v-if="$page.props.jetstream.canManageTwoFactorAuthentication"
          class="group bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden hover:shadow-md transition-all duration-300"
        >
          <div class="p-6 border-b border-gray-100 dark:border-gray-700 bg-gradient-to-r from-emerald-50/50 to-transparent dark:from-emerald-900/10 dark:to-transparent">
            <div class="flex items-start gap-4">
              <div class="p-3 bg-emerald-100 dark:bg-emerald-900/30 rounded-xl group-hover:scale-110 transition-transform duration-300">
                <Shield class="w-6 h-6 text-emerald-600 dark:text-emerald-400" />
              </div>
              <div class="flex-1">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Two-Factor Authentication</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Add additional security to your account using two-factor authentication.</p>
              </div>
              <div class="flex items-center gap-2">
                <span 
                  class="px-2.5 py-1 text-xs font-medium rounded-full"
                  :class="confirmsTwoFactorAuthentication ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400'"
                >
                  {{ confirmsTwoFactorAuthentication ? 'Active' : 'Inactive' }}
                </span>
                <ChevronRight class="w-5 h-5 text-gray-400 group-hover:text-emerald-500 group-hover:translate-x-1 transition-all" />
              </div>
            </div>
          </div>
          <div class="p-6">
            <TwoFactorAuthenticationForm :requires-confirmation="confirmsTwoFactorAuthentication" />
          </div>
        </div>

        <!-- Browser Sessions -->
        <div class="group bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden hover:shadow-md transition-all duration-300">
          <div class="p-6 border-b border-gray-100 dark:border-gray-700 bg-gradient-to-r from-purple-50/50 to-transparent dark:from-purple-900/10 dark:to-transparent">
            <div class="flex items-start gap-4">
              <div class="p-3 bg-purple-100 dark:bg-purple-900/30 rounded-xl group-hover:scale-110 transition-transform duration-300">
                <Monitor class="w-6 h-6 text-purple-600 dark:text-purple-400" />
              </div>
              <div class="flex-1">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Browser Sessions</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Manage and log out your active sessions on other browsers and devices.</p>
              </div>
              <ChevronRight class="w-5 h-5 text-gray-400 group-hover:text-purple-500 group-hover:translate-x-1 transition-all" />
            </div>
          </div>
          <div class="p-6">
            <LogoutOtherBrowserSessionsForm :sessions="sessions" />
          </div>
        </div>

        <!-- Delete Account -->
        <div 
          v-if="$page.props.jetstream.hasAccountDeletionFeatures"
          class="group bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-rose-200 dark:border-rose-900/30 overflow-hidden hover:shadow-md transition-all duration-300"
        >
          <div class="p-6 border-b border-rose-100 dark:border-rose-900/20 bg-gradient-to-r from-rose-50/50 to-transparent dark:from-rose-900/10 dark:to-transparent">
            <div class="flex items-start gap-4">
              <div class="p-3 bg-rose-100 dark:bg-rose-900/30 rounded-xl group-hover:scale-110 transition-transform duration-300">
                <Trash2 class="w-6 h-6 text-rose-600 dark:text-rose-400" />
              </div>
              <div class="flex-1">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Delete Account</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Permanently delete your account and all associated data.</p>
              </div>
              <div class="p-1 bg-rose-100 dark:bg-rose-900/30 rounded-lg">
                <AlertCircle class="w-5 h-5 text-rose-600 dark:text-rose-400" />
              </div>
            </div>
          </div>
          <div class="p-6">
            <DeleteUserForm />
          </div>
        </div>

        <!-- Footer Info -->
        <div class="text-center py-6">
          <p class="text-xs text-gray-400 dark:text-gray-500">
            Need help? Contact support for assistance with your account settings.
          </p>
        </div>
      </div>
    </div>
  </AppLayout>
</template>