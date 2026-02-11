export default {
    data() {
        return {
            savedLaboratoryPersonnel: JSON.parse(localStorage.getItem("laboratory_personnel")) || null,
        };
    },
    methods: {
        saveLaboratoryPersonnel(personnelData) {
            if (!personnelData?.employee_id) {
                return;
            }

            const personnel = {
                employee_id: personnelData.employee_id,
                fullName: personnelData.fullName || "",
                fname: personnelData.fname || "",
                mname: personnelData.mname || "",
                lname: personnelData.lname || "",
                suffix: personnelData.suffix || "",
            };

            // Update localStorage & ensure Vue reactivity
            localStorage.setItem("laboratory_personnel", JSON.stringify(personnel));
            this.savedLaboratoryPersonnel = { ...personnel }; // Force reactivity
        },
        clearLaboratoryPersonnel() {
            localStorage.removeItem("laboratory_personnel");
            this.savedLaboratoryPersonnel = null;
        },
        loadLaboratoryPersonnel() {
            const stored = localStorage.getItem("laboratory_personnel");
            this.savedLaboratoryPersonnel = stored ? JSON.parse(stored) : null;
        },
    },
    mounted() {
        // Listen for storage changes (if user opens multiple tabs)
        window.addEventListener("storage", () => {
            this.savedLaboratoryPersonnel = JSON.parse(localStorage.getItem("laboratory_personnel")) || null;
        });
    }
};
