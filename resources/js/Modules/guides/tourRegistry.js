export const TOUR_STORAGE_KEYS = {
    privacyConsent: "cbc.guides.privacy-consent",
    autoGuides: "cbc.guides.auto-enabled",
    seenPrefix: "cbc.guides.seen.",
};

const guestPageBase = [
    {
        element: "[data-guide='guest-page-header']",
        popover: {
            title: "What You Can Do Here",
            description:
                "This section explains the purpose of the page and guides you on what to do next.",
            side: "bottom",
            align: "start",
        },
    },
    {
        element: "[data-guide='guest-page-content']",
        popover: {
            title: "Your Main Workspace",
            description:
                "Use this area to search, review, and submit information for the service you need.",
            side: "top",
            align: "start",
        },
    },
    {
        element: "[data-guide='guide-controls']",
        popover: {
            title: "Need Help?",
            description:
                "Click here anytime to see this walkthrough again, or turn automatic tips on or off.",
            side: "left",
            align: "start",
        },
    },
];

export const TOUR_REGISTRY = {
    welcome: {
        title: "Getting Started",
        steps: [
            {
                element: "[data-guide='welcome-hero']",
                popover: {
                    title: "Welcome",
                    description:
                        "This is your starting point for accessing public services and shared tools.",
                    side: "bottom",
                    align: "center",
                },
            },
            {
                element: "[data-guide='welcome-services']",
                popover: {
                    title: "Available Services",
                    description:
                        "Each card opens a different service—rentals, event sign-ups, equipment logger, inventory checkout, and more.",
                    side: "top",
                    align: "center",
                },
            },
            {
                element: "[data-guide='services-bookings-rentals']",
                popover: {
                    title: "Reserve Vehicles & Venues",
                    description: "Need a vehicle for a trip or a venue for an event? Book them here.",
                    side: "top",
                    align: "center",
                },
            },
            {
                element: "[data-guide='services-event-registration']",
                popover: {
                    title: "Sign Up for Events",
                    description: "Find and register for upcoming events hosted by our organization.",
                    side: "top",
                    align: "center",
                },
            },
            {
                element: "[data-guide='services-fes-requests']",
                popover: {
                    title: "Facilities, Equipment, & Supplies Request",
                    description: "Need something for your project? Submit a request for facilities, equipment, or supplies here.",
                    side: "top",
                    align: "center",
                },
            },
            {
                element: "[data-guide='services-lab-equipment-log']",
                popover: {
                    title: "Log Laboratory Equipment you are using",
                    description: "Keep track of the equipment you have checked out and its usage details with our equipment logger.",
                    side: "top",
                    align: "center",
                },
            },
            {
                element: "[data-guide='services-ict-equipment-log']",
                popover: {
                    title: "Log ICT Equipment you are using",
                    description: "Easily manage ICT equipment usage with our ICT logger.",
                    side: "top",
                    align: "center",
                },
            },
            {
                element: "[data-guide='services-supplies-checkout']",
                popover: {
                    title: "Check Out Supplies",
                    description: "Browse available supplies and check out what you need for your work.",
                    side: "top",
                    align: "center",
                },
            },
            {
                element: "[data-guide='services-incident-reports']",
                popover: {
                    title: "Report an Incident",
                    description: "Encountered an issue? Use our incident report form to let us know so we can address it promptly.",
                    side: "top",
                    align: "center",
                },
            },
            {
                element: "[data-guide='services-experiment-log']",
                popover: {
                    title: "Log Your Experiments",
                    description: "Keep a record of your experiments and their progress with our experiment logging tool.",
                    side: "top",
                    align: "center",
                },
            },
            {
                element: "[data-guide='social-links']",
                popover: {
                    title: "Stay Connected",
                    description:
                        "Follow us on social media for updates, news, and support.",
                    side: "left",
                    align: "start",
                },
            },
            {
                element: "[data-guide='social-links-login']",
                popover: {
                    title: "Sign In",
                    description:
                        "Already have an account? Click here to log in.",
                    side: "left",
                    align: "start",
                },
            },
            {
                element: "[data-guide='social-links-register']",
                popover: {
                    title: "Create an Account",
                    description:
                        "New here? Click here to register and get started.",
                    side: "left",
                    align: "start",
                },
            },
            {
                element: "[data-guide='social-links-dashboard']",
                popover: {
                    title: "Your Dashboard",
                    description:
                        "Click here to go to your personal dashboard and manage your account.",
                    side: "left",
                    align: "start",
                },
            },
            {
                element: "[data-guide='social-links-corporate-website']",
                popover: {
                    title: "About Us",
                    description:
                        "Visit our main website to learn more about who we are and what we offer.",
                    side: "left",
                    align: "start",
                },
            },
            {
                element: "[data-guide='social-links-360tour']",
                popover: {
                    title: "Take a Virtual Tour",
                    description:
                        "Explore our facilities from anywhere with this immersive 360° experience.",
                    side: "left",
                    align: "start",
                },
            },
            {
                element: "[data-guide='social-links-pin']",
                popover: {
                    title: "Plant Breeders Network",
                    description:
                        "Discover our plant breeding community and resources through the PIN system.",
                    side: "left",
                    align: "start",
                },
            },
            {
                element: "[data-guide='social-links-facebook']",
                popover: {
                    title: "Follow Us on Facebook",
                    description:
                        "Stay in the loop with our latest news, events, and updates.",
                    side: "left",
                    align: "start",
                },
            },
            {
                element: "[data-guide='social-links-email']",
                popover: {
                    title: "Email Us",
                    description:
                        "Have questions or need help? Send us a message anytime.",
                    side: "left",
                    align: "start",
                },
            },
            {
                element: "[data-guide='privacy-notice']",
                popover: {
                    title: "Your Privacy Matters",
                    description:
                        "Please review and acknowledge our privacy notice before continuing. This helps us keep your information safe.",
                    side: "bottom",
                    align: "center",
                },
            },
            {
                element: "[data-guide='guide-controls']",
                popover: {
                    title: "Help Is Always Here",
                    description:
                        "Replay this introduction whenever you need it, or change your help preferences.",
                    side: "left",
                    align: "start",
                },
            },
        ],
    },
    "event-forms-guest": {
        title: "Event Registration",
        extends: "guest-page",
        steps: [
            {
                element: "[data-guide='form-search']",
                popover: {
                    title: "Find Your Event",
                    description:
                        "Enter the 4-digit event code from your invitation to locate your registration form.",
                    side: "bottom",
                    align: "center",
                },
            },
            {
                element: "[data-guide='form-cards']",
                popover: {
                    title: "Choose Your Event",
                    description:
                        "Matching events appear here. Click one to start your registration or submit your response.",
                    side: "top",
                    align: "center",
                },
            },
        ],
    },
    "fes-request-guest": {
        title: "Request Facilities & Equipment",
        extends: "guest-page",
        steps: [
            {
                element: "[data-guide='fes-request-card']",
                popover: {
                    title: "Submit Your Request",
                    description:
                        "Tell us what you need, when you need it, and provide your contact details. We'll handle the rest.",
                    side: "top",
                    align: "center",
                },
            },
        ],
    },
    "supplies-checkout-guest": {
        title: "Check Out Supplies",
        extends: "guest-page",
        steps: [
            {
                element: "[data-guide='supplies-search']",
                popover: {
                    title: "Find What You Need",
                    description:
                        "Search by name, browse categories, or scan a barcode to quickly locate items.",
                    side: "bottom",
                    align: "center",
                },
            },
            {
                element: "[data-guide='supplies-filters']",
                popover: {
                    title: "Narrow Your Options",
                    description:
                        "Use these filters to focus on the types of supplies you need and find the right item faster.",
                    side: "top",
                    align: "center",
                },
            },
            {
                element: "[data-guide='supplies-barcode-scanner']",
                popover: {
                    title: "Scan Item Barcodes",
                    description: "Use your device's camera to scan item barcodes for instant search results and quick checkout.",
                    side: "top",
                    align: "center",
                },
            },
            {
                element: "[data-guide='supplies-results']",
                popover: {
                    title: "Look for the supply you need",
                    description: "Browse the search results to find the item you want to check out. Click it to see more details and proceed with your request.",
                    side: "top",
                    align: "center",
                },
            },
            {
                element: "[data-guide='supplies-sample-item']",
                popover: {
                    title: "And then click to request it",
                    description: "Select any available item to open the checkout form and request it.",
                    side: "top",
                    align: "center",
                },
            },

        ],
    },
    "incident-report-guest": {
        title: "Report an Incident",
        extends: "guest-page",
        steps: [
            {
                element: "[data-guide='incident-report-form']",
                popover: {
                    title: "Tell Us What Happened",
                    description:
                        "Describe the issue and link it to any related record so our team can follow up with you.",
                    side: "top",
                    align: "center",
                },
            },
        ],
    },
    "equipment-logger-guest": {
        title: "Log Equipment Use",
        extends: "guest-page",
        steps: [
            {
                element: "[data-guide='equipment-summary']",
                popover: {
                    title: "Equipment Details",
                    description:
                        "See the item name, current location, status, and barcode information at a glance.",
                    side: "top",
                    align: "center",
                },
            },
            {
                element: "[data-guide='equipment-actions']",
                popover: {
                    title: "What You Can Do",
                    description:
                        "Check equipment in or out, update usage time, or report its current location.",
                    side: "top",
                    align: "center",
                },
            },
            {
                element: "[data-guide='equipment-active']",
                popover: {
                    title: "Currently Active Equipment",
                    description:
                        "Browse or search this list to see which equipment is in use right now.",
                    side: "top",
                    align: "center",
                },
            },
        ],
    },
    "rental-bookings-public": {
        title: "Book a Rental",
        extends: "guest-page",
        steps: [
            {
                element: "[data-guide='rental-quick-links']",
                popover: {
                    title: "Quick Shortcuts",
                    description:
                        "Jump straight to requesting a vehicle or reserving a venue.",
                    side: "bottom",
                    align: "center",
                },
            },
            {
                element: "[data-guide='calendar-main-area']",
                popover: {
                    title: "Check Availability",
                    description:
                        "View the schedule first to see what's already booked before making your request.",
                    side: "top",
                    align: "center",
                },
            },
            {
                element: "[data-guide='rental-calendar-search']",
                popover: {
                    title: "Find booked vehicles or venues",
                    description:
                        "Use this search to quickly find specific vehicles or venues in the calendar and check their availability.",
                    side: "top",
                    align: "center",
                },
            },
            {
                element: "[data-guide='calendar-filters']",
                popover: {
                    title: "Narrow down your search",
                    description:
                        "Use these filters to focus on the types of vehicles or venues you're interested in and see their availability.",
                    side: "top",
                    align: "center",
                },
            },
            {
                element: "[data-guide='calendar-legends']",
                popover: {
                    title: "Understand the Schedule",
                    description: "Refer to these legends to quickly identify the status of each booking and plan your request accordingly.",
                    side: "top",
                    align: "center",
                },
            },
        ],
    },
    "rental-vehicle-guest": {
        title: "Request a Vehicle",
        extends: "guest-page",
        steps: [
            {
                element: "[data-guide='rental-form-shell']",
                popover: {
                    title: "Vehicle Request Form",
                    description:
                        "Fill in your trip details and follow the on-screen instructions to complete your request.",
                    side: "top",
                    align: "center",
                },
            },
            {
                element: "[data-guide='calendar-main-area']",
                popover: {
                    title: "Check Availability",
                    description:
                        "View the schedule first to see what's already booked before making your request.",
                    side: "top",
                    align: "center",
                },
            },
            {
                element: "[data-guide='rental-calendar-search']",
                popover: {
                    title: "Find booked vehicles or venues",
                    description:
                        "Use this search to quickly find specific vehicles or venues in the calendar and check their availability.",
                    side: "top",
                    align: "center",
                },
            },
            {
                element: "[data-guide='calendar-filters']",
                popover: {
                    title: "Narrow down your search",
                    description:
                        "Use these filters to focus on the types of vehicles or venues you're interested in and see their availability.",
                    side: "top",
                    align: "center",
                },
            },
            {
                element: "[data-guide='calendar-legends']",
                popover: {
                    title: "Understand the Schedule",
                    description: "Refer to these legends to quickly identify the status of each booking and plan your request accordingly.",
                    side: "top",
                    align: "center",
                },
            },
        ],
    },
    "rental-venue-guest": {
        title: "Reserve a Venue",
        extends: "guest-page",
        steps: [
            {
                element: "[data-guide='rental-form-shell']",
                popover: {
                    title: "Venue Request Form",
                    description:
                        "Enter your event details and preferred schedule, then submit when you're ready.",
                    side: "top",
                    align: "center",
                },
            },
            {
                element: "[data-guide='calendar-main-area']",
                popover: {
                    title: "Check Availability",
                    description:
                        "View the schedule first to see what's already booked before making your request.",
                    side: "top",
                    align: "center",
                },
            },
            {
                element: "[data-guide='rental-calendar-search']",
                popover: {
                    title: "Find booked vehicles or venues",
                    description:
                        "Use this search to quickly find specific vehicles or venues in the calendar and check their availability.",
                    side: "top",
                    align: "center",
                },
            },
            {
                element: "[data-guide='calendar-filters']",
                popover: {
                    title: "Narrow down your search",
                    description:
                        "Use these filters to focus on the types of vehicles or venues you're interested in and see their availability.",
                    side: "top",
                    align: "center",
                },
            },
            {
                element: "[data-guide='calendar-legends']",
                popover: {
                    title: "Understand the Schedule",
                    description: "Refer to these legends to quickly identify the status of each booking and plan your request accordingly.",
                    side: "top",
                    align: "center",
                },
            },
        ],
    },
    "rental-vehicle-detail": {
        title: "Your Vehicle Request",
        extends: "guest-page",
        steps: [
            {
                element: "[data-guide='rental-details']",
                popover: {
                    title: "Request Status",
                    description:
                        "See where your vehicle request stands. For privacy, sensitive details are hidden.",
                    side: "top",
                    align: "center",
                },
            },
        ],
    },
    "rental-venue-detail": {
        title: "Your Venue Reservation",
        extends: "guest-page",
        steps: [
            {
                element: "[data-guide='rental-details']",
                popover: {
                    title: "Reservation Status",
                    description:
                        "Check the status of your venue request. Internal details remain private.",
                    side: "top",
                    align: "center",
                },
            },
        ],
    },
    "experiment-monitoring-guest": {
        title: "Monitor Experiments",
        extends: "guest-page",
        steps: [
            {
                element: "[data-guide='experiment-monitoring-content']",
                popover: {
                    title: "Experiment Overview",
                    description:
                        "View public information about ongoing experiments. More details will be added as they become available.",
                    side: "top",
                    align: "center",
                },
            },
        ],
    },
    "google-calendar-public": {
        title: "Use the Calendar",
        steps: [
            {
                element: "[data-guide='google-calendar-embed']",
                popover: {
                    title: "Shared Schedule",
                    description:
                        "Browse upcoming activities and bookings to plan your request at the right time.",
                    side: "left",
                    align: "center",
                },
            },
            {
                element: "[data-guide='guide-controls']",
                popover: {
                    title: "Help Options",
                    description:
                        "Replay this overview or change your automatic help settings here.",
                    side: "left",
                    align: "start",
                },
            },
        ],
    },
    "guest-page": {
        title: "Page Overview",
        steps: guestPageBase,
    },
};

export function resolveTourSteps(key) {
    const definition = TOUR_REGISTRY[key];

    if (!definition) {
        return TOUR_REGISTRY["guest-page"].steps;
    }

    const inherited =
        definition.extends && TOUR_REGISTRY[definition.extends]
            ? TOUR_REGISTRY[definition.extends].steps
            : [];

    return [...inherited, ...(definition.steps ?? [])];
}
