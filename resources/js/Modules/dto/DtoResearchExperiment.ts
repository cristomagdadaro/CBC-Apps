import DtoBaseClass from "@/Modules/dto/DtoBaseClass";
import DtoResearchStudy from "@/Modules/dto/DtoResearchStudy";

export default class DtoResearchExperiment extends DtoBaseClass implements IResearchExperiment {
    study_id: number | string;
    code: string;
    title: string;
    geographic_location: string;
    season: string;
    commodity: string;
    sample_type: string;
    sample_descriptor: string;
    pr_code: string;
    cross_combination: string;
    parental_background: string;
    filial_generation: string;
    generation: string;
    plot_number: string;
    field_number: string;
    replication_number: number | string;
    planned_plant_count: number | string;
    background_notes: string;
    study: IResearchStudy | null;
    samples_count: number;

    constructor(data: IResearchExperiment) {
        super(data);

        this.study_id = data?.study_id;
        this.code = data?.code;
        this.title = data?.title;
        this.geographic_location = data?.geographic_location;
        this.season = data?.season;
        this.commodity = data?.commodity;
        this.sample_type = data?.sample_type;
        this.sample_descriptor = data?.sample_descriptor;
        this.pr_code = data?.pr_code;
        this.cross_combination = data?.cross_combination;
        this.parental_background = data?.parental_background;
        this.filial_generation = data?.filial_generation;
        this.generation = data?.generation;
        this.plot_number = data?.plot_number;
        this.field_number = data?.field_number;
        this.replication_number = data?.replication_number;
        this.planned_plant_count = data?.planned_plant_count;
        this.background_notes = data?.background_notes;
        this.study = data?.study ? new DtoResearchStudy(data.study) : null;
        this.samples_count = Number(data?.samples_count || 0);

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

    get studyTitle(): string {
        return this.study?.title || 'Study';
    }

    get projectTitle(): string {
        return this.study?.project?.title || 'Project';
    }

    get generationLabel(): string {
        return this.generation || this.filial_generation || 'N/A';
    }
}