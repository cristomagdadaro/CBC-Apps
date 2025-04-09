import {BaseClass} from "@/Modules/core/domain/BaseClass.js";

export default class SummaryItem extends BaseClass{
    constructor(params = {}) {
        super(params);
    }

    static toObject(obj) {
        return Object.assign({}, obj);
    }

    static getColumns() {
        return [
            {
                title: 'Item',
                key: 'name',
                align: 'center',
                sortable: true,
                visible: true,
            },
            {
                title: 'Brand',
                key: 'brand',
                align: 'center',
                sortable: true,
                visible: true,
            },
            {
                title: 'Unit',
                key: 'unit',
                align: 'center',
                sortable: true,
                visible: true,
            },
            {
                title: 'Total Stocks',
                key: 'total_ingoing',
                align: 'center',
                sortable: true,
                visible: false,
            },
            {
                title: 'Total Consumed',
                key: 'total_outgoing',
                align: 'center',
                sortable: true,
                visible: false,
            },
            {
                title: 'Remaining Stocks',
                key: 'remaining_quantity',
                align: 'center',
                sortable: true,
                visible: false,
            },
        ];
    }
}
