import DtoForm from "@/Modules/dto/DtoForm";

export default class Form extends DtoForm {
    constructor(response: DtoForm) {
        super(response);

        this.api._apiIndex = 'api.form.guest.index';
        this.api._apiPost = 'api.form.post';
        this.api._apiPut = 'api.form.put';
        this.api._apiDelete = 'api.form.delete';
        this.api.appendWith = ['requirements'];
        this.api.appendCount = ['participants']
    }

    deleteField(model): object
    {
        return {
            event_id: model?.event_id,
        };
    }

    createFields(): object
    {
        return {
            event_id: null,
            title: null,
            description: null,
            details: null,
            date_from: null,
            date_to: null,
            time_from: null,
            time_to: null,
            venue: null,
            max_slots: null,
            requirements: null,
        }
    }

    updateFields(data: IForm): object
    {
        return {
            event_id: data?.event_id,
            title: data?.title,
            description: data?.description,
            details: data?.details,
            date_from: data?.date_from,
            date_to: data?.date_to,
            time_from: data?.time_from,
            time_to: data?.time_to,
            venue: data?.venue,
            is_suspended: data?.is_suspended,
            max_slots: data?.max_slots,
            requirements: null,
        }
    }

    static getColumns()
    {
        return [
            {
                title: 'ID',
                key: 'id',
                db_key: 'id',
                align: 'center',
                sortable: true,
                visible: false,
            },
            {
                title: 'Event ID',
                key: 'event_id',
                db_key: 'event_id',
                align: 'center',
                sortable: true,
                visible: true,
            },
            {
                title: 'Title',
                key: 'title',
                db_key: 'title',
                align: 'center',
                sortable: true,
                visible: true,
            },
            {
                title: 'Description',
                key: 'description',
                db_key: 'description',
                align: 'center',
                sortable: true,
                visible: true,
            },
            {
                title: 'Details',
                key: 'details',
                db_key: 'details',
                align: 'center',
                sortable: true,
                visible: true,
            },
        ]
    }
}
