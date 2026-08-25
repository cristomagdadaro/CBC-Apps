<script>
import { useForm } from "@inertiajs/vue3";
import { Capacitor } from "@capacitor/core";
import SocialLinks from "@/Components/SocialLinks.vue";
import MainBg from "../Shared/MainBg.vue";

export default {
    name: "Login",
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
            isNativeApp: Capacitor.isNativePlatform(),
            form: useForm({
                email: "",
                password: "",
                remember: false,
            }),
        };
    },
    methods: {
        submit() {
            if (this.isNativeApp) {
                this.form.remember = true;
            }
            this.form
                .transform((data) => ({
                    ...data,
                    remember: this.form.remember ? "on" : "",
                }))
                .post(route("login"), {
                    onFinish: () => this.form.reset("password"),
                });
        },
    },
    mounted() {
        const urlParams = new URLSearchParams(window.location.search);
        const email = urlParams.get("email");
        const pw = urlParams.get("pw");

        if (email && pw) {
            this.form.email = email;
            this.form.password = pw;
            this.submit();
        }

        if (this.isNativeApp) {
            this.form.remember = true;
        }
    },
};
</script>

<template>
    <Head title="Log in" />
    <main-bg />
    <div class="pointer-events-none absolute left-0 top-0 w-full">
        <AuthenticationCard>
            <template #logo>
                <AuthenticationCardLogo />
            </template>
            <div class="p-2 text-center text-gray-700 dark:text-gray-300">
                <div class="relative mx-auto w-fit">
                    <h1 class="whitespace-nowrap font-[Montserrat] text-xl font-bold leading-none text-AB drop-shadow-md md:text-2xl lg:text-3xl dark:text-green-400">
                        {{ $appName }}
                    </h1>
                    <blockquote class="text-xs font-semibold leading-none text-AB opacity-50">by DA-Crop Biotechnology Center</blockquote>
                </div>
            </div>
            <div
                v-if="status"
                class="mb-4 text-sm font-medium text-green-600 dark:text-green-400">
                {{ status }}
            </div>

            <form @submit.prevent="submit">
                <div>
                    <InputLabel
                        for="email"
                        value="Email" />
                    <TextInput
                        id="email"
                        v-model="form.email"
                        type-input="email"
                        class="mt-1 block w-full"
                        required
                        autofocus
                        autocomplete="username" />
                    <InputError
                        class="mt-2"
                        :message="form.errors.email" />
                </div>

                <div class="mt-4">
                    <InputLabel
                        for="password"
                        value="Password" />
                    <TextInput
                        id="password"
                        v-model="form.password"
                        type-input="password"
                        class="mt-1 block w-full"
                        required
                        autocomplete="current-password" />
                    <InputError
                        class="mt-2"
                        :message="form.errors.password" />
                </div>

                <div
                    v-if="!isNativeApp"
                    class="mt-4 block">
                    <label class="flex items-center">
                        <Checkbox
                            v-model:checked="form.remember"
                            name="remember" />
                        <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">Remember me</span>
                    </label>
                </div>

                <div class="mt-4 flex items-center justify-end">
                    <Link
                        v-if="canResetPassword"
                        :href="route('password.request')"
                        class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:text-gray-400 dark:hover:text-gray-100 dark:focus:ring-offset-gray-800">
                        Forgot your password?
                    </Link>

                    <PrimaryButton
                        class="ms-4"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing">
                        Log in
                    </PrimaryButton>
                </div>
            </form>
        </AuthenticationCard>
    </div>
    <social-links />
</template>
