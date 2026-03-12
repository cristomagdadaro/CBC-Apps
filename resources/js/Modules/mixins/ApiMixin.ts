import {useForm} from "@inertiajs/vue3";
import DtoBaseClass from "@/Modules/dto/DtoBaseClass";
import ConcreteApiService from "@/Modules/infrastructure/ConcreteApiService";
import {AxiosError} from "axios";
import DtoError from "@/Modules/dto/DtoError";
import DtoResponse from "@/Modules/dto/DtoResponse";
import { useNotifier } from '@/Modules/composables/useNotifier';

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
            confirmDelete: false,
            processing: false,
        }
    },
    computed: {
        suppliers() {
            return this.$page.props.suppliers;
        },
        categories() {
            return this.$page.props.categories;
        },
        projectCodes() {
            return this.$page.props.projectCodes;
        },
        storage_locations() {
            if (!Array.isArray(this.$page.props.storage_locations)) {
                return [];
            }

            return this.$page.props.storage_locations.map(location => ({
                name: location.name,
                label: location.label,
            }));
        },
    },
    methods: {
        createFormWithRemember(payload: object, action: string) {
            const rememberKey = this.rememberFormKey
                ? `${this.rememberFormKey}.${action}`
                : null;

            if (rememberKey) {
                return useForm(rememberKey, payload);
            }

            return useForm(payload);
        },
        setFormAction(action: string) {
            switch (action) {
                case "create":
                    this.form = this.createFormWithRemember(this.model.createFields, action);
                    break;
                case "update":
                    this.form = this.createFormWithRemember(this.model.updateFields(this.data), action);
                    break;
                case "delete":
                    this.form = this.createFormWithRemember(this.model.deleteField(this.toDelete), action);
                    break;
                case "get":
                    this.form = this.createFormWithRemember(this.model.api.getSearchFields(), action);
                    break;
                case "show":
                    this.form = this.createFormWithRemember(this.model.updateFields(this.data), action);
                    break;
                case "summary":
                    this.form = this.createFormWithRemember(this.model.api.getSearchFields(), action);
                    break;
            }


            return this.form;
        },
        async fetchData() {
            this.processing = true;
            return await this.model.api.getIndex(this.form.data(), this.model).finally(() => {
                this.processing = false;
            });
        },
        async fetchGetApi(url: string, params?: object, model?: DtoBaseClass) {
            this.processing = true;
            const api = new ConcreteApiService();
            return await api.getApi(url, params, model).finally(() => {
                this.processing = false;
            });
        },
        async fetchPostApi(url: string, params?: object, config?: object) {
            this.processing = true;
            const api = new ConcreteApiService();
            return await api.post(url, params, config).finally(() => {
                this.processing = false;
            });
        },
        async fetchPutApi(url: string, id?: any, params?: object) {
            this.processing = true;
            const api = new ConcreteApiService();
            return await api.put(url, id, params).finally(() => {
                this.processing = false;
            });
        },
        async fetchDeleteApi(url: string, id?: any) {
            this.processing = true;
            const api = new ConcreteApiService();
            return await api.delete(url, id).finally(() => {
                this.processing = false;
            });
        },
        async submitCreate(toCast: boolean = false, except:  string | string[] | null = null) {
            this.processing = true;
            this.form.clearErrors();
            return await this.model.api.postIndex(this.form.data()).then(response => {
                this.resetForm(except);
                if (toCast) {
                    return new DtoResponse(response).castDataToModel(this.model.constructor);
                }
                return new DtoResponse(response);
            }).catch(error => {
                return this.checkError(error);
            }).finally(() => {
                this.processing = false;
            });
        },
        async submitUpdate(toCast: boolean = false, except:  string | string[] | null = null) {
            this.processing = true;
            this.form.clearErrors();
            return await this.model.api.putIndex(this.form.data()).then(response => {
                this.resetForm(except);
                if (toCast) {
                    return new DtoResponse(response).castDataToModel(this.model.constructor);
                }
                return new DtoResponse(response);
            }).catch(error => {
                return this.checkError(error);
            }).finally(() => {
                this.processing = false;
            });
        },
        async submitDelete() {
            this.processing = true;
            this.setFormAction('delete');
            return await this.model.api.deleteApiIndex(this.form.data()).then(response => {
                this.resetForm();
                return new DtoResponse(response);
            }).catch(error => {
                return this.checkError(error);
            }).finally(() => {
                this.processing = false;
            });
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
                
            } finally {
                if (link) {
                    document.body.removeChild(link);
                }
            }

            return null;
        },
        resetForm(retain: string | string[] | null = null) {
            if (!this.form) return;

            const retainKeys = Array.isArray(retain)
                ? retain
                : retain
                    ? [retain]
                    : [];

            const retainedValues: Record<string, any> = {};

            // store retained values
            retainKeys.forEach(key => {
                if (Object.prototype.hasOwnProperty.call(this.form, key)) {
                    retainedValues[key] = this.form[key];
                }
            });

            this.form.reset();
            this.form.clearErrors();

            // restore retained values
            Object.entries(retainedValues).forEach(([key, value]) => {
                this.form[key] = value;
            });
        },
        formatNumber(value){
            if (value === null || value === undefined) return '';
            return value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
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
                try {
                    const { error: notifyError } = useNotifier();
                    if (dto.status === 422) {
                        notifyError('Please check the form fields and try again.');
                    } else {
                        notifyError(dto.message || 'An error occurred while processing the request.');
                    }
                } catch (e) {
                    // notifier may not be available here
                }
            }
            else {
                dto = new DtoError({
                    title: error.name,
                    message: error.message
                })
            }

            try {
                const { error: notifyError } = useNotifier();
                notifyError(dto.message || dto.title || 'An error occurred.');
            } catch (e) {
                // ignore notifier failures
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
