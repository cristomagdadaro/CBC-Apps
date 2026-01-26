
type FormStyleMode = 'color' | 'image' | null;

interface IFormStyleToken {
    mode: FormStyleMode;
    value: string | null;
}

interface IFormStyleTokens {
    'form-background': IFormStyleToken;
    'form-header-box': IFormStyleToken;
    'form-time-from': IFormStyleToken;
    'form-time-to': IFormStyleToken;
    'form-time-between': IFormStyleToken;
}

export default interface IForm extends IBaseClass {
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
    requirements: Array<ISubformRequirement>;
    style_tokens: IFormStyleTokens;

    participants_count: number;

    registrations: Array<IRegistration>;
    participants: Array<IParticipant>;
}
