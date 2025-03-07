interface IRegistration {
    id: string;
    event_id: string;
    participant_id: string;
    pretest_finished: boolean;
    posttest_finished: boolean;

    form: Array<IForm>;
    participant: Array<IParticipant>;
}
