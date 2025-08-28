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
        async fetchGetApi() {
            return await this.model.api.getApi(this.form.data(), this.model);
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
        async exportCSV(data) {
            let link = document.createElement("a");
            try {
                // Filter and map visible columns
                let columnsTitles = this.model.constructor.visibleColumns().map(column => column.title);
                let columnKeys = this.model.constructor.visibleColumns().map(column => column.key);
                // Prepare CSV content
                let csvContent = "data:text/csv;charset=utf-8,";

                // Add header row
                csvContent += columnsTitles.join(",") + "\r\n";
                // Add data rows
                data.forEach(function(rowArray) {
                    let row = columnKeys.map(column => {
                        let value = DtoBaseClass.getNestedValue(rowArray, column);
                        // Check if the value contains a comma
                        if (typeof value === 'string' && value.includes(',')) {
                            // Encapsulate the value in double quotes
                            return `"${value}"`;
                        }
                        return value;
                    }).join(",");
                    csvContent += row + "\r\n";
                });

                // Encode CSV content
                let encodedUri = encodeURI(csvContent);

                // Create download link
                link.setAttribute("href", encodedUri);
                link.setAttribute("download", `${new Date().toISOString().replace(/:/g, "-").replace(/\..+/, '')}.csv`);

                // Append link to body and trigger download
                document.body.appendChild(link);
                link.click();
            } catch (error) {
                console.log(error);
            } finally {
                // Clean up: remove link from body
                if (link) {
                    document.body.removeChild(link);
                }
            }
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
