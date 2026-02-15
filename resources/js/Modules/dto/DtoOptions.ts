import DtoBaseClass from "@/Modules/dto/DtoBaseClass";
import IOptions from "@/Modules/interface/IOptions";

export default class DtoOptions extends DtoBaseClass implements IOptions {
    key: string;
    label: string;
    description: string;
    type: string;
    group: string;
    value: any;
    options: any;

    constructor(data: any) {
        super(data);

        this.key = data?.key ?? '';
        this.label = data?.label ?? '';
        this.description = data?.description ?? '';
        this.type = data?.type ?? '';
        this.group = data?.group ?? '';
        this.value = data?.value ?? null;
        this.options = data?.options ?? null;

        this.api.setSearchFields({
            search: null,
            filter: null,
            filter_by: null,
            is_exact: false,
            page: 1,
            per_page: 10,
            sort: 'created_at',
            order: 'desc'
        });
    }
}
