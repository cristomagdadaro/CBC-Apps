import { BaseClass } from '@/Modules/core/domain/BaseClass.js';
export class Personnel extends BaseClass{
    constructor(params = {}) {
        super(params);
        this.fullname = Personnel.fullName(params);
    }

    static toObject(obj) {
        return Object.assign({}, obj);
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
