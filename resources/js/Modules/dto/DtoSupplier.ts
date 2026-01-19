import DtoBaseClass from "@/Modules/dto/DtoBaseClass";

export default class DtoSupplier extends DtoBaseClass implements ISupplier {
    name: string;
    email: string;
    phone: string;
    address: string;
    description: string;

    constructor(data: ISupplier) {
        super(data);

        this.name = data?.name;
        this.email = data?.email;
        this.phone =  data?.phone;
        this.address = data?.address;
        this.description = data?.description;
    }
}
