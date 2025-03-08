interface IBaseClass {
    id: string;
    table: string;

    created_at?: Date;
    updated_at?: Date;
    delete_at?: Date;

    _appendedWith?: string[];
    _appendedCount?: string[];
}
