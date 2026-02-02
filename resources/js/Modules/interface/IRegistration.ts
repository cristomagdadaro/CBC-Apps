interface IRegistration {
    id: string;
    event_id: string;
    participant_id: string;
    pretest_finished: boolean;
    posttest_finished: boolean;
    checked_in_at?: string | null;
    checked_in_by?: number | null;
    checkin_source?: string | null;

    form: IForm;
    participant: IParticipant;
}
