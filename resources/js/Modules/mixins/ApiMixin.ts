import {useForm} from "@inertiajs/vue3";
import DtoBaseClass from "@/Modules/dto/DtoBaseClass";
import {AxiosError} from "axios";
import DtoError from "@/Modules/dto/DtoError";

export default {
    props: {
        data: {
            type: Object,
            default: null
        },
    },
    data() {
        return {
            model: DtoBaseClass,
            form: null,
        }
    },
    methods: {
        setFormAction(action: string) {
            switch (action) {
                case "create":
                    this.form = useForm(this.model.createFields);
                    break;
                case "update":
                    this.form = useForm(this.model.updateFields(this.data));
                    break;
                case "delete":
                    this.form = useForm(this.model.deleteField(this.data));
                    break;
                case "get":
                    this.form = useForm(this.model.getFields);
                    break;
            }
        },
        async fetchData() {
            return await this.model.getIndex(this.form.data());
        },
        async submitCreate() {
            await this.model.postIndex(this.form.data()).then(response => {
                this.form.reset();
                this.form.clearErrors();
            }).catch(error => {
                return this.checkError(error);
            })
        },
        async submitUpdate() {
            await this.model.putIndex(this.form.data()).then(response => {
                this.form.reset();
                this.form.clearErrors();
            }).catch(error => {
                return this.checkError(error);
            })
        },
        async submitDelete() {
            this.setFormAction('delete');
            await this.model.deleteApiIndex(this.form.data()).then(response => {
                this.form.reset();
                this.form.clearErrors();
                this.$emit('deletedModel', response);
            }).catch(error => {
                return this.checkError(error);
            })
        },
        checkError(error) {
            let dto = null;
            if (error instanceof TypeError)
            {
                dto = new DtoError({
                    title: error.name,
                    message: error.message
                });
            }
            else if (error instanceof AxiosError)
            {
                dto = new DtoError({
                    title: error.name,
                    message: error.response.data.message,
                    data: error.response.data,
                    status: error.response.status,
                });

                if (dto.status === 422){
                    Object.keys(dto.data.errors).forEach(key => {
                        this.form.setError(key, dto.data.errors[key].join(''))
                    })
                }
            }
            else {
                dto = new DtoError({
                    title: error.name,
                    message: error.message
                })
            }
            console.log(dto.toObject());
            return dto;
        }
    }
}
