import DtoBaseClass from "@/Modules/dto/DtoBaseClass";
import { FormAppearanceTokens, mergeFormStyleTokens } from "@/Modules/shared/formStyleTokens";
import SubformRequirement from "../domain/SubformRequirement";
import IForm from "../interface/IForm";
import DtoSubformRequirement from "./DtoSubformRequirement";
import DtoRegistration from "./DtoRegistration";
import DtoParticipant from "./DtoParticipant";
export default class DtoForm extends DtoBaseClass implements IForm{
    event_id: string;
    title: string;
    description: string;
    details: string;
    date_from: string;
    date_to: string;
    time_from: string;
    time_to: string;
    venue: string;
    is_suspended: boolean;
    max_slots: number;
    requirements: Array<SubformRequirement>;
    style_tokens: FormAppearanceTokens;

    participants_count: number;

    registrations: Array<IRegistration>;
    participants: Array<IParticipant>;

    constructor(data: DtoForm) {
        super(data);

        this.event_id = data?.event_id ?? '';
        this.title = data?.title ?? '';
        this.description = data?.description ?? '';
        this.details = data?.details ?? '';
        this.date_from = data?.date_from ?? '';
        this.date_to = data?.date_to ?? '';
        this.time_from = data?.time_from ?? '';
        this.time_to = data?.time_to ?? '';
        this.venue = data?.venue ?? '';
        this.is_suspended = data?.is_suspended ?? false;
        this.max_slots = data?.max_slots ?? 0;

        this.style_tokens = mergeFormStyleTokens(data?.style_tokens);
        this.participants_count = data?.participants_count ?? 0;

        if (Array.isArray(data?.requirements)) {
            this.requirements = data.requirements.map( item => {
                return new DtoSubformRequirement(item);
            })
        } 
        if (Array.isArray(data?.registrations)) {
            this.registrations = data.registrations.map( item => {
                return new DtoRegistration(item);
            })
        } 
        if (Array.isArray(data?.participants)) {
            this.participants = data.participants.map( item => {
                return new DtoParticipant(item);
            })
        } 
    }
}
