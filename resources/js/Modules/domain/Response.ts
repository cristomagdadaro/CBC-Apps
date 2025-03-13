import DtoResponse from "@/Modules/dto/DtoResponse";
import {AxiosResponse} from "axios";

export default class Response extends DtoResponse {
    message: string;
    data: any;

    constructor(response: AxiosResponse) {
        super(response);
    }
}
