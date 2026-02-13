import axios, {AxiosError, AxiosResponse} from "axios";
import DtoBaseClass from "@/Modules/dto/DtoBaseClass";
import ConsoleLogger from "@/Modules/shared/infrastructure/ConsoleLogger";

export default abstract class ApiService {
    public axiosInstance: any;
    public processing: boolean = false;

    public _apiIndex: string;
    public _apiPost: string;
    public _apiPut: string;
    public _apiDelete: string;
    public _apiGet: string;

    public _appendedWith?: string[];
    public _appendedCount?: string[];

    private _SearchFields: object = {};

    protected constructor() {
        this.axiosInstance = axios.create({});

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
        try {
            const routeParams = params?.routeParams;
            const cleanedParams = params ? { ...params } : undefined;
            if (cleanedParams && Object.prototype.hasOwnProperty.call(cleanedParams, 'routeParams')) {
                delete cleanedParams.routeParams;
            }
            
            const id = model ? model.apiGet : null;
            // @ts-ignore
            const routeUrl = id
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
                ConsoleLogger.debug('API GET Response:', response);
                if (model) {
                    response.data.data = this.castToModel(response.data.data, model);
                }
                return response;
            });
            this.processing = false;
            ConsoleLogger.debug('GET Response Data:', response.data);
            return response.data;
        } catch (error) {
            this.processing = false;
            this.notifyError(error, 'Failed to load data.');
            throw error;
        }
    }

    async post(url: string, params?: any, config?: any) {
        this.processing = true;
        try {
            ConsoleLogger.debug('POST Parameters:', params);
            const routeParams = config?.routeParams;
            const cleanConfig = config ? { ...config } : undefined;
            if (cleanConfig && Object.prototype.hasOwnProperty.call(cleanConfig, 'routeParams')) {
                delete cleanConfig.routeParams;
            }
            // @ts-ignore
            const response = await this.axiosInstance.post(
                // @ts-ignore
                routeParams !== undefined ? route(url, routeParams) : route(url),
                params,
                cleanConfig
            );
            this.processing = false;
            this.notifySuccess('Data submitted successfully.');
            ConsoleLogger.debug('POST Response Data:', response.data);
            return response;
        } catch (error) {
            this.processing = false;
            ConsoleLogger.error('POST Error:', error);
            this.notifyError(error, 'Failed to submit data.');
            throw error;
        }
    }

    async put(url: string, id: any, params?: any) {

        this.processing = true;
        try {
            ConsoleLogger.debug('PUT Parameters:', params);
            // @ts-ignore
            const response = await axios.put(`${route(url)}/${id}`, params);
            this.processing = false;
            ConsoleLogger.debug('PUT Response Data:', response.data);
            this.notifySuccess('Data updated successfully.');
            return response;
        } catch (error) {
            this.processing = false;
            ConsoleLogger.error('PUT Error:', error);
            this.notifyError(error, 'Failed to update data.');
            throw error;
        }
    }

    async delete(url: string, id: any) {
        this.processing = true;
        try {
            ConsoleLogger.debug('DELETE Parameters:', id);
            // @ts-ignore
            const response = await axios.delete(route(url, id), id);
            this.processing = false;
            ConsoleLogger.debug('DELETE Response Data:', response.data);
            this.notifyWarning('Data deleted successfully.');
            return response;
        } catch (error) {
            this.processing = false;
            ConsoleLogger.error('DELETE Error:', error);
            this.notifyError(error, 'Failed to delete data.');
            throw error;
        }
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
            console.error('API for index not found');
            this.notifyError(new Error('API for index not found'), 'API for index not found.');
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
            console.error('API for update not found');
            this.notifyError(new Error('API for update not found'), 'API for update not found.');
            return null;
        }
        return await this.put(this._apiPut, this.getIdentifier(params), params);
    }

    async postIndex(params: any)
    {
        if (!this._apiPost){
            console.error('API for create not found');
            this.notifyError(new Error('API for create not found'), 'API for create not found.');
            return null;
        }
        return await this.post(this._apiPost, params);
    }

    async deleteApiIndex(params: any)
    {
        if (!this._apiDelete){
            console.error('API for delete not found');
            this.notifyError(new Error('API for delete not found'), 'API for delete not found.');
            return null;
        }
        return await this.delete(this._apiDelete, this.getIdentifier(params));
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
