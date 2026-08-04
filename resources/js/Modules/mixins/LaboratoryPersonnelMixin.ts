export default {
    data() {
        return {
            savedLaboratoryPersonnel: null,
            laboratoryPersonnelCryptoKey: null,
        };
    },
    methods: {
        async getLaboratoryPersonnelCryptoKey() {
            if (this.laboratoryPersonnelCryptoKey) {
                return this.laboratoryPersonnelCryptoKey;
            }

            this.laboratoryPersonnelCryptoKey = await window.crypto.subtle.generateKey(
                { name: "AES-GCM", length: 256 },
                false,
                ["encrypt", "decrypt"]
            );
            return this.laboratoryPersonnelCryptoKey;
        },
        async encryptLaboratoryPersonnel(plainText) {
            const key = await this.getLaboratoryPersonnelCryptoKey();
            const iv = window.crypto.getRandomValues(new Uint8Array(12));
            const encoded = new TextEncoder().encode(plainText);
            const cipherBuffer = await window.crypto.subtle.encrypt(
                { name: "AES-GCM", iv },
                key,
                encoded
            );

            const cipherBytes = new Uint8Array(cipherBuffer);
            const payloadBytes = new Uint8Array(iv.length + cipherBytes.length);
            payloadBytes.set(iv, 0);
            payloadBytes.set(cipherBytes, iv.length);

            let binary = "";
            payloadBytes.forEach((b) => {
                binary += String.fromCharCode(b);
            });
            return btoa(binary);
        },
        async decryptLaboratoryPersonnel(payload) {
            const key = await this.getLaboratoryPersonnelCryptoKey();
            const binary = atob(payload);
            const payloadBytes = Uint8Array.from(binary, (c) => c.charCodeAt(0));
            const iv = payloadBytes.slice(0, 12);
            const cipherBytes = payloadBytes.slice(12);

            const plainBuffer = await window.crypto.subtle.decrypt(
                { name: "AES-GCM", iv },
                key,
                cipherBytes
            );
            return new TextDecoder().decode(plainBuffer);
        },
        async saveLaboratoryPersonnel(personnelData) {
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
                position: personnelData.position || "",
                phone: personnelData.phone || "",
                address: personnelData.address || "",
                email: personnelData.email || "",
                has_email: personnelData.has_email === true,
                profile_requires_update:
                    personnelData.profile_requires_update === true,
            };

            // Update localStorage & ensure Vue reactivity
            const encryptedPersonnel = await this.encryptLaboratoryPersonnel(JSON.stringify(personnel));
            localStorage.setItem("laboratory_personnel", encryptedPersonnel);
            this.savedLaboratoryPersonnel = { ...personnel }; // Force reactivity
        },
        clearLaboratoryPersonnel() {
            localStorage.removeItem("laboratory_personnel");
            this.savedLaboratoryPersonnel = null;
        },
        async loadLaboratoryPersonnel() {
            const stored = localStorage.getItem("laboratory_personnel");
            if (!stored) {
                this.savedLaboratoryPersonnel = null;
                return;
            }

            try {
                const decrypted = await this.decryptLaboratoryPersonnel(stored);
                this.savedLaboratoryPersonnel = JSON.parse(decrypted);
            } catch (e) {
                localStorage.removeItem("laboratory_personnel");
                this.savedLaboratoryPersonnel = null;
            }
        },
    },
    mounted() {
        this.loadLaboratoryPersonnel();
        // Listen for storage changes (if user opens multiple tabs)
        window.addEventListener("storage", () => {
            this.loadLaboratoryPersonnel();
        });
    }
};
