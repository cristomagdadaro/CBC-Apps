import DtoBaseClass from "@/Modules/dto/DtoBaseClass";
import DtoForm from "@/Modules/dto/DtoForm";
import DtoParticipant from "@/Modules/dto/DtoParticipant";

export default class DtoRegistration extends DtoBaseClass implements IRegistration {
    id: string;
    event_id: string;
    participant_id: string;
    pretest_finished: boolean;
    posttest_finished: boolean;

    form: IForm;
    participant: IParticipant;

    table: string;
    constructor(data: any) {
        super(data);

        this.id = data.id;
        this.event_id = data.event_id;
        this.participant_id = data.participant_id;
        this.pretest_finished = data.pretest_finished;
        this.posttest_finished = data.posttest_finished;

        this.table = data.table;

        if (data.form){
            this.form = new DtoForm(data.form);
        }

        if (data.participant){
            this.participant = new DtoParticipant(data.participant);
        }
    }
}
