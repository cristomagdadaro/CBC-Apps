import DtoOptions from "@/Modules/dto/DtoOptions";
import IOptions from "@/Modules/interface/IOptions";

export default class Options extends DtoOptions {
    constructor(response: DtoOptions) {
        super(response);

        this.api._apiIndex = 'api.options.index';
        this.api._apiPost = 'api.options.store';
        this.api._apiPut = 'api.options.update';
        this.api._apiDelete = 'api.options.destroy';

        this.showPage = 'system.options.show';
    }

    createFields(): object {
        return {
            key: null,
            label: null,
            description: null,
            type: null,
            group: null,
            value: null,
            options: null,
        }
    }

    updateFields(data: IOptions): object {
        return {
            id: data?.id,
            key: data?.key,
            label: data?.label,
            description: data?.description,
            type: data?.type,
            group: data?.group,
            value: data?.value,
            options: data?.options,
        }
    }

    static getColumns() {
        return [
            { title: 'ID', key: 'id', db_key: 'id', align: 'center', sortable: true, visible: false },
            { title: 'Key', key: 'key', db_key: 'key', align: 'left', sortable: true, visible: true },
            { title: 'Label', key: 'label', db_key: 'label', align: 'left', sortable: true, visible: true },
            { title: 'Group', key: 'group', db_key: 'group', align: 'center', sortable: true, visible: true },
            { title: 'Type', key: 'type', db_key: 'type', align: 'center', sortable: true, visible: true },
            { title: 'Value', key: 'value', db_key: 'value', align: 'left', sortable: false, visible: true },
            { title: 'Created At', key: 'created_at', db_key: 'created_at', align: 'center', sortable: true, visible: false },
        ]
    }
}