import DtoResearchProject from "@/Modules/dto/DtoResearchProject";

export default class ResearchProject extends DtoResearchProject {
    static endpoints = {
        post: 'api.research.projects.store',
        put: 'api.research.projects.update',
        delete: 'api.research.projects.destroy',
        show: 'research.projects.show',
        create: 'research.projects.create',
    };

    constructor(response: DtoResearchProject) {
        super(response);

        this.api._apiPost = ResearchProject.endpoints.post;
        this.api._apiPut = ResearchProject.endpoints.put;
        this.api._apiDelete = ResearchProject.endpoints.delete;
        this.showPage = ResearchProject.endpoints.show;
        this.createPage = ResearchProject.endpoints.create;
    }

    createFields(): object {
        return {
            title: '',
            commodity: 'Rice',
            duration_start: '',
            duration_end: '',
            overall_budget: '',
            objective: '',
            funding_agency: '',
            funding_code: '',
            project_leader_id: '',
        };
    }

    updateFields(data: IResearchProject): object {
        return {
            id: data?.id ?? null,
            title: data?.title ?? '',
            commodity: data?.commodity ?? 'Rice',
            duration_start: data?.duration_start ?? '',
            duration_end: data?.duration_end ?? '',
            overall_budget: data?.overall_budget ?? '',
            objective: data?.objective ?? '',
            funding_agency: data?.funding_agency ?? '',
            funding_code: data?.funding_code ?? '',
            project_leader_id: data?.project_leader?.id ?? '',
        };
    }
}