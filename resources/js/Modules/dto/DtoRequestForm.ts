import DtoBaseClass from "@/Modules/dto/DtoBaseClass";
import DtoRequester from "@/Modules/dto/DtoRequester";

export default class DtoRequestForm extends DtoBaseClass implements IRequestForm {
    request_type: Array<string> | string;
    request_details: string;
    request_purpose: string;
    project_title: string;
    date_of_use: string;
    time_of_use: string;
    labs_to_use: object;
    equipments_to_use: object;
    consumables_to_use: object;

    requester: IRequester;

    constructor(data: any) {
        super(data);

        this.request_type = data?.request_type ?? [];
        this.request_details = data?.request_details;
        this.request_purpose = data?.request_purpose;
        this.project_title = data?.project_title;
        this.date_of_use = data?.date_of_use;
        this.time_of_use = data?.time_of_use;
        this.labs_to_use = data?.labs_to_use;
        this.equipments_to_use = data?.equipments_to_use;
        this.consumables_to_use = data?.consumables_to_use;

        if (data?.requester) {
            this.requester = new DtoRequester(data.requester);
        }
    }
}
