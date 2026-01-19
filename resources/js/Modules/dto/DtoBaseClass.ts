import ConcreteApiService from "@/Modules/infrastructure/ConcreteApiService";

export default class DtoBaseClass implements IBaseClass {
    id: string;
    table: string;

    created_at?: Date;
    updated_at?: Date;
    delete_at?: Date;

    public api: ConcreteApiService;

    indexPage: string;
    showPage: string;
    updatePage: string;
    createPage: string;

    constructor(data: Partial<IBaseClass>) {
        this.id = data?.id;
        this.table = data?.table;
        this.created_at = data?.created_at;
        this.updated_at = data?.updated_at;
        this.delete_at = data?.delete_at;

        this.api = new ConcreteApiService();
    }

    identifier(model: DtoBaseClass): object
    {
        if (model)
            return {
                id: model?.id
            };

        return {
            id: this?.id,
        }
    }

    deleteField(model: any): object
    {
        return {
            id: model?.id
        };
    }

    get fullName() {
        // check if the instance has a fname, mname, etc. attribute
        if (this.hasOwnProperty('fname') && this.hasOwnProperty('mname') && this.hasOwnProperty('lname') && this.hasOwnProperty('suffix')){
            //@ts-ignore
            return [this.fname, this.mname ? this.mname?.[0]+'.' : '', this.lname, this.suffix]
                .filter(part => part)
                .join(" ");
        }

        //@ts-ignore
        return this.name || this.table || this.title;
    }

    static getNestedValue(obj, path) {
        if (!obj || !path) return undefined;
        return path.split('.').reduce((acc, part) => acc && acc[part], obj);
    }

    static getFilterColumns() {
        return this.getColumns()
            .filter(column => column.visible !== false)
            .map(column => ({
                name: column.db_key,
                label: column.title,
            }));
    }

    static getColumns() {
        return []
    }

    static visibleColumns() {
        return this.getColumns().filter(column => column.visible);
    }

}
