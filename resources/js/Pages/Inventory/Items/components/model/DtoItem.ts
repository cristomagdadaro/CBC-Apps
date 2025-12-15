import DtoBaseClass from "@/Modules/dto/DtoBaseClass";
import DtoSupplier from "@/Pages/Inventory/Supplier/components/model/DtoSupplier";
import DtoCategory from "@/Pages/Inventory/Items/components/model/DtoCatergory";

export default class DtoItem extends DtoBaseClass implements IItem {
    name: string;
    brand: string;
    description: string;
    category_id: string;
    supplier_id: string;
    image: string;

    supplier: ISupplier;
    category: ICategory;

    constructor(data: IItem) {
        super(data);

        this.name = data?.name;
        this.brand = data?.brand;
        this.description = data?.description;
        this.category_id = data?.category_id;
        this.supplier_id = data?.supplier_id;
        this.image = data?.image;

        if (data?.supplier)
            this.supplier = new DtoSupplier(data.supplier);

        if (data?.category)
            this.category = new DtoCategory(data.category);
    }

    get fullName(): string {
        return `${this.name} ` + (this.description ? `(${this.description})` : '');
    }
}
