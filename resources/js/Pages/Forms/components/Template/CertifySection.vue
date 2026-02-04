<script>
import Checkbox from '@/Components/Checkbox.vue'
import InputError from '@/Components/InputError.vue'
import TransitionContainer from "@/Components/Transitions/TransitionContrainer.vue";

export default {
    name: 'CertifySection',
    components: {
        Checkbox,
        InputError,
        TransitionContainer,
    },

    props: {
        agreed_tc: {
            type: Boolean,
            required: true,
        },
        agreed_updates: {
            type: Boolean,
            required: true,
        },
        errors: {
            type: Object,
            required: true,
        },
    },

    emits: ['update:agreed_tc', 'update:agreed_updates'],

    computed: {
        agreedTcModel: {
            get() {
                return this.agreed_tc
            },
            set(value) {
                this.$emit('update:agreed_tc', value)
            },
        },
        agreedUpdatesModel: {
            get() {
                return this.agreed_updates
            },
            set(value) {
                this.$emit('update:agreed_updates', value)
            },
        },
    },
}
</script>

<template>
    <div class="flex flex-col">
        <div class="py-3 flex gap-2 items-start">
            <Checkbox
                id="agreed_tc"
                v-model="agreedTcModel"
                :class="{ 'border border-red-600': errors.agreed_tc }"
            />

            <label
                for="agreed_tc"
                class="text-xs leading-snug cursor-pointer select-none"
            >
                I hereby certify that the information provided is true, correct, and complete. I authorize the Department of Agriculture – Crop Biotechnology Center (DA-CBC) to collect, process, store, update, and manage my personal data in accordance with Republic Act No. 10173 (Data Privacy Act of 2012) for legitimate purposes related to its programs and web applications.

                <transition-container type="slide-bottom">
                    <InputError
                        v-if="errors.agreed_tc"
                        :message="errors.agreed_tc"
                    />
                </transition-container>
            </label>
        </div>

        <div class="py-3 flex gap-2 items-start">
            <Checkbox
                id="agreed_updates"
                v-model="agreedUpdatesModel"
                :class="{ 'border border-red-600': errors.agreed_updates }"
            />

            <label
                for="agreed_updates"
                class="text-xs leading-snug cursor-pointer select-none"
            >
                I consent to receive official updates, announcements, and program-related communications from the DA–Crop Biotechnology Center through my registered email address, mobile number, and/or messaging applications.

                <transition-container type="slide-bottom">
                    <InputError
                        v-if="errors.agreed_updates"
                        :message="errors.agreed_updates"
                    />
                </transition-container>
            </label>
        </div>
    </div>
</template>
