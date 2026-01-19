export default class DtoError extends Error implements IError{
    status: number;
    message: string;
    data: any;
    title: string;

    constructor(error?: Partial<{
        data: {
            message: string;
            errors: string[];
        },
        status: number,
        title: string,
        message: string,
    }>) {
        super(error?.message ?? '');
        this.status = error?.status ?? 0;
        this.title = error?.title ?? 'Error';
        this.message = error?.message ?? '';
        this.data = error?.data ?? null;

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
