import ConcreteApiService from "@/Modules/infrastructure/ConcreteApiService";

export default class DtoBaseClass implements IBaseClass {
    id: string;
    table: string;

    created_at?: Date;
    updated_at?: Date;
    delete_at?: Date;

    public api: ConcreteApiService;

    constructor(data: Partial<IBaseClass>) {
        this.id = data?.id;
        this.table = data?.table;
        this.created_at = data?.created_at;
        this.updated_at = data?.updated_at;
        this.delete_at = data?.delete_at;

        this.api = new ConcreteApiService();
    }

    get fullName() {
        // @ts-ignore
        return `${this?.fname} ${this?.mname} ${this?.lname} ${this?.suffix}`;
    }
}
