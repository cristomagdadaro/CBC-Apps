import DtoItem from "@/Modules/dto/DtoItem";

export default class Item extends DtoItem {
    constructor(response: DtoItem) {
        super(response);

        this.api._apiIndex = 'api.inventory.items.index';
        this.api._apiPost = 'api.inventory.items.store';
        this.api._apiPut = 'api.inventory.items.update';
        this.api._apiDelete = 'api.inventory.items.destroy';

        this.api.appendWith = ['category', 'supplier'];

        this.showPage = 'items.show';
    }

    createFields(): object {
        return {
            name: null,
            brand: null,
            description: null,
            category_id: null,
            supplier_id: null,
            image: null,
        }
    }

    updateFields(data: IItem): object {
        return {
            name: data?.name,
            brand: data?.brand,
            description: data?.description,
            category_id: data?.category_id,
            supplier_id: data?.supplier_id,
            image: data?.image,
        }
    }

    static getColumns() {
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
                title: 'Name',
                key: 'name',
                db_key: 'name',
                align: 'center',
                sortable: true,
                visible: true,
            },
            {
                title: 'Brand',
                key: 'brand',
                db_key: 'brand',
                align: 'center',
                sortable: true,
                visible: true,
            },
            {
                title: 'Supplier',
                key: 'supplier.fullName',
                db_key: 'supplier_id',
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
                visible: false,
            },
            {
                title: 'Category',
                key: 'category.fullName',
                db_key: 'category_id',
                align: 'center',
                sortable: true,
                visible: true,
            },
            {
                title: 'Image',
                key: 'image',
                db_key: 'image',
                align: 'center',
                sortable: true,
                visible: false,
            },
            {
                title: 'Created At',
                key: 'created_at',
                db_key: 'created_at',
                align: 'center',
                sortable: true,
                visible: false,
            },
            {
                title: 'Updated At',
                key: 'updated_at',
                db_key: 'updated_at',
                align: 'center',
                sortable: true,
                visible: false,
            },
            {
                title: 'Deleted At',
                key: 'deleted_at',
                db_key: 'deleted_at',
                align: 'center',
                sortable: true,
                visible: false,
            },
        ]
    }
}
