interface IBaseClass {
    id: string;
    table: string;

    created_at?: Date;
    updated_at?: Date;
    delete_at?: Date;
    showPage?: string;
    showPageTarget?: string;
    showPageParams?: string | number | Record<string, any> | null;
    updatePage?: string;
    updatePageTarget?: string;
    updatePageParams?: string | number | Record<string, any> | null;
}
