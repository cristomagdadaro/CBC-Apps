import ApiService from "@/Modules/infrastructure/ApiService";
import DtoTransaction from "@/Pages/Inventory/Scan/components/model/DtoTransaction";

export default class Transaction extends ApiService {
    static model = DtoTransaction;
    private _apiIndex: string;
    private _apiPost: string;
    private _apiPut: string;
    private _apiDelete: string;

    constructor(response: DtoTransaction) {
        super(response);

        this._apiIndex = 'api.inventory.transactions.index';
        this._apiPost = 'api.inventory.transactions.store';
        this._apiPut = 'api.inventory.transactions.update';
        this._apiDelete = 'api.inventory.transactions.destroy';
    }

    async getIndex(params: any)
    {
        return await this.get(this._apiIndex, params);
    }

    get apiIndex(): string {
        return this._apiIndex;
    }

    set apiIndex(value: string) {
        this._apiIndex = value;
    }

    async putIndex(params: any)
    {
        return await this.put(this._apiPut, params.id, params);
    }

    async postIndex(params: any)
    {
        return await this.post(this._apiPost, params);
    }

    async deleteApiIndex(params: any)
    {
        return await this.delete(this._apiDelete, params.id, params);
    }

    deleteField(model): object
    {
        return {
            id: model.id ?? null,
        };
    }

    createFields(): object
    {
        return {
            brand: null,
            unit: null,
            remaining_quantity: null,
            total_outgoing: null,
            total_cost: null,
        };
    }

    updateFields(model: ITransaction): object
    {
        return {
            id: model.id ?? null,
            brand: model.brand ?? null,
            unit: model.unit ?? null,
            remaining_quantity: model.remaining_quantity ?? null,
            total_outgoing: model.total_outgoing ?? null,
            total_cost: model.total_cost ?? null,
        };
    }

    static getFilterColumns() {
        return Transaction.getColumns()
            .filter(column => column.visible !== false)
            .map(column => ({
                name: column.db_key,
                label: column.title,
            }));
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
            },{
                title: 'Barcode',
                key: 'barcode',
                db_key: 'barcode',
                align: 'center',
                sortable: true,
                visible: true,
            },{
                title: 'Type',
                key: 'transac_type',
                db_key: 'transac_type',
                align: 'center',
                sortable: true,
                visible: true,
            },{
                title: 'Quantity',
                key: 'quantity',
                db_key: 'quantity',
                align: 'center',
                sortable: true,
                visible: true,
            },{
                title: 'Unit',
                key: 'unit',
                db_key: 'unit',
                align: 'center',
                sortable: true,
                visible: true,
            },{
                title: 'Unit Price',
                key: 'unit_price',
                db_key: 'unit_price',
                align: 'center',
                sortable: true,
                visible: true,
            },{
                title: 'Total Cost',
                key: 'total_cost',
                db_key: 'total_cost',
                align: 'center',
                sortable: true,
                visible: true,
            },{
                title: 'Personnel',
                key: 'personnel_id',
                db_key: 'personnel_id',
                align: 'center',
                sortable: true,
                visible: true,
            },{
                title: 'Project Code',
                key: 'project_code',
                db_key: 'project_code',
                align: 'center',
                sortable: true,
                visible: true,
            },{
                title: 'Expiration',
                key: 'expiration',
                db_key: 'expiration',
                align: 'center',
                sortable: true,
                visible: true,
            },{
                title: 'Remarks',
                key: 'remarks',
                db_key: 'remarks',
                align: 'center',
                sortable: true,
                visible: true,
            },{
                title: 'Item',
                key: 'item',
                db_key: 'item',
                align: 'center',
                sortable: true,
                visible: true,
            },{
                title: 'User',
                key: 'user',
                db_key: 'user',
                align: 'center',
                sortable: true,
                visible: true,
            },

        ]
    }
}
