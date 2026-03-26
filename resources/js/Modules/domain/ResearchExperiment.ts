import DtoResearchExperiment from "@/Modules/dto/DtoResearchExperiment";

export default class ResearchExperiment extends DtoResearchExperiment {
    static endpoints = {
        index: 'api.research.experiments.index',
        post: 'api.research.experiments.store',
        put: 'api.research.experiments.update',
        delete: 'api.research.experiments.destroy',
        show: 'research.experiments.show',
    };

    constructor(response: DtoResearchExperiment) {
        super(response);

        this.api._apiIndex = ResearchExperiment.endpoints.index;
        this.api._apiPost = ResearchExperiment.endpoints.post;
        this.api._apiPut = ResearchExperiment.endpoints.put;
        this.api._apiDelete = ResearchExperiment.endpoints.delete;
        this.api.appendWith = ['study', 'study.project'];
        this.api.appendCount = ['samples'];
        this.showPage = ResearchExperiment.endpoints.show;
    }

    createFields(): object {
        return {
            study_id: null,
            title: '',
            geographic_location: '',
            season: 'wet',
            commodity: 'Rice',
            sample_type: 'Seeds',
            sample_descriptor: '',
            pr_code: '',
            cross_combination: '',
            parental_background: '',
            filial_generation: '',
            generation: '',
            plot_number: '',
            field_number: '',
            replication_number: '',
            planned_plant_count: '',
            background_notes: '',
        };
    }

    updateFields(data: IResearchExperiment): object {
        return {
            id: data?.id ?? null,
            study_id: data?.study_id ?? data?.study?.id ?? null,
            title: data?.title ?? '',
            geographic_location: data?.geographic_location ?? '',
            season: data?.season ?? 'wet',
            commodity: data?.commodity ?? 'Rice',
            sample_type: data?.sample_type ?? 'Seeds',
            sample_descriptor: data?.sample_descriptor ?? '',
            pr_code: data?.pr_code ?? '',
            cross_combination: data?.cross_combination ?? '',
            parental_background: data?.parental_background ?? '',
            filial_generation: data?.filial_generation ?? '',
            generation: data?.generation ?? '',
            plot_number: data?.plot_number ?? '',
            field_number: data?.field_number ?? '',
            replication_number: data?.replication_number ?? '',
            planned_plant_count: data?.planned_plant_count ?? '',
            background_notes: data?.background_notes ?? '',
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
                title: 'Study',
                key: 'studyTitle',
                db_key: 'study_id',
                align: 'text-left',
                sortable: false,
                visible: true,
            },
            {
                title: 'Location',
                key: 'geographic_location',
                db_key: 'geographic_location',
                align: 'text-left',
                sortable: true,
                visible: true,
            },
            {
                title: 'Season',
                key: 'season',
                db_key: 'season',
                align: 'text-left',
                sortable: true,
                visible: true,
            },
            {
                title: 'Commodity',
                key: 'commodity',
                db_key: 'commodity',
                align: 'text-left',
                sortable: true,
                visible: true,
            },
            {
                title: 'Sample Type',
                key: 'sample_type',
                db_key: 'sample_type',
                align: 'text-left',
                sortable: true,
                visible: true,
            },
            {
                title: 'Samples',
                key: 'samples_count',
                db_key: 'samples_count',
                align: 'text-left',
                sortable: false,
                visible: true,
            },
        ];
    }
}