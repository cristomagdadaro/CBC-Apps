import DtoItem from "@/Modules/dto/DtoItem";

export default class Item extends DtoItem {
    static endpoints = {
        index: 'api.inventory.items.index',
        post: 'api.inventory.items.store',
        put: 'api.inventory.items.update',
        delete: 'api.inventory.items.destroy',
        show: 'items.show',
    };
    constructor(response: DtoItem) {
        super(response);

        this.api._apiIndex = Item.endpoints.index;
        this.api._apiPost = Item.endpoints.post;
        this.api._apiPut = Item.endpoints.put;
        this.api._apiDelete = Item.endpoints.delete;

        this.api.appendWith = ['category', 'supplier'];
        console.log(this)
        this.showPage = Item.endpoints.show;
    }

    createFields(): object {
        return {
            name: null,
            brand: null,
            description: null,
            specifications: null,
            category_id: null,
            supplier_id: null,
            image: null,
        }
    }

    updateFields(data: IItem): object {
        return {
            id: data?.id,
            name: data?.name,
            brand: data?.brand,
            description: data?.description,
            specifications: data?.specifications,
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
                visible: true,
            },
            {
                title: 'Specifications',
                key: 'specifications',
                db_key: 'specifications',
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
