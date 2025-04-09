import {useForm} from "@inertiajs/vue3";
import DtoBaseClass from "@/Modules/dto/DtoBaseClass";
import {AxiosError} from "axios";
import DtoError from "@/Modules/dto/DtoError";
import DtoResponse from "@/Modules/dto/DtoResponse";

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
                    this.form = useForm(this.model.getSearchFields());
                    break;
            }
        },
        async fetchData() {
            return await this.model.getIndex(this.form.data());
        },
        async submitCreate(toCast: boolean = false, except: string = '') {
            this.form.clearErrors();
            return await this.model.postIndex(this.form.data()).then(response => {
                this.resetForm(except);
                if (toCast) {
                    return new DtoResponse(response).castDataToModel(this.model.constructor);
                }
                return new DtoResponse(response);
            }).catch(error => {
                return this.checkError(error);
            })
        },
        async submitUpdate(toCast: boolean = false, except: string = '') {
            this.form.clearErrors();
            return await this.model.putIndex(this.form.data()).then(response => {
                this.resetForm(except);
                if (toCast) {
                    return new DtoResponse(response).castDataToModel(this.model.constructor);
                }
                return new DtoResponse(response);
            }).catch(error => {
                return this.checkError(error);
            })
        },
        async submitDelete() {
            this.setFormAction('delete');
            return await this.model.deleteApiIndex(this.form.data()).then(response => {
                this.resetForm();
                return new DtoResponse(response);
            }).catch(error => {
                return this.checkError(error);
            })
        },
        resetForm(retain: string){
            const temp = this.form[retain];
            this.form.reset();
            this.form.clearErrors();
            this.form[retain] = temp;
        },
        checkError(error) {
            let dto = null;
            console.log(error);
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

                // Validation Errors
                if (dto.status === 422) {
                    Object.keys(dto.data.errors).forEach(key => {
                        this.form.setError(key, dto.data.errors[key].join(''))
                    })
                }

                // Authorization Errors
                if (dto.status === 403) {
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
        },
        toggleOption(field: string, value: any, checked: boolean) {
            if (Array.isArray(this.form[field])) {
                const index = this.form[field].indexOf(value);
                if (checked && index === -1) {
                    this.form[field].push(value);
                } else if (!checked && index !== -1) {
                    this.form[field].splice(index, 1);
                }
            } else {
                console.error(`Field ${field} does not exist or is not an array.`);
            }
        }
    }
}
