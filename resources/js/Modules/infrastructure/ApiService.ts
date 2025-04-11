import axios from "axios";
import DtoBaseClass from "@/Modules/dto/DtoBaseClass";

export default abstract class ApiService {
    protected axiosInstance: any;
    protected static model: { new(data: any): DtoBaseClass };
    protected processing: boolean = false;

    protected _apiIndex: string;
    protected _apiPost: string;
    protected _apiPut: string;
    protected _apiDelete: string;

    protected constructor(model: DtoBaseClass) {
        this.axiosInstance = axios.create({});
    }

    async get(url: string, params?: any) {
        this.processing = true;
        try {
            // @ts-ignore
            const response = await this.axiosInstance.get(route(url), { params });
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

    castToModel(response: any) {
        if (!response || !ApiService.model) return [];
        return response.map((item: any) => (item ? new ApiService.model(item) : null));
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

    protected async getIndex(params: any)
    {
        return await this.get(this._apiIndex, params);
    }

    get apiIndex(): string {
        return this._apiIndex;
    }

    set apiIndex(value: string) {
        this._apiIndex = value;
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
