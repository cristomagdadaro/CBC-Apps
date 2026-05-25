import ApiMixin from "@/Modules/mixins/ApiMixin";

export default {
    mixins: [ApiMixin],
    data() {
        return {
            locationRegions: [],
            locationProvinces: [],
            locationCities: [],
            locationLoading: false,
            locationRequestTokens: {
                regions: 0,
                provinces: 0,
                cities: 0,
            },
            locationActiveRequests: {
                regions: null,
                provinces: null,
                cities: null,
            },
        };
    },
    methods: {
        startLocationRequest(type) {
            const requestId = ++this.locationRequestTokens[type];
            this.locationActiveRequests[type] = requestId;
            this.locationLoading = Object.values(this.locationActiveRequests).some(Boolean);

            return requestId;
        },
        finishLocationRequest(type, requestId) {
            if (this.locationActiveRequests[type] === requestId) {
                this.locationActiveRequests[type] = null;
            }

            this.locationLoading = Object.values(this.locationActiveRequests).some(Boolean);
        },
        invalidateLocationRequest(type) {
            this.locationRequestTokens[type] += 1;
            this.locationActiveRequests[type] = null;
            this.locationLoading = Object.values(this.locationActiveRequests).some(Boolean);
        },
        resetLocationRegions() {
            this.invalidateLocationRequest('regions');
            this.locationRegions = [];
        },
        resetLocationProvinces() {
            this.invalidateLocationRequest('provinces');
            this.locationProvinces = [];
        },
        resetLocationCities() {
            this.invalidateLocationRequest('cities');
            this.locationCities = [];
        },
        async loadRegions() {
            const requestId = this.startLocationRequest('regions');
            try {
                const response = await this.fetchGetApi('api.locations.regions');
                if (this.locationRequestTokens.regions !== requestId) {
                    return [];
                }

                this.locationRegions = response?.data ?? [];
                return this.locationRegions;
            } finally {
                this.finishLocationRequest('regions', requestId);
            }
        },
        async loadProvinces(region) {
            const requestId = this.startLocationRequest('provinces');
            try {
                const response = await this.fetchGetApi('api.locations.provinces', region ? { region } : {});
                if (this.locationRequestTokens.provinces !== requestId) {
                    return [];
                }

                this.locationProvinces = response?.data ?? [];
                return this.locationProvinces;
            } finally {
                this.finishLocationRequest('provinces', requestId);
            }
        },
        async loadCities(province, region = null) {
            const requestId = this.startLocationRequest('cities');
            try {
                const params = {};
                if (province) params.province = province;
                if (region) params.region = region;
                const response = await this.fetchGetApi('api.locations.cities', params);
                if (this.locationRequestTokens.cities !== requestId) {
                    return [];
                }

                this.locationCities = response?.data ?? [];
                return this.locationCities;
            } finally {
                this.finishLocationRequest('cities', requestId);
            }
        },
    },
};
