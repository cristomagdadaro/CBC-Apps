import ApiMixin from "@/Modules/mixins/ApiMixin";

export default {
    mixins: [ApiMixin],
    data() {
        return {
            locationRegions: [],
            locationProvinces: [],
            locationCities: [],
            locationLoading: false,
        };
    },
    methods: {
        async loadRegions() {
            this.locationLoading = true;
            try {
                const response = await this.fetchGetApi('api.locations.regions');
                this.locationRegions = response?.data ?? [];
            } finally {
                this.locationLoading = false;
            }
        },
        async loadProvinces(region) {
            this.locationLoading = true;
            try {
                const response = await this.fetchGetApi('api.locations.provinces', region ? { region } : {});
                this.locationProvinces = response?.data ?? [];
            } finally {
                this.locationLoading = false;
            }
        },
        async loadCities(province, region = null) {
            this.locationLoading = true;
            try {
                const params = {};
                if (province) params.province = province;
                if (region) params.region = region;
                const response = await this.fetchGetApi('api.locations.cities', params);
                this.locationCities = response?.data ?? [];
            } finally {
                this.locationLoading = false;
            }
        },
    },
};
