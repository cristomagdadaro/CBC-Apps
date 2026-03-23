export default interface IBaseRequest {
    page: number;
    per_page: string;
    sort: string;
    order: string;

    search?: string;
    filter?: string;
    is_exact?: boolean;

    filter_by_parent_id?: number;
    filter_by_parent_column?: string;
    scope_by?: string;

    appendWith?: string[];
    appendCount?: string[];
}
