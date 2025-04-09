import { BaseClass } from '@/Modules/core/domain/BaseClass.js';
import {Supplier} from "@/Pages/Supplier/components/model/Supplier.js";
import Category from "@/Pages/Items/components/model/Category.js";

export class Item extends BaseClass{
    constructor(params = {
        category: {},
        supplier: {}
    }) {
        super(params);
        this.category_id = params && params.category ? new Category(params.category).name : null;
        this.supplier_id = params && params.supplier ? new Supplier(params.supplier).name : null;
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
                title: 'Supplier',
                key: 'supplier_id',
                align: 'center',
                sortable: true,
                visible: true,
            },
            {
                title: 'Description',
                key: 'description',
                align: 'center',
                sortable: true,
                visible: false,
            },
            {
                title: 'Category',
                key: 'category_id',
                align: 'center',
                sortable: true,
                visible: true,
            },
            {
                title: 'Image',
                key: 'image',
                align: 'center',
                sortable: true,
                visible: false,
            },
            {
                title: 'Created At',
                key: 'created_at',
                align: 'center',
                sortable: true,
                visible: true,
            },
            {
                title: 'Updated At',
                key: 'updated_at',
                align: 'center',
                sortable: true,
                visible: false,
            },
            {
                title: 'Deleted At',
                key: 'deleted_at',
                align: 'center',
                sortable: true,
                visible: false,
            },
        ]
    }
}
