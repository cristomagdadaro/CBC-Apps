import DtoResponse from "@/Modules/dto/DtoResponse";
import TextInput from "@/Components/TextInput.vue";
import InputError from "@/Components/InputError.vue";
import DropdownLink from "@/Components/DropdownLink.vue";
import Dropdown from "@/Components/Dropdown.vue";
import Checkbox from "@/Components/Checkbox.vue";
import TransitionContainer from "@/Components/Transitions/TransitionContrainer.vue";
import QrcodeVue, { QrcodeCanvas, QrcodeSvg } from 'qrcode.vue'
import SubmitBtn from "@/Components/Buttons/SubmitBtn.vue";
import ApiMixin from "@/Modules/mixins/ApiMixin";
import FormLocalMixin from "@/Modules/mixins/FormLocalMixin";
import CustomDropdown from "@/Components/CustomDropdown/CustomDropdown.vue";
import DataFormatterMixin from "@/Modules/mixins/DataFormatterMixin";


export default {
    mixins: [ApiMixin, FormLocalMixin, DataFormatterMixin],
    props: {
        eventId: [String, Number],
        config: Object,
    },
    components: {
        CustomDropdown,SubmitBtn,TransitionContainer, Checkbox, Dropdown, DropdownLink, InputError, TextInput, QrcodeVue, QrcodeCanvas, QrcodeSvg
    },
    watch: {
        'form.agreed_tc': {
            immediate: true,
            handler() {
                this.form?.clearErrors('agreed_tc');
            }
        }
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
                this.registrationIDHashed = response.data.participant_hash;
                this.saveLocalHashedIds(response.data);
                this.$emit('createdModel', response.data);
            }
        }
    },
}