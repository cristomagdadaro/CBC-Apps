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
            if (!region) {
                this.locationProvinces = [];
                return;
            }
            this.locationLoading = true;
            try {
                const response = await this.fetchGetApi('api.locations.provinces', { region });
                this.locationProvinces = response?.data ?? [];
            } finally {
                this.locationLoading = false;
            }
        },
        async loadCities(province, region = null) {
            if (!province && !region) {
                this.locationCities = [];
                return;
            }
            this.locationLoading = true;
            try {
                const response = await this.fetchGetApi('api.locations.cities', { province, region });
                this.locationCities = response?.data ?? [];
            } finally {
                this.locationLoading = false;
            }
        },
    },
};
