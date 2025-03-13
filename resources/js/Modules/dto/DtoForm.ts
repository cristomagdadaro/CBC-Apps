import DtoBaseClass from "@/Modules/dto/DtoBaseClass";

export default class DtoForm extends DtoBaseClass implements IForm{
    id: string;
    event_id: string;
    title: string;
    description: string;
    details: string;
    date_from: string;
    date_to: string;
    time_from: string;
    time_to: string;
    venue: string;
    has_pretest: boolean;
    has_posttest: boolean;
    has_preregistration: boolean;
    is_suspended: boolean;

    registrations: Array<IRegistration>
    participants: Array<IParticipant>

    constructor(data: IForm) {
        super(data);

        this.title = 'forms';
        this.event_id = data.event_id;
        this.title = data.title;
        this.description = data.description;
        this.details = data.details;
        this.date_from = data.date_from;
        this.date_to = data.date_to;
        this.time_from = data.time_from;
        this.time_to = data.time_to;
        this.venue = data.venue;
        this.has_pretest = data.has_pretest;
        this.has_posttest = data.has_posttest;
        this.has_preregistration = data.has_preregistration;
        this.is_suspended = data.is_suspended;
    }
}
