<script>
import { useForm } from '@inertiajs/vue3';

export default {
    name: 'ConfirmPassword',
    data() {
        return {
            form: useForm({ password: '' }),
        };
    },
    mounted() {
        this.$refs.passwordInput?.focus();
    },
    methods: {
        submit() {
            this.form.post(route('password.confirm'), {
                onFinish: () => {
                    this.form.reset();
                    this.$refs.passwordInput?.focus();
                },
            });
        },
    },
};
</script>

<template>
    <Head title="Secure Area" />

    <AuthenticationCard>
        <template #logo>
            <AuthenticationCardLogo />
        </template>

        <div class="mb-4 text-sm text-gray-600 dark:text-gray-400">
            This is a secure area of the application. Please confirm your password before continuing.
        </div>

        <form @submit.prevent="submit">
            <div>
                <InputLabel for="password" value="Password" />
                <TextInput
                    id="password"
                    ref="passwordInput"
                    v-model="form.password"
                    type="password"
                    class="mt-1 block w-full"
                    required
                    autocomplete="current-password"
                    autofocus
                />
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="flex justify-end mt-4">
                <PrimaryButton class="ms-4" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    Confirm
                </PrimaryButton>
            </div>
        </form>
    </AuthenticationCard>
</template>
