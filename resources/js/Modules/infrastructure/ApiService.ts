import axios, {AxiosError, AxiosResponse} from "axios";
import DtoBaseClass from "@/Modules/dto/DtoBaseClass";
import ConsoleLogger from "@/Modules/shared/infrastructure/ConsoleLogger";

export default abstract class ApiService {
    public axiosInstance: any;
    public processing: boolean = false;
    private csrfReady: boolean = false;

    public _apiIndex: string;
    public _apiPost: string;
    public _apiPut: string;
    public _apiDelete: string;
    public _apiGet: string;

    public _appendedWith?: string[];
    public _appendedCount?: string[];

    private _SearchFields: object = {};

    protected constructor() {
        this.axiosInstance = axios.create({
            withCredentials: true,
            xsrfCookieName: 'XSRF-TOKEN',
            xsrfHeaderName: 'X-XSRF-TOKEN',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        if (typeof document !== 'undefined') {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            if (csrfToken) {
                this.axiosInstance.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;
                this.csrfReady = true;
            }
        }

        // default search fields
        this._SearchFields = {
            search: null,
            filter: null,
            filter_by: null,
            is_exact: false,
            page: 1,
            per_page: 10
        };
    }

    private async ensureCsrfProtection(): Promise<void> {
        if (this.csrfReady) {
            return;
        }

        if (typeof window === 'undefined') {
            return;
        }

        try {
            await this.axiosInstance.get('/sanctum/csrf-cookie');
            this.csrfReady = true;
            ConsoleLogger.debug('CSRF cookie prefetched successfully.');
        } catch (error) {
            ConsoleLogger.warn(error);
            ConsoleLogger.log('Proceeding with current headers.');
        }
    }

    private isBinaryValue(value: any): boolean {
        return value instanceof File || value instanceof Blob;
    }

    private hasBinaryValue(payload: any): boolean {
        if (payload == null) {
            return false;
        }

        if (this.isBinaryValue(payload)) {
            return true;
        }

        if (payload instanceof FormData) {
            let containsBinary = false;
            payload.forEach((value) => {
                if (this.isBinaryValue(value)) {
                    containsBinary = true;
                }
            });
            return containsBinary;
        }

        if (Array.isArray(payload)) {
            return payload.some((entry) => this.hasBinaryValue(entry));
        }

        if (typeof payload === 'object') {
            return Object.values(payload).some((entry) => this.hasBinaryValue(entry));
        }

        return false;
    }

    private appendToFormData(formData: FormData, key: string, value: any) {
        if (value === undefined) {
            return;
        }

        if (value === null) {
            formData.append(key, '');
            return;
        }

        if (this.isBinaryValue(value)) {
            formData.append(key, value);
            return;
        }

        if (Array.isArray(value)) {
            value.forEach((item, index) => {
                this.appendToFormData(formData, `${key}[${index}]`, item);
            });
            return;
        }

        if (typeof value === 'object') {
            Object.entries(value).forEach(([childKey, childValue]) => {
                this.appendToFormData(formData, `${key}[${childKey}]`, childValue);
            });
            return;
        }

        if (typeof value === 'boolean') {
            formData.append(key, value ? '1' : '0');
            return;
        }

        formData.append(key, String(value));
    }

    private buildFormData(payload: Record<string, any>): FormData {
        const formData = new FormData();
        Object.entries(payload || {}).forEach(([key, value]) => {
            this.appendToFormData(formData, key, value);
        });
        return formData;
    }

    private notifyError(error: unknown, fallbackMessage: string = 'Request failed. Please try again.') {
        if (typeof window === 'undefined') {
            return;
        }

        const axiosError = error as AxiosError<any>;
        const status = axiosError?.response?.status;
        const responseMessage = axiosError?.response?.data?.message;

        const message =
            status === 401
                ? 'Unauthorized request. Please sign in again.'
                : status === 403
                    ? 'Access denied for this action.'
                    : status === 404
                        ? 'Requested resource was not found.'
                        : status === 413
                            ? 'Upload is too large for the server. Please reduce file size and try again.'
                        : status === 422
                            ? 'Some fields are invalid. Please review your input.'
                            : responseMessage || fallbackMessage;

        window.dispatchEvent(
            new CustomEvent('cbc:notify', {
                detail: {
                    type: 'error',
                    message,
                },
            })
        );
    }

    protected notifySuccess(message: string = 'Operation completed successfully.') {
        if (typeof window === 'undefined') {
            return;
        }

        window.dispatchEvent(
            new CustomEvent('cbc:notify', {
                detail: {
                    type: 'success',
                    message,
                },
            })
        );
    }

    protected notifyWarning(message: string = 'Please note.') {
        if (typeof window === 'undefined') {
            return;
        }

        window.dispatchEvent(
            new CustomEvent('cbc:notify', {
                detail: {
                    type: 'warning',
                    message,
                },
            })
        );
    }

    async get(url: string, params?: any, model?: DtoBaseClass) {
        this.processing = true;
        let routeUrl: string | null = null;
        let cleanedParams: any = params ? { ...params } : undefined;
        try {
            const routeParams = params?.routeParams;
            if (cleanedParams && Object.prototype.hasOwnProperty.call(cleanedParams, 'routeParams')) {
                delete cleanedParams.routeParams;
            }
            
            const id = model ? model.apiGet : null;
            // @ts-ignore
            routeUrl = id
                ? route(url, id)
                : routeParams !== undefined
                    // @ts-ignore
                    ? route(url, routeParams)
                    // @ts-ignore
                    : route(url);

            const response = await this.axiosInstance.get(routeUrl, {
                params: {
                    ...cleanedParams,
                    ...(model?.api?.appendedWith && Array.isArray(model?.api?.appendedWith)
                        ? { with: model.api.appendedWith.toString() }
                        : {}),
                    ...(model?.api?.appendedCount && Array.isArray(model?.api?.appendedCount)
                        ? { count: model.api.appendedCount.toString() }
                        : {})
                }
            }).then((response: AxiosResponse) => {
                ConsoleLogger.debug({
                    tag: 'API_GET',
                    status: response.status,
                    data: response.data,
                });
                if (model) {
                    response.data.data = this.castToModel(response.data.data, model);
                }
                return response;
            });
            this.processing = false;
            return response.data;
        } catch (error) {
            this.processing = false;
            ConsoleLogger.error({
                tag: 'API_GET_ERROR',
                url: routeUrl,
                params: cleanedParams,
                error: error,
            });
            this.notifyError(error, 'Failed to load data.');
            throw error;
        }
    }

    async post(url: string, params?: any, config?: any) {
        this.processing = true;
        const routeParams = config?.routeParams;
        const cleanConfig = config ? { ...config } : undefined;
        if (cleanConfig && Object.prototype.hasOwnProperty.call(cleanConfig, 'routeParams')) {
            delete cleanConfig.routeParams;
        }
        // @ts-ignore
        const postUrl = routeParams !== undefined ? route(url, routeParams) : route(url);
        try {
            await this.ensureCsrfProtection();
            ConsoleLogger.debug({
                tag: 'API_POST',
                params: params,
            });

            const shouldUseMultipart = !(params instanceof FormData) && this.hasBinaryValue(params);
            const payload = shouldUseMultipart ? this.buildFormData(params || {}) : params;
            const requestConfig = shouldUseMultipart
                ? {
                    ...cleanConfig,
                    headers: {
                        ...(cleanConfig?.headers || {}),
                        'Content-Type': 'multipart/form-data',
                    },
                }
                : cleanConfig;
            // @ts-ignore
            const response = await this.axiosInstance.post(
                // @ts-ignore
                postUrl,
                payload,
                requestConfig
            );
            this.processing = false;
            ConsoleLogger.log({
                tag: 'API_POST_RESPONSE',
                status: response.status,
                data: response.data,
            });
            this.notifySuccess('Data submitted successfully.');
            return response;
        } catch (error) {
            this.processing = false;
            ConsoleLogger.error({
                tag: 'API_POST_ERROR',
                url: postUrl,
                params: params,
                error: error,
            });
            this.notifyError(error, 'Failed to submit data.');
            throw error;
        }
    }

    async put(url: string, id: any, params?: any) {

        this.processing = true;
        try {
            await this.ensureCsrfProtection();
            ConsoleLogger.debug({
                tag: 'API_PUT',
                id: id,
                params: params,
            });
            let response: any = null;
            const shouldUseMultipart = !(params instanceof FormData) && this.hasBinaryValue(params);

            if (shouldUseMultipart) {
                const formData = this.buildFormData(params || {});
                formData.append('_method', 'PUT');
                if (!id) {
                    response = await this.axiosInstance.post(`${route(url)}`, formData, {
                        headers: {
                            'Content-Type': 'multipart/form-data',
                        },
                    });
                } else {
                    response = await this.axiosInstance.post(`${route(url, id)}`, formData, {
                        headers: {
                            'Content-Type': 'multipart/form-data',
                        },
                    });
                }
            } else if (!id) {
               response = await this.axiosInstance.put(`${route(url)}`, params);
            } else {
               response = await this.axiosInstance.put(`${route(url, id)}`, params);
            }
            this.processing = false;
            ConsoleLogger.log({
                tag: 'API_PUT_RESPONSE',
                status: response.status,
                data: response.data,
            });
            this.notifySuccess('Data updated successfully.');
            return response;
        } catch (error) {
            this.processing = false;
            ConsoleLogger.error({
                tag: 'API_PUT_ERROR',
                url: !id ? route(url) : route(url, id),
                id: id,
                params: params,
                error: error,
            });
            this.notifyError(error, 'Failed to update data.');
            throw error;
        }
    }

    async delete(url: string, id: any, data?: any) {
        this.processing = true;
        try {
            await this.ensureCsrfProtection();
            ConsoleLogger.debug({
                tag: 'API_DELETE',
                id: id,
                data: data,
            });
            const config = data && Object.keys(data).length
                ? { data }
                : undefined;
            const response = await this.axiosInstance.delete(route(url, id), config);
            this.processing = false;
            ConsoleLogger.log({
                tag: 'API_DELETE_RESPONSE',
                status: response.status,
                data: response.data,
            });
            this.notifyWarning('Data deleted successfully.');
            return response;
        } catch (error) {
            this.processing = false;
            ConsoleLogger.error({
                tag: 'API_DELETE_ERROR',
                url: route(url, id),
                id: id,
                error: error,
            });
            this.notifyError(error, 'Failed to delete data.');
            throw error;
        }
    }

    private getDeletePayload(params: any): any {
        if (!params || typeof params !== 'object') {
            return undefined;
        }

        const payload = { ...params };

        if ('id' in payload) {
            delete payload.id;
        } else if ('event_id' in payload) {
            delete payload.event_id;
        } else {
            const otherIdKey = Object.keys(payload).find((key) => key.endsWith('_id'));
            if (otherIdKey) {
                delete payload[otherIdKey];
            }
        }

        return Object.keys(payload).length ? payload : undefined;
    }

    castToModel(response: any, modelOrCtor: any) {
        if (!response || !modelOrCtor) return [];

        const Ctor = typeof modelOrCtor === 'function'
            ? modelOrCtor
            : modelOrCtor?.constructor;

        if (!Ctor) return [];

        const toInstance = (item: any) => (item ? new Ctor(item) : null);

        if (Array.isArray(response)) return response.map(toInstance);

        if (response && typeof response === 'object' && Array.isArray(response.data)) {
            return response.data.map(toInstance);
        }

        return toInstance(response);
    }

    static createFields(): object
    {
        return {
            id: null,
        }
    }

    static updateFields(data: any): object
    {
        return {
            id: data.id ?? null,
        }
    }

    getSearchFields(): object
    {
        return this._SearchFields
    }

    setSearchFields(fields: object)
    {
        this._SearchFields = fields;
    }

    public async getIndex(params: any, model?: DtoBaseClass)
    {
        if (!this._apiIndex){
            const error = new Error('API for index not found');
            ConsoleLogger.error(error);
            this.notifyError(error, 'API for index not found.');
            return null;
        }
        return await this.get(this._apiIndex, params, model);
    }

    public async getApi(url: string, params: any, model?: DtoBaseClass)
    {
        return await this.get(url, params, model);
    }

    set apiGet(value: string) {
        this._apiGet = value;
    }

    get apiGet(): string {
        return this._apiGet;
    }

    get apiIndex(): string {
        return this._apiIndex;
    }

    set apiIndex(value: string) {
        this._apiIndex = value;
    }

    get appendedWith() {
        return this._appendedWith;
    }

    set appendWith(columns: string[]) {
        this._appendedWith = columns;
    }

    get appendedCount() {
        return this._appendedCount;
    }

    set appendCount(columns: string[]) {
        this._appendedCount = columns;
    }

    async putIndex(params: any)
    {
        if (!this._apiPut){
            const error = new Error('API for update not found');
            ConsoleLogger.error(error);
            this.notifyError(error, 'API for update not found.');
            return null;
        }
        return await this.put(this._apiPut, this.getIdentifier(params), params);
    }

    async postIndex(params: any)
    {
        if (!this._apiPost){
            const error = new Error('API for create not found');
            ConsoleLogger.error(error);
            this.notifyError(error, 'API for create not found.');
            return null;
        }
        return await this.post(this._apiPost, params);
    }

    async deleteApiIndex(params: any)
    {
        if (!this._apiDelete){
            const error = new Error('API for delete not found');
            ConsoleLogger.error(error);
            this.notifyError(error, 'API for delete not found.');
            return null;
        }
        return await this.delete(
            this._apiDelete,
            this.getIdentifier(params),
            this.getDeletePayload(params)
        );
    }

    private getIdentifier(params: any): null {
        let identifier: any = null;

        // select keys containing id or _id string
        if ('id' in params) {
            identifier = params.id;
        } else if ('event_id' in params) {
            identifier = params.event_id;
        } else {
            //@ts-ignore
            const otherIdKey = Object.keys(params).find(key => key.endsWith('_id'));
            identifier = otherIdKey ? params[otherIdKey] : null;
        }

        if (!identifier) throw new Error('No valid ID found in parameters.');

        return identifier;
    }
}
