import DtoBaseClass from "@/Modules/dto/DtoBaseClass";

export default class DtoResearchProject extends DtoBaseClass implements IResearchProject {
    code: string;
    title: string;
    commodity: string;
    duration_start: string;
    duration_end: string;
    overall_budget: number | string;
    objective: string;
    funding_agency: string;
    funding_code: string;
    project_leader: IResearchMember | null;
    studies_count: number;
    experiments_count: number;

    constructor(data: IResearchProject) {
        super(data);

        this.code = data?.code;
        this.title = data?.title;
        this.commodity = data?.commodity;
        this.duration_start = data?.duration_start;
        this.duration_end = data?.duration_end;
        this.overall_budget = data?.overall_budget;
        this.objective = data?.objective;
        this.funding_agency = data?.funding_agency;
        this.funding_code = data?.funding_code;
        this.project_leader = data?.project_leader || null;
        this.studies_count = Number(data?.studies_count || 0);
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

    get projectLeaderName(): string {
        return this.project_leader?.name || 'Unassigned';
    }

    get budgetDisplay(): string {
        if (this.overall_budget === null || this.overall_budget === undefined || this.overall_budget === '') {
            return 'Pending';
        }

        const amount = Number(this.overall_budget);

        return Number.isNaN(amount) ? String(this.overall_budget) : amount.toLocaleString();
    }
}