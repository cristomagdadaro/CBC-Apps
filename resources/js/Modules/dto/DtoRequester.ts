import DtoBaseClass from "@/Modules/dto/DtoBaseClass";

export default class DtoRequester extends DtoBaseClass implements IRequester {
    id: string;
    name: string;
    affiliation: string;
    philrice_id: string;
    position: string;
    email: string;
    phone: string;

    constructor(props: IRequester) {
        super(props);

        this.name = props?.name;
        this.affiliation = props?.affiliation;
        this.philrice_id = props?.philrice_id ?? '';
        this.position = props?.position;
        this.email = props?.email;
        this.phone = props?.phone;
    }

}
