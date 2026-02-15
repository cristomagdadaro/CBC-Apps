<script>
import axios from 'axios'
import { useNotifier } from '@/Modules/composables/useNotifier'

export default {
    name: 'EditUser',
    props: {
        data: { type: Object, required: true },
    },
    data() {
        return {
            form: {
                name: this.data.name || '',
                email: this.data.email || '',
                employee_id: this.data.employee_id || '',
                password: '',
                password_confirmation: '',
                is_admin: !!this.data.is_admin,
                roles: (this.data.roles || []).map((r) => r.name),
            },
            roleOptions: [
                { value: 'admin', label: 'Admin' },
                { value: 'laboratory_manager', label: 'Laboratory Manager' },
                { value: 'ict_manager', label: 'ICT Manager' },
                { value: 'administrative_assistant', label: 'Administrative Assistant' },
            ],
            errors: {},
            submitting: false,
            deleting: false,
            success: null,
            notifyError: null,
            warning: null,
        }
    },
    created() {
        const notifier = useNotifier()
        this.success = notifier.success
        this.notifyError = notifier.error
        this.warning = notifier.warning
    },
    methods: {
        async submit() {
            this.submitting = true
            this.errors = {}
            try {
                await axios.put(route('api.users.update', this.data.id), this.form)
                this.success('User updated successfully.')
                router.visit(route('system.users.index'))
            } catch (error) {
                this.errors = error?.response?.data?.errors || {}
                if (!Object.keys(this.errors).length) {
                    this.notifyError('Failed to update user.')
                }
            } finally {
                this.submitting = false
            }
        },
        async destroyUser() {
            if (!confirm('Delete this user?')) {
                this.warning('User deletion was cancelled.')
                return
            }
            this.deleting = true
            try {
                await axios.delete(route('api.users.destroy', this.data.id))
                this.success('User deleted successfully.')
                router.visit(route('system.users.index'))
            } catch (error) {
                this.notifyError('Failed to delete user.')
            } finally {
                this.deleting = false
            }
        }
    }
}
</script>

<template>
    <AppLayout title="Edit User">
        <template #header>
            <h1 class="text-xl font-semibold">Edit User</h1>
        </template>

        <div class="max-w-3xl mx-auto px-4">
            <form class="bg-white rounded-lg shadow p-4 space-y-3" @submit.prevent="submit">
                <input v-model="form.name" class="w-full rounded border-gray-300" placeholder="Name" />
                <p v-if="errors.name" class="text-xs text-red-600">{{ errors.name[0] }}</p>

                <input v-model="form.email" class="w-full rounded border-gray-300" placeholder="Email" />
                <p v-if="errors.email" class="text-xs text-red-600">{{ errors.email[0] }}</p>

                <input v-model="form.employee_id" class="w-full rounded border-gray-300" placeholder="Employee ID (optional)" />

                <input v-model="form.password" type="password" class="w-full rounded border-gray-300" placeholder="New Password (optional)" />
                <input v-model="form.password_confirmation" type="password" class="w-full rounded border-gray-300" placeholder="Confirm New Password" />

                <label class="flex items-center gap-2 text-sm">
                    <input v-model="form.is_admin" type="checkbox" />
                    System Administrator
                </label>

                <div>
                    <p class="text-sm font-medium mb-1">Roles</p>
                    <label v-for="role in roleOptions" :key="role.value" class="flex items-center gap-2 text-sm mb-1">
                        <input v-model="form.roles" type="checkbox" :value="role.value" />
                        {{ role.label }}
                    </label>
                </div>

                <div class="flex gap-2">
                    <Link :href="route('system.users.index')" class="px-3 py-2 rounded border">Cancel</Link>
                    <button type="button" :disabled="deleting" class="px-3 py-2 rounded border border-red-300 text-red-700" @click="destroyUser">Delete</button>
                    <button :disabled="submitting" class="px-3 py-2 rounded bg-AB text-white hover:bg-AC">Save</button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
