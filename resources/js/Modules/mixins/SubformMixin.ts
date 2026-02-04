import DtoResponse from "@/Modules/dto/DtoResponse";
import QrcodeVue, { QrcodeCanvas, QrcodeSvg } from 'qrcode.vue'
import ApiMixin from "@/Modules/mixins/ApiMixin";
import FormLocalMixin from "@/Modules/mixins/FormLocalMixin";
import DataFormatterMixin from "@/Modules/mixins/DataFormatterMixin";
import CertifySection from "@/Pages/Forms/components/Template/CertifySection.vue";

export default {
    mixins: [ApiMixin, FormLocalMixin, DataFormatterMixin],
    props: {
        eventId: [String, Number],
        config: Object,
    },
    components: { QrcodeVue, QrcodeCanvas, QrcodeSvg, CertifySection },
    watch: {
        'form.agreed_tc': {
            immediate: true,
            handler() {
                this.form?.clearErrors('agreed_tc');
            }
        },
        'form.agreed_updates': {
            immediate: true,
            handler() {
                this.form?.clearErrors('agreed_updates');
            }
        },
    },
    data() {
        return {
            model: null,
            showSuccess: false,
            registrationIDHashed: null,
            value: 'https://example.com',
            size: 200,
        }
    },
    methods: {
        async handleCreate() {
            const response = await this.submitCreate(null, 'response_data');
            this.showSuccess = response.status === 201;
            if (response instanceof DtoResponse) {
                //@ts-ignore
                this.registrationIDHashed = response.data.participant_hash;
                this.saveLocalHashedIds(response.data);
                this.$emit('createdModel', response.data);
            }
        }
    },
}