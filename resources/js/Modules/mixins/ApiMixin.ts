import {useForm} from "@inertiajs/vue3";
import DtoBaseClass from "@/Modules/dto/DtoBaseClass";
import ConcreteApiService from "@/Modules/infrastructure/ConcreteApiService";
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
            toDelete: null,
            confirmDelete: false
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
                    this.form = useForm(this.model.deleteField(this.toDelete));
                    break;
                case "get":
                    this.form = useForm(this.model.api.getSearchFields());
                    break;
                case "summary":
                    this.form = useForm(this.model.api.getSearchFields());
                    break;
            }
        },
        async fetchData() {
            return await this.model.api.getIndex(this.form.data(), this.model);
        },
        async fetchGetApi(url: string, params?: object, model?: DtoBaseClass) {
            const api = new ConcreteApiService();
            return await api.getApi(url, params, model);
        },
        async fetchPostApi(url: string, params?: object, config?: object) {
            const api = new ConcreteApiService();
            return await api.post(url, params, config);
        },
        async submitCreate(toCast: boolean = false, except: string = '') {
            this.form.clearErrors();
            return await this.model.api.postIndex(this.form.data()).then(response => {
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
            return await this.model.api.putIndex(this.form.data()).then(response => {
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
            return await this.model.api.deleteApiIndex(this.form.data()).then(response => {
                this.resetForm();
                return new DtoResponse(response);
            }).catch(error => {
                return this.checkError(error);
            })
        },
        async exportCSV(data: Array<any>, filename: string = null) {
            let link = document.createElement("a");
            try {
                // Filter and map visible columns
                let columnsTitles = this.model.constructor.visibleColumns().map(column => column.title);
                let columnKeys = this.model.constructor.visibleColumns().map(column => column.key);

                // Add header row
                let csvContent = columnsTitles.join(",") + "\r\n";

                // Add data rows
                data.forEach(function(rowArray) {
                    let row = columnKeys.map(column => {
                        let value = DtoBaseClass.getNestedValue(rowArray, column);

                        // Handle null/undefined
                        if (value == null) return "";

                        // Escape quotes + commas
                        value = value.toString().replace(/"/g, '""');
                        if (value.includes(",") || value.includes("\n")) {
                            return `"${value}"`;
                        }
                        return value;
                    }).join(",");

                    csvContent += row + "\r\n";
                });

                // Add BOM to force Excel to read as UTF-8
                const BOM = "\uFEFF";
                const blob = new Blob([BOM + csvContent], { type: "text/csv;charset=utf-8;" });

                // Create download link
                const url = URL.createObjectURL(blob);
                link.setAttribute("href", url);
                link.setAttribute("download", `${filename} ${new Date().toISOString().replace(/:/g, "-").replace(/\..+/, '')}.csv`);

                document.body.appendChild(link);
                link.click();

                // Clean up object URL
                URL.revokeObjectURL(url);

            } catch (error) {
                console.log(error);
            } finally {
                if (link) {
                    document.body.removeChild(link);
                }
            }

            return null;
        },
        resetForm(retain: string | null = null){
            let temp = null;
            if (retain && this.form && Object.prototype.hasOwnProperty.call(this.form, retain)) {
                temp = this.form[retain];
            }
            this.form.reset();
            this.form.clearErrors();
            if (retain && temp !== null) {
                this.form[retain] = temp;
            }
        },
        formatNumber(value){
            if (value === null || value === undefined) return '';
            return value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        },
        checkError(error) {
            let dto = null;
            console.error(error);
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
                        const message = dto.data.errors[key].join('');
                        this.form.setError(key, message);
                        ['response_data.', 'report_data.'].forEach(prefix => {
                            if (key.startsWith(prefix)) {
                                const shortKey = key.replace(prefix, '');
                                this.form.setError(shortKey, message);
                            }
                        });
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
        },
        resetField(def: Object) {
            this.form = useForm(def);
        }
    }
}
