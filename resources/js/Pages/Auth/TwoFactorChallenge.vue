<script>
import { nextTick } from "vue";
import { useForm } from "@inertiajs/vue3";

export default {
  name: "TwoFactorChallenge",
  data() {
    return {
      recovery: false,
      form: useForm({
        code: "",
        recovery_code: "",
      }),
    };
  },
  mounted() {
    this.recoveryCodeInput = null;
    this.codeInput = null;
  },
  methods: {
    async toggleRecovery() {
      this.recovery = !this.recovery;
      await nextTick();
      if (this.recovery) {
        if (this.$refs.recoveryCodeInput) this.$refs.recoveryCodeInput.focus();
        this.form.code = "";
      } else {
        if (this.$refs.codeInput) this.$refs.codeInput.focus();
        this.form.recovery_code = "";
      }
    },
    submit() {
      this.form.post(route("two-factor.login"));
    },
  },
};
</script>

<template>
  <Head title="Two-factor Confirmation" />

  <AuthenticationCard>
    <template #logo>
      <AuthenticationCardLogo />
    </template>

    <div class="mb-4 text-sm text-gray-600 dark:text-gray-400">
      <template v-if="!recovery">
        Please confirm access to your account by entering the authentication code provided
        by your authenticator application.
      </template>

      <template v-else>
        Please confirm access to your account by entering one of your emergency recovery
        codes.
      </template>
    </div>

    <form @submit.prevent="submit">
      <div v-if="!recovery">
        <InputLabel for="code" value="Code" />
        <TextInput
          id="code"
          ref="codeInput"
          v-model="form.code"
          type="text"
          inputmode="numeric"
          class="mt-1 block w-full"
          autofocus
          autocomplete="one-time-code"
        />
        <InputError class="mt-2" :message="form.errors.code" />
      </div>

      <div v-else>
        <InputLabel for="recovery_code" value="Recovery Code" />
        <TextInput
          id="recovery_code"
          ref="recoveryCodeInput"
          v-model="form.recovery_code"
          type="text"
          class="mt-1 block w-full"
          autocomplete="one-time-code"
        />
        <InputError class="mt-2" :message="form.errors.recovery_code" />
      </div>

      <div class="flex items-center justify-end mt-4">
        <button
          type="button"
          class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 underline cursor-pointer"
          @click.prevent="toggleRecovery"
        >
          <template v-if="!recovery"> Use a recovery code </template>

          <template v-else> Use an authentication code </template>
        </button>

        <PrimaryButton
          class="ms-4"
          :class="{ 'opacity-25': form.processing }"
          :disabled="form.processing"
        >
          Log in
        </PrimaryButton>
      </div>
    </form>
  </AuthenticationCard>
</template>
