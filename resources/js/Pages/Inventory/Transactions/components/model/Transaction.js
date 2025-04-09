import { BaseClass } from '@/Modules/core/domain/BaseClass.js';
import {Item} from "@/Pages/Items/components/model/Item.js";
export class Transaction extends BaseClass{
  constructor(params = {}) {
    super(params);
    const item = new Item(params.item);
    this.name = item.name;
    this.brand = item.brand;
    this.transac_type = params.transac_type ? params.transac_type : null;
  }


  static getColumns() {
    return [
        {
            title: 'ID',
            key: 'id',
            align: 'center',
            sortable: true,
            visible: false,
        },{
            title: 'Item',
            key: 'name',
            align: 'center',
            sortable: true,
            visible: true,
        },{
            title: 'Brand',
            key: 'brand',
            align: 'center',
            sortable: true,
            visible: true,
        },{
            title: 'Unit',
            key: 'unit',
            align: 'center',
            sortable: true,
            visible: true,
        },{
            title: 'Barcode',
            key: 'barcode',
            align: 'center',
            sortable: true,
            visible: false,
        },{
            title: 'Type',
            key: 'transac_type',
            align: 'center',
            sortable: true,
            visible: true,
        },{
            title: 'Quantity',
            key: 'quantity',
            align: 'center',
            sortable: true,
            visible: true,
        },{
            title: 'Unit Price',
            key: 'unit_price',
            align: 'center',
            sortable: true,
            visible: false,
        },{
            title: 'Total Cost',
            key: 'total_cost',
            align: 'center',
            sortable: true,
            visible: false,
        },{
            title: 'Personnel',
            key: 'personnel_id',
            align: 'center',
            sortable: true,
            visible: false,
        },{
            title: 'Charging',
            key: 'project_code',
            align: 'center',
            sortable: true,
            visible: true,
        },{
            title: 'Supplier',
            key: 'supplier_id',
            align: 'center',
            sortable: true,
            visible: false,
        },{
            title: 'User',
            key: 'user_id',
            align: 'center',
            sortable: true,
            visible: false,
        },{
            title: 'Expiration',
            key: 'expiration',
            align: 'center',
            sortable: true,
            visible: false,
        },{
            title: 'Remarks',
            key: 'remarks',
            align: 'center',
            sortable: true,
            visible: false,
        },{
            title: 'Date',
            key: 'created_at',
            align: 'center',
            sortable: true,
            visible: true,
        },{
            title: 'Updated At',
            key: 'updated_at',
            align: 'center',
            sortable: true,
            visible: false,
        },{
            title: 'Deleted At',
            key: 'deleted_at',
            align: 'center',
            sortable: true,
            visible: false,
        },
    ];
    }
}
