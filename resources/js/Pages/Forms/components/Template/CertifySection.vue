<script>
export default {
    name: "CertifySection",
    props: {
        agreed_tc: {
            type: [Boolean, String, Number],
            default: false,
        },
        agreed_updates: {
            type: [Boolean, String, Number],
            default: false,
        },
        errors: {
            type: Object,
            required: true,
        },
    },

    emits: ["update:agreed_tc", "update:agreed_updates"],

    computed: {
        agreedTcModel: {
            get() {
                return this.agreed_tc;
            },
            set(value) {
                this.$emit("update:agreed_tc", value);
            },
        },
        agreedUpdatesModel: {
            get() {
                return this.agreed_updates;
            },
            set(value) {
                this.$emit("update:agreed_updates", value);
            },
        },
    },
};
</script>

<template>
    <div class="flex flex-col">
        <div class="flex items-start gap-2 py-3">
            <Checkbox
                id="agreed_tc"
                v-model="agreedTcModel"
                :checked="agreedTcModel"
                :class="{ 'border border-red-600': errors.agreed_tc }" />

            <label
                for="agreed_tc"
                class="cursor-pointer text-xs leading-snug">
                I hereby certify that the information provided is true, correct, and complete. I authorize the
                <a
                    href="https://dacbc.philrice.gov.ph/"
                    target="_blank">
                    Department of Agriculture – Crop Biotechnology Center (DA-CBC)
                </a>
                to collect, process, store, update, and manage my personal data in accordance with Republic Act No. 10173 (Data Privacy Act of 2012) for legitimate purposes related to its programs and web applications.

                <transition-container type="slide-bottom">
                    <InputError
                        v-if="errors.agreed_tc"
                        :message="errors.agreed_tc" />
                </transition-container>
            </label>
        </div>

        <div class="flex items-start gap-2 py-3">
            <Checkbox
                id="agreed_updates"
                v-model="agreedUpdatesModel"
                :checked="agreedUpdatesModel"
                :class="{ 'border border-red-600': errors.agreed_updates }" />

            <label
                for="agreed_updates"
                class="cursor-pointer text-xs leading-snug">
                I consent to receive official updates, announcements, and program-related communications from the DA–Crop Biotechnology Center through my registered email address, mobile number, and/or messaging applications.

                <transition-container type="slide-bottom">
                    <InputError
                        v-if="errors.agreed_updates"
                        :message="errors.agreed_updates" />
                </transition-container>
            </label>
        </div>
    </div>
</template>
