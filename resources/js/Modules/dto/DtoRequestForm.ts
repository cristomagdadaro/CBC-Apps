import DtoBaseClass from "@/Modules/dto/DtoBaseClass";
import DtoRequester from "@/Modules/dto/DtoRequester";

export default class DtoRequestForm extends DtoBaseClass implements IRequestForm {
    request_type: string[];
    request_details: string;
    request_purpose: string;
    project_title: string;
    date_of_use: string;
    date_of_use_end: string;
    time_of_use: string;
    time_of_use_end: string;
    labs_to_use: string[];
    laboratories_labels?: string[];
    equipments_to_use: string[];
    equipments_labels?: string[];
    consumables_to_use: string[];
    consumables_labels?: string[];

    requester: IRequester;

    constructor(data: any) {
        super(data);

        this.request_type = Array.isArray(data?.request_type) ? data.request_type : (data?.request_type ? [data.request_type] : []);
        this.request_details = data?.request_details ?? '';
        this.request_purpose = data?.request_purpose ?? '';
        this.project_title = data?.project_title ?? '';
        this.date_of_use = data?.date_of_use ?? '';
        this.date_of_use_end = data?.date_of_use_end ?? '';
        this.time_of_use = data?.time_of_use ?? '';
        this.time_of_use_end = data?.time_of_use_end ?? '';
        this.labs_to_use = Array.isArray(data?.labs_to_use) ? data.labs_to_use : [];
        this.laboratories_labels = Array.isArray(data?.laboratories_labels) ? data.laboratories_labels : [];
        this.equipments_to_use = Array.isArray(data?.equipments_to_use) ? data.equipments_to_use : [];
        this.equipments_labels = Array.isArray(data?.equipments_labels) ? data.equipments_labels : [];
        this.consumables_to_use = Array.isArray(data?.consumables_to_use) ? data.consumables_to_use : [];
        this.consumables_labels = Array.isArray(data?.consumables_labels) ? data.consumables_labels : [];


        const emptyRequester = {
            id: '',
            name: '',
            affiliation: '',
            email: '',
            position: '',
            phone: '',
        } as IRequester;

        this.requester = data?.requester
            ? new DtoRequester(data.requester)
            : new DtoRequester(emptyRequester);
    }
}
