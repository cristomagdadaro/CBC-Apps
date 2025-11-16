import DtoParticipant from "@/Modules/dto/DtoParticipant";

export default class Participant extends DtoParticipant {
    constructor(response: DtoParticipant) {
        super(response);
        this.api._apiIndex = 'api.form.participants.index';
        this.api._apiDelete = 'api.form.participants.delete';
        this.api._apiPost = 'api.form.registration.post';

        this.api.appendWith = ['registrations'];
    }

    createFields(): object
    {
        return {
            name: null,
            email: null,
            phone: null,
            sex: null,
            age: null,
            organization: null,
            designation: null,
            is_ip: false,
            is_pwd: false,
            city_address: null,
            province_address: null,
            country_address: null,
            agreed_tc: false,
            event_id: null,
            attendance_type: null
        }
    }

    updateFields(data: IParticipant): object
    {
        return {
            name: data?.name,
            email: data?.email,
            phone: data?.phone,
            sex: data?.sex,
            age: data?.age,
            organization: data?.organization,
            designation: data?.designation,
            is_ip: data?.is_ip,
            is_pwd: data?.is_pwd,
            city_address: data?.city_address,
            province_address: data?.province_address,
            country_address: data?.country_address,
            agreed_tc: data?.agreed_tc,
            event_id: data?.event_id,
            attendance_type: data?.attendance_type
        }
    }

    static getColumns(){
        return [
            {
                title: 'ID',
                key: 'id',
                db_key: 'id',
                align: 'center',
                sortable: true,
                visible: false,
            }, {
                title: 'Name',
                key: 'name',
                db_key: 'name',
                align: 'center',
                sortable: true,
                visible: true,
            },{
                title: 'Email',
                key: 'email',
                db_key: 'email',
                align: 'center',
                sortable: true,
                visible: true,
            },{
                title: 'Phone',
                key: 'phone',
                db_key: 'phone',
                align: 'center',
                sortable: true,
                visible: true,
            },{
                title: 'Sex',
                key: 'sex',
                db_key: 'sex',
                align: 'center',
                sortable: true,
                visible: true,
            },{
                title: 'Age',
                key: 'age',
                db_key: 'age',
                align: 'text-center',
                sortable: true,
                visible: true,
            },{
                title: 'Organization',
                key: 'organization',
                db_key: 'organization',
                align: 'center',
                sortable: true,
                visible: true,
            },{
                title: 'Designation',
                key: 'designation',
                db_key: 'designation',
                align: 'center',
                sortable: true,
                visible: true,
            },{
                title: 'Is IP',
                key: 'is_ip',
                db_key: 'is_ip',
                align: 'text-center',
                sortable: true,
                visible: true,
            },{
                title: 'Is PWD',
                key: 'is_pwd',
                db_key: 'is_pwd',
                align: 'text-center',
                sortable: true,
                visible: true,
            },{
                title: 'City',
                key: 'city_address',
                db_key: 'city_address',
                align: 'center',
                sortable: true,
                visible: true,
            },{
                title: 'Province',
                key: 'province_address',
                db_key: 'province_address',
                align: 'center',
                sortable: true,
                visible: true,
            },{
                title: 'Country',
                key: 'country_address',
                db_key: 'country_address',
                align: 'center',
                sortable: true,
                visible: true,
            },{
                title: 'Agreed T&C',
                key: 'agreed_tc',
                db_key: 'agreed_tc',
                align: 'text-center',
                sortable: true,
                visible: true,
            },
        ]
    }
}
