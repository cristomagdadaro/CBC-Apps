import IBaseResponse from "../../interface/base/IBaseResponse";
import IDataView from "@/Modules/core/interface/base/IDataView";
export default class DtoBaseResponse implements IBaseResponse {
    data: any;
    links?: any;
    meta?: any;
    dataView?: IDataView;

    constructor(params: IBaseResponse) {
        if (!params) return;
        this.data = params.data.data ?? null;
        if (params.data?.links)
            this.links = params.data.links;
        if (params.data?.meta)
            this.meta = params.data.meta;
        if (params.data?.dataView)
            this.dataView = params.data.dataView;
    }

    getMeta() {
        return this.meta
    }

    getData() {
        return this.data
    }

    getLinks() {
        return this.links
    }

    getDataView(){
        return this.dataView
    }

    getCurrentPage() {
        if(this.meta)
            return this.meta.current_page
        return null
    }

    getLastPage() {
        if(this.links)
            return this.links.last
        return null
    }

    getFirstPage() {
        if(this.links)
            return this.links.first
        return null
    }

    getNextPage() {
        if(this.links)
            return this.links.next
        return null
    }

    getPrevPage() {
        if(this.links)
            return this.links.prev
        return null
    }

    getTotalPage() {
        if(this.meta)
            return this.meta.total
        return null
    }

    getPerPage() {
        if(this.meta)
            return this.meta.per_page
        return null
    }

    getFromPage() {
        if(this.meta)
            return this.meta.from
        return null
    }

    getToPage() {
        if(this.meta)
            return this.meta.to
        return null
    }

    isAllFetched() {
        return this.getCurrentPage() && this.getTotalPage() && this.getCurrentPage() === this.getTotalPage()
    }
}
