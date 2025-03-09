export default class DtoError extends Error implements IError{
    status: number;
    message: string;
    data: Array<String>;
    title: string;

    constructor(error?: Partial<{
        data: {
            message: string;
            errors: Array<string>;
        },
        status: number,
        title: string,
        message: string,
    }>) {
        super(error.data.message);
        this.status = error.status;
        this.title = error.title;
        this.message = error.data.message;
        this.data = error.data;
        console.error(this.toObject());
        return this;
    }

    toObject(): any {
        return {
            message: this.message,
            status: this.status,
            title: this.title,
            data: this.data,
        }
    }
}
