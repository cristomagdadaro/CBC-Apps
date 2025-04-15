import { AxiosResponse } from "axios";
import IResponse from "@/Modules/interface/IResponse";

export default class DtoResponse implements IResponse {
    message: string;
    data: Object;
    status: number;

    constructor(response: AxiosResponse) {
        this.message = response.statusText;
        this.data = response.data;
        this.status = response.status;
    }

    castDataToModel<M>(model: new (data: any) => M): M | M[] {
        if (!this.data || !model) return [];

        if (Array.isArray(this.data)) {
            return this.data.map((item) => (item ? new model(item) : null));
        }

        return new model(this.data);
    }

    get getPaginatedResponse() {
        // @ts-ignore
        return this.data;
    }
}
