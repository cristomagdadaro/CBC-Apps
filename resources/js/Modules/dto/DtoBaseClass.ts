export default class DtoBaseClass implements IBaseClass {
    id: string;
    table: string;

    created_at?: Date;
    updated_at?: Date;
    delete_at?: Date;

    _appendedWith?: string[];
    _appendedCount?: string[];

    constructor(data: Partial<IBaseClass>) {
        this.id = data.id;
        this.table = data.table;
        this.created_at = data.created_at;
        this.updated_at = data.updated_at;
        this.delete_at = data.delete_at;
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
}
