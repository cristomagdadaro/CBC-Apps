import DtoBaseClass from "@/Modules/dto/DtoBaseClass";
import DtoSupplier from "@/Modules/dto/DtoSupplier";
import DtoCategory from "@/Modules/interface/DtoCatergory";

export default class DtoItem extends DtoBaseClass implements IItem {
    name: string;
    brand: string;
    description: string;
    specifications: string;
    category_id: string;
    supplier_id: string;
    equipment_logger_mode: string;
    image: string;

    supplier: ISupplier;
    category: ICategory;

    constructor(data: IItem) {
        super(data);

        this.name = data?.name;
        this.brand = data?.brand;
        this.description = data?.description;
        this.specifications = data?.specifications;
        this.category_id = data?.category_id;
        this.supplier_id = data?.supplier_id;
        this.equipment_logger_mode = data?.equipment_logger_mode;
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
