import DtoRegistration from "@/Modules/dto/DtoRegistration";

export default class Registration extends DtoRegistration {
	static endpoints = {
		index: null,
		post: null,
		put: null,
		delete: null,
	};

	constructor(response: DtoRegistration) {
		super(response);
	}

	createFields(): object
	{
		return {
			event_id: null,
			participant_id: null,
			pretest_finished: false,
			posttest_finished: false,
		}
	}

	updateFields(data: IRegistration): object
	{
		return {
			id: data?.id,
			event_id: data?.event_id,
			participant_id: data?.participant_id,
			pretest_finished: data?.pretest_finished ?? false,
			posttest_finished: data?.posttest_finished ?? false,
		}
	}

	static getColumns()
	{
		return [
			{
				title: 'ID',
				key: 'id',
				db_key: 'id',
				align: 'center',
				sortable: true,
				visible: false,
			},
			{
				title: 'Event ID',
				key: 'event_id',
				db_key: 'event_id',
				align: 'center',
				sortable: true,
				visible: true,
			},
			{
				title: 'Participant ID',
				key: 'participant_id',
				db_key: 'participant_id',
				align: 'center',
				sortable: true,
				visible: true,
			},
			{
				title: 'Pretest Finished',
				key: 'pretest_finished',
				db_key: 'pretest_finished',
				align: 'center',
				sortable: true,
				visible: false,
			},
			{
				title: 'Posttest Finished',
				key: 'posttest_finished',
				db_key: 'posttest_finished',
				align: 'center',
				sortable: true,
				visible: false,
			},
		]
	}
}
