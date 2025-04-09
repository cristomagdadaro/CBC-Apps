export default class BaseRequest{
    constructor(params = {}) {
        this.params = {
            page: params.page ?? 1,
            per_page: params.per_page ?? '10',
            sort: params.sort ?? 'created_at',
            order: params.order ?? 'desc',
        };

        // optional parameters
        this.updateParam('search', params.search ?? null);
        this.updateParam('filter', params.filter ?? null);
        this.updateParam('is_exact', params.is_exact ?? null);
    }

    updateParam(key, value) {
        if(value === null || value === undefined || value === '' || value === false)
            this.removeParam(key);
        else
            this.params[key] = value;

        this.saveParamsLocal();
    }

    getParam(key) {
        return this.params[key];
    }

    removeParam(key) {
        delete this.params[key];
    }

    toObject() {
        /*if (localStorage.getItem('params') !== null)
            return JSON.parse(localStorage.getItem('params'));
        else*/
        return this.params;
    }

    saveParamsLocal() {
        localStorage.setItem('params', JSON.stringify(this.params));
    }

    static getParamsLocal() {
        if (localStorage.getItem('params') !== null)
            return JSON.parse(localStorage.getItem('params'));
        else
            return new BaseRequest().params;
    }
}
