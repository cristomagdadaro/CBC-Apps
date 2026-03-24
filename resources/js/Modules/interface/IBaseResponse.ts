import IDatatableResponse from "./IDatatableResponse";
import IDataView from "@/Modules/core/interface/base/IDataView";
export default interface IBaseResponse {
    data: IDatatableResponse | any;
    meta?: any;
    links?: any;
    dataView?: IDataView;
}
