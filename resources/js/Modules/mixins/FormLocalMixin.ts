export default {
    data() {
        return {
            localHashedIds: JSON.parse(localStorage.getItem("participant_hashes")) || [],
            lastCreatedForm: null,
        };
    },
    computed: {
        storedLocalHashedIds() {
            return this.localHashedIds; // Ensure reactivity
        },
        recentQrCodes(){
            if (this.lastCreatedForm)
            {
                this.localHashedIds = [...this.localHashedIds, ...[this.lastCreatedForm]];
                this.lastCreatedForm = null;
            }
            return this.storedLocalHashedIds;
        }
    },
    methods: {
        saveLocalHashedIds(response) {
            this.localHashedIds.push(response);

            // Ensure the array does not exceed 6 items
            if (this.localHashedIds.length > 6) {
                this.localHashedIds.shift(); // Remove the oldest entry
            }

            // Update localStorage & ensure Vue reactivity
            localStorage.setItem("participant_hashes", JSON.stringify(this.localHashedIds));
            this.localHashedIds = [...this.localHashedIds]; // Force reactivity
        },
        clearLocalHashedIds(hashedId = null) {
            if (!hashedId) {
                // Clear all
                this.localHashedIds = [];
                localStorage.removeItem("participant_hashes");
                this.lastCreatedForm = null;
            } else if(hashedId == this.lastCreatedForm.participant_hash) {
                this.lastCreatedForm = null;
            }
            else {
                // Remove a specific hash
                this.localHashedIds = this.localHashedIds.filter(item => item.participant_hash !== hashedId);
                localStorage.setItem("participant_hashes", JSON.stringify(this.localHashedIds));
            }
        }
    },
    mounted() {
        // Listen for storage changes (if user opens multiple tabs)
        window.addEventListener("storage", () => {
            this.localHashedIds = JSON.parse(localStorage.getItem("participant_hashes")) || [];
        });
    }
};
