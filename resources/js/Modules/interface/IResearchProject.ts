interface IResearchProject extends IBaseClass {
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
    studies_count?: number;
    experiments_count?: number;
}