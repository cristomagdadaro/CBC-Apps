interface IForm extends IBaseClass {
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

    registrations: Array<IRegistration>
    participants: Array<IParticipant>
}
