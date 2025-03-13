import axios from "axios";
import DtoBaseClass from "@/Modules/dto/DtoBaseClass";

export default abstract class ApiService {
    protected axiosInstance: any;
    protected static model: { new(data: any): DtoBaseClass };
    protected processing: boolean = false;
    protected constructor(model: DtoBaseClass) {
        this.axiosInstance = axios.create({});
    }

    async get(url: string, params?: any) {
        this.processing = true;
        try {
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
}
