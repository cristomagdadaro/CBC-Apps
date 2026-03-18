<script>
import { useForm } from '@inertiajs/vue3';
import SocialLinks from "@/Components/SocialLinks.vue";
import MainBg from '../Shared/MainBg.vue';

export default {
    name: 'Login',
    components: {
        SocialLinks,
        MainBg,
    },
    props: {
        canResetPassword: Boolean,
        status: String,
    },
    data() {
        return {
            form: useForm({
                email: '',
                password: '',
                remember: false,
            })
        }
    },
    methods: {
        submit() {
            this.form.transform(data => ({
                ...data,
                remember: this.form.remember ? 'on' : '',
            })).post(route('login'), {
                onFinish: () => this.form.reset('password'),
            });
        }
    }
}
</script>

<template>
    <Head title="Log in" />
    <main-bg />
    <div class="absolute top-0 left-0 w-full ">
        <AuthenticationCard>
            <template #logo>
                <AuthenticationCardLogo />
            </template>
            <div class="text-center text-gray-700 dark:text-gray-300 p-2">
                <div class="relative w-fit mx-auto">
                    <h1 class="lg:text-3xl md:text-2xl text-xl font-bold leading-none text-AB dark:text-green-400 font-[Montserrat] drop-shadow-md whitespace-nowrap">
                        {{ $appName }}
                    </h1>
                    <blockquote class="font-semibold text-AB leading-none text-xs opacity-50">by DA-Crop Biotechnology Center</blockquote>
                </div>

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
                        type-input="email"
                        class="mt-1 block w-full"
                        required
                        autofocus
                        autocomplete="username"
                    />
                    <InputError class="mt-2" :message="form.errors.email" />
                </div>

                <div class="mt-4">
                    <InputLabel for="password" value="Password" />
                    <TextInput
                        id="password"
                        v-model="form.password"
                        type-input="password"
                        class="mt-1 block w-full"
                        required
                        autocomplete="current-password"
                    />
                    <InputError class="mt-2" :message="form.errors.password" />
                </div>

                <div class="block mt-4">
                    <label class="flex items-center">
                        <Checkbox v-model:checked="form.remember" name="remember" />
                        <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">Remember me</span>
                    </label>
                </div>

                <div class="flex items-center justify-end mt-4">
                    <Link v-if="canResetPassword" :href="route('password.request')" class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">
                        Forgot your password?
                    </Link>

                    <PrimaryButton class="ms-4" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                        Log in
                    </PrimaryButton>
                </div>
            </form>
        </AuthenticationCard>
    </div>
    <social-links />
</template>
