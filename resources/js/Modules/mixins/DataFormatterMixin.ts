export default {
    props: {
        data: { type: Object },
    },
    data() {
        return {
            intervalId: null,
            countdownData: { days: 0, hours: 0, minutes: 0, seconds: 0 }
        };
    },
    methods: {
        parseDate(dateString:string ) {
            // Ensure correct parsing of "YYYY-MM-DD" format
            const [year, month, day] = dateString.split('-').map(Number);
            return new Date(year, month - 1, day); // Month is zero-based
        },

        updateCountdown() {
            if (!this.data || !this.data.date_to || !this.data.time_to) {
                this.countdownData = { days: 0, hours: 0, minutes: 0, seconds: 0 };
                return;
            }

            const now = new Date();
            const targetDate = this.parseDate(this.data.date_to);

            if (!targetDate) {
                console.error("Failed to parse target date.");
                return;
            }

            // Extract time from "HH:MM:SS" and set it on the target date
            const [hours, minutes, seconds] = this.data.time_to.split(':').map(Number);
            targetDate.setHours(hours, minutes, seconds);
            // @ts-ignore
            const timeDifference = targetDate - now;

            if (timeDifference <= 0) {
                this.countdownData = { days: 0, hours: 0, minutes: 0, seconds: 0 };
                clearInterval(this.intervalId);
                return;
            }

            this.countdownData = {
                days: Math.floor(timeDifference / (1000 * 60 * 60 * 24)),
                hours: Math.floor((timeDifference % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60)),
                minutes: Math.floor((timeDifference % (1000 * 60 * 60)) / (1000 * 60)),
                seconds: Math.floor((timeDifference % (1000 * 60)) / 1000),
            };
        },
        startCountdown() {
            this.updateCountdown();
            this.intervalId = setInterval(this.updateCountdown, 1000);
        },
        formatDate(dateString: string) {
            if (!dateString) return "";

            const [year, month, day] = dateString.split("-").map(Number);
            const date = new Date(year, month - 1, day); // Month is zero-based in JS

            return new Intl.DateTimeFormat("en-US", {
                year: "numeric",
                month: "long",
                day: "numeric"
            }).format(date);
        },
        formatTime(timeString: string) {
            if (!timeString) return "";

            const [hours, minutes, seconds] = timeString.split(":").map(Number);

            // Convert to 12-hour format
            const ampm = hours >= 12 ? "PM" : "AM";
            const formattedHours = hours % 12 === 0 ? 12 : hours % 12;
            // @ts-ignore
            return `${formattedHours}:${minutes.toString().padStart(2, "0")} ${ampm}`;
        },
        arrayToString(data: string[]) {
            if (Array.isArray(data)) {
                return data.join(", ");
            }
            if (typeof data === "string") {
                try {
                    const parsed = JSON.parse(data);
                    return Array.isArray(parsed) ? parsed.join(", ") : data;
                } catch {
                    return data; // fallback: return string as-is
                }
            }
            return "";
        }
    },
    computed: {
        countdownDisplay() {
            return `${this.countdownData.days}d ${this.countdownData.hours}h ${this.countdownData.minutes}m ${this.countdownData.seconds}s`;
        },
        isExpired() {
            return !this.countdownData.days && !this.countdownData.hours && !this.countdownData.minutes && !this.countdownData.seconds;
        }
    },
}
