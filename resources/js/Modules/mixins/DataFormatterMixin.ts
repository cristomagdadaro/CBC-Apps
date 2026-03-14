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
            if (!dateString) return null;
            const parts = dateString.split('-');
            if (parts.length !== 3) return null;
            const [year, month, day] = parts.map(Number);
            if (!year || !month || !day) return null;
            return new Date(year, month - 1, day); // Month is zero-based
        },

        updateCountdown() {
            // Use event start (date_from/time_from) as the countdown target.
            // If those are missing, fall back to event end (date_to/time_to).
            if (!this.data) {
                this.countdownData = { days: 0, hours: 0, minutes: 0, seconds: 0 };
                return;
            }

            const hasStart = this.data.date_from && this.data.time_from;
            const hasEnd = this.data.date_to && this.data.time_to;

            if (!hasStart && !hasEnd) {
                this.countdownData = { days: 0, hours: 0, minutes: 0, seconds: 0 };
                return;
            }

            const now = new Date();
            const baseDateStr = hasStart ? this.data.date_from : this.data.date_to;
            const baseTimeStr = hasStart ? this.data.time_from : this.data.time_to;

            const targetDate = this.parseDate(baseDateStr);
            if (!targetDate) {
                this.countdownData = { days: 0, hours: 0, minutes: 0, seconds: 0 };
                return;
            }

            const [hours = 0, minutes = 0, seconds = 0] = String(baseTimeStr).split(':').map(Number);
            targetDate.setHours(hours, minutes, seconds || 0, 0);

            const timeDifference = targetDate.getTime() - now.getTime();

            if (timeDifference <= 0) {
                // Event already started (or ended if only end date is provided)
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
        formatDate(dateInput: string | Date | null) {
            if (!dateInput) return "";

            let date: Date;

            if (dateInput instanceof Date) {
                date = new Date(dateInput.getTime());
            } else {
                const raw = String(dateInput).trim();
                const normalized = raw.includes('T') ? raw : raw.replace(' ', 'T');
                const parsed = new Date(normalized);

                if (!Number.isNaN(parsed.getTime())) {
                    date = parsed;
                } else {
                    const dateOnly = raw.slice(0, 10);
                    const [year, month, day] = dateOnly.split("-").map(Number);

                    if (!year || !month || !day) {
                        return "";
                    }

                    date = new Date(year, month - 1, day);
                }
            }

            if (Number.isNaN(date.getTime())) {
                return "";
            }

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
        /**
         * Format a full datetime into a friendly label.
         * Accepts either:
         *  - a combined ISO-like string (e.g. "2025-11-16T10:30:00"), or
         *  - separate date (YYYY-MM-DD) and time (HH:MM or HH:MM:SS) strings.
         */
        formatDateTime(dateInput: string | Date | null, timeInput?: string | null) {
            if (!dateInput && !timeInput) return "";

            let date: Date;

            if (dateInput instanceof Date) {
                date = new Date(dateInput.getTime());
            } else if (dateInput && !timeInput) {
                // Single string: try native Date parse (for ISO) and fallback to YYYY-MM-DD
                const parsed = new Date(dateInput);
                if (!isNaN(parsed.getTime())) {
                    date = parsed;
                } else {
                    // Fallback: assume "YYYY-MM-DD HH:MM[:SS]" or "YYYY-MM-DD"
                    const [dPart, tPart] = dateInput.split(" ");
                    const base = this.parseDate(dPart);
                    if (tPart) {
                        const [h, m, s = "0"] = tPart.split(":");
                        base.setHours(Number(h), Number(m), Number(s));
                    }
                    date = base;
                }
            } else {
                // Separate date + time strings
                const base = typeof dateInput === 'string' && dateInput
                    ? this.parseDate(dateInput)
                    : new Date();
                if (timeInput) {
                    const [h, m, s = "0"] = timeInput.split(":");
                    base.setHours(Number(h), Number(m), Number(s));
                }
                date = base;
            }

            if (!date || isNaN(date.getTime())) return "";

            return new Intl.DateTimeFormat("en-US", {
                year: "numeric",
                month: "short",
                day: "2-digit",
                hour: "2-digit",
                minute: "2-digit",
            }).format(date);
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
        },
        getExpirationStatus(expirationDate) {
            if (!expirationDate) return null;
            const [year, month, day] = expirationDate.split('-').map(Number);
            const expDate = new Date(Date.UTC(year, month - 1, day));
            const today = new Date(Date.UTC(new Date().getUTCFullYear(), new Date().getUTCMonth(), new Date().getUTCDate()));
            const diffTime = expDate.getTime() - today.getTime();
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            if (diffDays < 0) {
                return 'expired';
            } else if (diffDays === 0) {
                return 'expiring_today';
            } else if (diffDays <= 30) {
                return 'expiring_soon';
            }
            return null;
        }
    },
    computed: {
        countdownDisplay() {
            return `${this.countdownData.days}d ${this.countdownData.hours}h ${this.countdownData.minutes}m ${this.countdownData.seconds}s`;
        },
        isExpired() {
            // Consider the event expired only if we have an end datetime and it's in the past.
            if (!this.data || !this.data.date_to || !this.data.time_to) {
                return false;
            }

            const endDate = this.parseDate(this.data.date_to);
            if (!endDate) return false;

            const [hours = 0, minutes = 0, seconds = 0] = String(this.data.time_to).split(':').map(Number);
            endDate.setHours(hours, minutes, seconds || 0, 0);

            return new Date().getTime() >= endDate.getTime();
        }
    },
}
