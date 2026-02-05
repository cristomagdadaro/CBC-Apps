interface IRequestForm extends IBaseClass {
    request_type: Array<string> | string;
    request_details: string;
    request_purpose: string;
    project_title: string;
    date_of_use: string;
    date_of_use_end: string;
    time_of_use: string;
    time_of_use_end: string;
    labs_to_use: Array<string> | object;
    equipments_to_use: Array<string> | object;
    consumables_to_use: Array<string> | object;

    requester: IRequester;
}
