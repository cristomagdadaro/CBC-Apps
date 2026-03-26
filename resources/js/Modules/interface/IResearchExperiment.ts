interface IResearchExperiment extends IBaseClass {
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
    study?: IResearchStudy | null;
    samples_count?: number;
}