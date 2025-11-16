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
    is_suspended: boolean;
    max_slots: number;
    requirements: Array<string>;

    participants_count: number;

    registrations: Array<IRegistration>
    participants: Array<IParticipant>
}
