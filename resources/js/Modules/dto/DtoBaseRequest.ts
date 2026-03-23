import IBaseRequest from "@/Modules/interface/IBaseRequest";
import { usePage } from "@inertiajs/vue3";
export default class DtoBaseRequest implements IBaseRequest {
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

    static props = usePage();

    constructor(params : IBaseRequest = {
        page: 1,
        per_page: '25',
        sort: 'created_at',
        order: 'desc',
        search: null,
        filter: null,
        is_exact: null,
        filter_by_parent_id: null,
        filter_by_parent_column: null,
        filter_by_scopeby_user: false,
        filter_by_scopeby_institute: false,
        filter_by_scopeby_public: false,
    }) {
        this.page = params.page;
        this.per_page = params.per_page;
        this.sort = params.sort;
        this.order = params.order;

        // optional parameters
        this.search = params.search;
        this.filter = params.filter;
        this.is_exact = params.is_exact;

        this.filter_by_parent_id = params.filter_by_parent_id;
        this.filter_by_parent_column = params.filter_by_parent_column;
        this.scope_by = params.scope_by;
    }

    get getPerPage() {
        return this.per_page;
    }

    get getPage() {
        return this.page;
    }

    get getIsExact() {
        return this.is_exact;
    }

    get getSort() {
        return this.sort;
    }

    get getOrder() {
        return this.order;
    }

    get getSearch() {
        return this.search;
    }

    get getFilter() {
        return this.filter;
    }

    get getScope() {
        return this.scope_by;
    }

    updateParam(key : string, value : any) {
        if(value === null || value === undefined || value === '' || value === false)
            this.removeParam(key);
        else
            this[key] = value;
        //this.saveParamsLocal();
    }

    removeParam(key: string) {
        delete this[key];
    }

    getParam(key: string) {
        return this[key];
    }

    toObject() {
        return this;
    }

    saveParamsLocal() {
        // use the current route name as the key
        localStorage.setItem(DtoBaseRequest.props.component, JSON.stringify(this));
    }

    static getParamsLocal() {
        if (localStorage.getItem(DtoBaseRequest.props.component) !== null)
            return JSON.parse(localStorage.getItem(DtoBaseRequest.props.component));
        else
            return new this();
    }

    static resetParamsLocal() {
        localStorage.removeItem(DtoBaseRequest.props.component);
    }
}
