import DtoBaseClass from "@/Modules/dto/DtoBaseClass";
import DtoResearchProject from "@/Modules/dto/DtoResearchProject";

export default class DtoResearchStudy extends DtoBaseClass implements IResearchStudy {
    project_id: number | string;
    code: string;
    title: string;
    objective: string;
    budget: number | string;
    study_leader: IResearchMember | null;
    supervisor: IResearchMember | null;
    staff_members: IResearchMember[];
    project: IResearchProject | null;
    experiments_count: number;

    constructor(data: IResearchStudy) {
        super(data);

        this.project_id = data?.project_id;
        this.code = data?.code;
        this.title = data?.title;
        this.objective = data?.objective;
        this.budget = data?.budget;
        this.study_leader = data?.study_leader || null;
        this.supervisor = data?.supervisor || null;
        this.staff_members = Array.isArray(data?.staff_members) ? data.staff_members : [];
        this.project = data?.project ? new DtoResearchProject(data.project) : null;
        this.experiments_count = Number(data?.experiments_count || 0);

        this.api.setSearchFields({
            search: null,
            filter: null,
            filter_by: null,
            is_exact: false,
            page: 1,
            per_page: 10,
            sort: 'updated_at',
            order: 'desc',
        });
    }

    get projectTitle(): string {
        return this.project?.title || 'Project';
    }

    get studyLeaderName(): string {
        return this.study_leader?.name || 'Unassigned';
    }

    get supervisorName(): string {
        return this.supervisor?.name || 'Unassigned';
    }

    get staffSummary(): string {
        if (!this.staff_members.length) {
            return 'No staff assigned';
        }

        return this.staff_members.map((member) => member?.name).filter(Boolean).join(', ');
    }

    get budgetDisplay(): string {
        if (this.budget === null || this.budget === undefined || this.budget === '') {
            return 'Pending';
        }

        const amount = Number(this.budget);

        return Number.isNaN(amount) ? String(this.budget) : amount.toLocaleString();
    }
}