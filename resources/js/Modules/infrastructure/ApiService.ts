import axios, {AxiosResponse} from "axios";
import DtoBaseClass from "@/Modules/dto/DtoBaseClass";

export default abstract class ApiService {
    public axiosInstance: any;
    public processing: boolean = false;

    public _apiIndex: string;
    public _apiPost: string;
    public _apiPut: string;
    public _apiDelete: string;

    public _appendedWith?: string[];
    public _appendedCount?: string[];

    protected constructor() {
        this.axiosInstance = axios.create({});
    }

    async get(url: string, params?: any, model?: DtoBaseClass) {
        this.processing = true;
        try {
            // @ts-ignore
            const response = await this.axiosInstance.get(route(url), {
                params: {
                    ...params,
                    ...(model.api.appendWith && Array.isArray(model.api.appendWith) ? {with: model.api.appendWith.toString()} : {}),
                    ...(model.api.appendedCount && Array.isArray(model.api.appendedCount) ? {count: model.api.appendedCount.toString()} : {})
                }
            }).then((response: AxiosResponse) => {
                if (model) {
                    response.data.data = this.castToModel(response.data.data, model);
                }
                return response;
            });
            this.processing = false;
            return response.data;
        } catch (error) {
            this.processing = false;
            throw error;
        }
    }

    async post(url: string, params?: any) {
        this.processing = true;
        try {
            // @ts-ignore
            const response = await this.axiosInstance.post(route(url), params);
            this.processing = false;
            return response;
        } catch (error) {
            this.processing = false;
            throw error;
        }
    }

    async put(url: string, id: any, params?: any) {

        this.processing = true;
        try {
            // @ts-ignore
            const response = await axios.put(`${route(url)}/${id}`, params);
            this.processing = false;
            return response;
        } catch (error) {
            this.processing = false;
            throw error;
        }
    }

    async delete(url: string, id: any, params?: any) {

        this.processing = true;
        try {
            // @ts-ignore
            const response = await axios.delete(`${route(url)}/${id}`, params);

            this.processing = false;
            return response;
        } catch (error) {
            this.processing = false;
            throw error;
        }
    }

    castToModel(response: any, model: DtoBaseClass) {
        if (!response || !model) return [];
        // @ts-ignore
        return response.map((item: any) => (item ? new model.constructor(item) : null));
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
        return {
            search: null,
            filter: null,
            is_exact: false,
            page: 1,
            per_page: 10,
            sort: 'created_at',
            order: 'desc',
        }
    }

    public async getIndex(params: any, model?: DtoBaseClass)
    {
        return await this.get(this._apiIndex, params, model);
    }

    get apiIndex(): string {
        return this._apiIndex;
    }

    set apiIndex(value: string) {
        this._apiIndex = value;
    }

    get appendedWith() {
        return this._appendedCount;
    }

    get appendedCount() {
        return this._appendedCount;
    }

    set appendedCount(columns: string[]) {
        this._appendedCount = columns;
    }

    set appendWith(columns: string[]) {
        this._appendedCount = columns;
    }

    async putIndex(params: any)
    {
        return await this.put(this._apiPut, params.event_id, params);
    }

    async postIndex(params: any)
    {
        return await this.post(this._apiPost, params);
    }

    async deleteApiIndex(params: any)
    {
        return await this.delete(this._apiDelete, params.event_id, params);
    }
}
