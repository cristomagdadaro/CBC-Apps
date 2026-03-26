interface IResearchStudy extends IBaseClass {
    project_id: number | string;
    code: string;
    title: string;
    objective: string;
    budget: number | string;
    study_leader: IResearchMember | null;
    supervisor: IResearchMember | null;
    staff_members: IResearchMember[];
    project?: IResearchProject | null;
    experiments_count?: number;
}