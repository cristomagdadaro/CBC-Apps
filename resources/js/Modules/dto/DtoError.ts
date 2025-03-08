import {AxiosError} from "axios";

export default class DtoError extends Error implements IError{
    status: number;
    statusText: string;
    message: string;
    errors: Array<string>;

    constructor(error?: Partial<{
        data: {
            message: string;
            errors: Array<string>;
        },
        status: number,
        statusText: string,
    }>) {
        super(error.data.message);
        this.status = error.status;
        this.statusText = error.statusText;
        this.message = error.data.message;
        this.errors = error.data.errors;

        return this;
    }

    toObject(): any {
        return {
            message: this.message,
            status: this.status,
            statusText: this.statusText,
            errors: this.errors,
        }
    }
}
