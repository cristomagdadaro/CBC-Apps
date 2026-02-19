<script>
import axios from 'axios'
import { useNotifier } from '@/Modules/composables/useNotifier'

export default {
    name: 'CreateUser',
    components: { Link },
    props: {
        roleOptions: {
            type: Array,
            default: () => [],
        },
        permissionOptions: {
            type: Array,
            default: () => [],
        },
    },
    data() {
        return {
            form: {
                name: '',
                email: '',
                employee_id: '',
                password: '',
                password_confirmation: '',
                is_admin: false,
                roles: [],
                permissions: [],
            },
            errors: {},
            submitting: false,
            success: null,
            notifyError: null,
        }
    },
    computed: {
        normalizedRoleOptions() {
            return this.roleOptions.map((role) => ({
                value: role,
                label: this.formatLabel(role),
            }))
        },
    },
    created() {
        const notifier = useNotifier()
        this.success = notifier.success
        this.notifyError = notifier.error
    },
    methods: {
        formatLabel(value) {
            if (!value) return ''
            return String(value)
                .replace(/[._]/g, ' ')
                .split(' ')
                .filter(Boolean)
                .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
                .join(' ')
        },
        async submit() {
            this.submitting = true
            this.errors = {}
            try {
                await axios.post(route('api.users.store'), this.form)
                this.success('User created successfully.')
                router.visit(route('system.users.index'))
            } catch (error) {
                this.errors = error?.response?.data?.errors || {}
                if (!Object.keys(this.errors).length) {
                    this.notifyError('Failed to create user.')
                }
            } finally {
                this.submitting = false
            }
        }
    }
}
</script>

<template>
    <AppLayout title="Create User">
        <template #header>
            <ActionHeaderLayout title="Create User" subtitle="Add a new user to the system and assign roles and permissions." :route-link="route('system.users.index')" />
        </template>

        <div class="max-w-3xl mx-auto px-4">
            <form class="bg-white rounded-lg shadow p-4 space-y-3" @submit.prevent="submit">
                <input v-model="form.name" class="w-full rounded border-gray-300" placeholder="Name" />
                <p v-if="errors.name" class="text-xs text-red-600">{{ errors.name[0] }}</p>

                <input v-model="form.email" class="w-full rounded border-gray-300" placeholder="Email" />
                <p v-if="errors.email" class="text-xs text-red-600">{{ errors.email[0] }}</p>

                <input v-model="form.employee_id" class="w-full rounded border-gray-300" placeholder="Employee ID (optional)" />

                <input v-model="form.password" type="password" class="w-full rounded border-gray-300" placeholder="Password" />
                <input v-model="form.password_confirmation" type="password" class="w-full rounded border-gray-300" placeholder="Confirm Password" />

                <label class="flex items-center gap-2 text-sm">
                    <input v-model="form.is_admin" type="checkbox" />
                    System Administrator
                </label>

                <div>
                    <p class="text-sm font-medium mb-1">Roles</p>
                    <label v-for="role in normalizedRoleOptions" :key="role.value" class="flex items-center gap-2 text-sm mb-1">
                        <input v-model="form.roles" type="checkbox" :value="role.value" />
                        {{ role.label }}
                    </label>
                </div>

                <div>
                    <p class="text-sm font-medium mb-1">Permissions</p>
                    <label v-for="permission in permissionOptions" :key="permission" class="flex items-center gap-2 text-sm mb-1">
                        <input v-model="form.permissions" type="checkbox" :value="permission" />
                        {{ formatLabel(permission) }}
                    </label>
                </div>

                <div class="flex gap-2">
                    <Link :href="route('system.users.index')" class="px-3 py-2 rounded border">Cancel</Link>
                    <button :disabled="submitting" class="px-3 py-2 rounded bg-AB text-white hover:bg-AC">Create</button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
