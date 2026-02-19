import DtoForm from "@/Modules/dto/DtoForm";
import { createEmptyFormStyleTokens, mergeFormStyleTokens } from "@/Modules/shared/formStyleTokens";
import {usePage} from "@inertiajs/vue3";

export default class Form extends DtoForm {
    static endpoints = {
        indexGuest: 'api.form.guest.index',
        indexAuth: 'api.form.index',
        post: 'api.form.post',
        put: 'api.form.put',
        delete: 'api.form.delete',
    };

    constructor(response: DtoForm) {
        super(response);
        const page = usePage();
        this.api._apiIndex = (page.props.auth && page.props.auth.user) ? Form.endpoints.indexAuth : Form.endpoints.indexGuest;
        this.api._apiPost = Form.endpoints.post;
        this.api._apiPut = Form.endpoints.put;
        this.api._apiDelete = Form.endpoints.delete;
        this.api.appendWith = ['requirements'];
        this.api.appendCount = ['participants','responses'];
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
            requirements: [],
            style_tokens: createEmptyFormStyleTokens(),
        }
    }

    updateFields(data: DtoForm): object
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
            requirements: data?.requirements || [],
            style_tokens: mergeFormStyleTokens(data?.style_tokens),
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
