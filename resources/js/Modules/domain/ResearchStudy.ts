import DtoResearchStudy from "@/Modules/dto/DtoResearchStudy";

export default class ResearchStudy extends DtoResearchStudy {
    static endpoints = {
        index: 'api.research.studies.index',
        post: 'api.research.studies.store',
        put: 'api.research.studies.update',
        delete: 'api.research.studies.destroy',
        show: 'research.studies.show',
    };

    constructor(response: DtoResearchStudy) {
        super(response);

        this.api._apiIndex = ResearchStudy.endpoints.index;
        this.api._apiPost = ResearchStudy.endpoints.post;
        this.api._apiPut = ResearchStudy.endpoints.put;
        this.api._apiDelete = ResearchStudy.endpoints.delete;
        this.api.appendWith = ['project'];
        this.api.appendCount = ['experiments'];
        this.showPage = ResearchStudy.endpoints.show;
    }

    createFields(): object {
        return {
            project_id: null,
            title: '',
            objective: '',
            budget: '',
            study_leader_id: '',
            supervisor_id: '',
            staff_member_ids: [],
        };
    }

    updateFields(data: IResearchStudy): object {
        return {
            id: data?.id ?? null,
            project_id: data?.project_id ?? data?.project?.id ?? null,
            title: data?.title ?? '',
            objective: data?.objective ?? '',
            budget: data?.budget ?? '',
            study_leader_id: data?.study_leader?.id ?? '',
            supervisor_id: data?.supervisor?.id ?? '',
            staff_member_ids: Array.isArray(data?.staff_members)
                ? data.staff_members.map((member) => member?.id).filter(Boolean)
                : [],
        };
    }

    static getColumns() {
        return [
            {
                title: 'ID',
                key: 'id',
                db_key: 'id',
                align: 'text-left',
                sortable: true,
                visible: false,
            },
            {
                title: 'Code',
                key: 'code',
                db_key: 'code',
                align: 'text-left',
                sortable: true,
                visible: true,
            },
            {
                title: 'Title',
                key: 'title',
                db_key: 'title',
                align: 'text-left',
                sortable: true,
                visible: true,
            },
            {
                title: 'Project',
                key: 'projectTitle',
                db_key: 'project_id',
                align: 'text-left',
                sortable: false,
                visible: true,
            },
            {
                title: 'Study Leader',
                key: 'studyLeaderName',
                db_key: 'study_leader',
                align: 'text-left',
                sortable: false,
                visible: true,
            },
            {
                title: 'Supervisor',
                key: 'supervisorName',
                db_key: 'supervisor',
                align: 'text-left',
                sortable: false,
                visible: true,
            },
            {
                title: 'Budget',
                key: 'budgetDisplay',
                db_key: 'budget',
                align: 'text-left',
                sortable: true,
                visible: true,
            },
            {
                title: 'Experiments',
                key: 'experiments_count',
                db_key: 'experiments_count',
                align: 'text-left',
                sortable: false,
                visible: true,
            },
        ];
    }
}