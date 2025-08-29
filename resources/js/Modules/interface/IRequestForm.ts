interface IRequestForm extends IBaseClass {
    request_type: string;
    request_details: string;
    request_purpose: string;
    project_title: string;
    date_of_use: string;
    time_of_use: string;
    labs_to_use: object;
    equipments_to_use: object;
    consumables_to_use: object;

    requester: IRequester;
}
