<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import axios from 'axios';
import { useNotifier } from '@/Modules/composables/useNotifier';

const { success, error: notifyError } = useNotifier();

const form = ref({
    name: '',
    email: '',
    employee_id: '',
    password: '',
    password_confirmation: '',
    is_admin: false,
    roles: [],
});

const roleOptions = [
    { value: 'admin', label: 'Admin' },
    { value: 'laboratory_manager', label: 'Laboratory Manager' },
    { value: 'ict_manager', label: 'ICT Manager' },
    { value: 'administrative_assistant', label: 'Administrative Assistant' },
];

const errors = ref({});
const submitting = ref(false);

const submit = async () => {
    submitting.value = true;
    errors.value = {};
    try {
        await axios.post(route('api.users.store'), form.value);
        success('User created successfully.');
        router.visit(route('system.users.index'));
    } catch (error) {
        errors.value = error?.response?.data?.errors || {};
        if (!Object.keys(errors.value).length) {
            notifyError('Failed to create user.');
        }
    } finally {
        submitting.value = false;
    }
};
</script>

<template>
    <AppLayout title="Create User">
        <template #header>
            <h1 class="text-xl font-semibold">Create User</h1>
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
                    <label v-for="role in roleOptions" :key="role.value" class="flex items-center gap-2 text-sm mb-1">
                        <input v-model="form.roles" type="checkbox" :value="role.value" />
                        {{ role.label }}
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
