<script>
import { useForm } from '@inertiajs/vue3';
import MainBg from '../Shared/MainBg.vue';

export default {
    name: 'ForgotPassword',
    components: {
        MainBg,
    },
    props: {
        status: {
            type: String,
            default: null,
        },
    },
    data() {
        return {
            form: useForm({
                email: '',
            }),
        };
    },
    methods: {
        submit() {
            this.form.post(route('password.email'));
        },
    },
};
</script>

<template>
    <Head title="Forgot Password" />
    <main-bg />
    <div class="absolute top-0 left-0 w-full ">
        <AuthenticationCard>
            <template #logo>
                <AuthenticationCardLogo />
            </template>

            <div class="mb-4 text-sm text-gray-600 dark:text-gray-400">
                Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.
            </div>

            <div v-if="status" class="mb-4 font-medium text-sm text-green-600 dark:text-green-400">
                {{ status }}
            </div>

            <form @submit.prevent="submit">
                <div>
                    <InputLabel for="email" value="Email" />
                    <TextInput
                        id="email"
                        v-model="form.email"
                        type="email"
                        class="mt-1 block w-full"
                        required
                        autofocus
                        autocomplete="username"
                    />
                    <InputError class="mt-2" :message="form.errors.email" />
                </div>

                <div class="flex items-center justify-end mt-4">
                    <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                        Email Password Reset Link
                    </PrimaryButton>
                </div>
            </form>
        </AuthenticationCard>
    </div>
</template>
