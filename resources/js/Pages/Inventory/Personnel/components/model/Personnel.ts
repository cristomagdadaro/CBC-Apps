import ApiService from "@/Modules/infrastructure/ApiService";
import DtoPersonnel from "@/Pages/Inventory/Personnel/components/model/DtoPersonnel";

export default class Personnel extends ApiService {
    static model = DtoPersonnel;

    constructor(response: DtoPersonnel) {
        super(response);

        this._apiIndex = 'api.inventory.personnels.index';
        this._apiPost = 'api.inventory.personnels.store';
        this._apiPut = 'api.inventory.personnels.update';
        this._apiDelete = 'api.inventory.personnels.destroy';
    }

    static getColumns() {
        return [
            {
                title: 'ID',
                key: 'id',
                align: 'center',
                sortable: true,
                visible: false,
            },
            {
                title: 'Name',
                key: 'fullname',
                align: 'center',
                sortable: true,
                visible: true,
            },
            {
                title: 'Middle Name',
                key: 'mname',
                align: 'center',
                sortable: true,
                visible: false,
            },
            {
                title: 'Last Name',
                key: 'lname',
                align: 'center',
                sortable: true,
                visible: false,
            },
            {
                title: 'Suffix',
                key: 'suffix',
                align: 'center',
                sortable: true,
                visible: false,
            },
            {
                title: 'Position',
                key: 'position',
                align: 'center',
                sortable: true,
                visible: true,
            },
            {
                title: 'Phone',
                key: 'phone',
                align: 'center',
                sortable: true,
                visible: true,
            },
            {
                title: 'Address',
                key: 'address',
                align: 'center',
                sortable: true,
                visible: false,
            },
            {
                title: 'Email',
                key: 'email',
                align: 'center',
                sortable: true,
                visible: false,
            },
        ]
    }
}
