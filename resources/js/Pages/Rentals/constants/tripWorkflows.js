export const rentalVehicleTripOptions = [
    {
        name: "dedicated_trip",
        label: "Dedicated Trip",
        description: "Dedicated vehicle from start to end of the inclusive trip date.",
    },
    {
        name: "drop_off_and_pickup",
        label: "Drop-Off and Pickup",
        description: "Drop-off to destination and return only on the pickup date.",
    },
    {
        name: "one_way_drop_off",
        label: "One-Way Drop-Off",
        description: "Drop-off to destination and return to CES.",
    },
    {
        name: "one_way_pick_up",
        label: "One-Way Pick-Up",
        description: "Pick up from a destination and return to CES.",
    },
    {
        name: "shuttle_service",
        label: "Shuttle Service",
        description: "Travel multiple times to multiple locations.",
    },
    {
        name: "multi_stop_trip",
        label: "Multi-Stop Trip",
        description: "Multiple declared destinations in a single trip flow.",
    },
];

export const rentalVehicleTripOptionsByName = rentalVehicleTripOptions.reduce((carry, option) => {
    carry[option.name] = option;
    return carry;
}, {});

export const getTripTypeMeta = (tripType) => {
    return rentalVehicleTripOptionsByName[tripType] || {
        name: tripType || "dedicated_trip",
        label: "Trip Workflow",
        description: "Trip workflow details were not provided.",
    };
};

export const buildTripRoute = (tripType, destinations = [], originLabel = "CES") => {
    const cleanedStops = destinations.filter(Boolean);
    const primaryDestination = cleanedStops[0] || "Destination";
    const extraStops = cleanedStops.slice(1);

    switch (tripType) {
        case "drop_off_and_pickup":
            return [
                { label: originLabel, kind: "origin" },
                { label: primaryDestination, kind: "stop" },
                { label: `${primaryDestination} pickup`, kind: "transfer" },
                { label: originLabel, kind: "return" },
            ];
        case "one_way_drop_off":
            return [
                { label: originLabel, kind: "origin" },
                { label: primaryDestination, kind: "stop" },
                { label: originLabel, kind: "return" },
            ];
        case "one_way_pick_up":
            return [
                { label: primaryDestination, kind: "origin" },
                { label: originLabel, kind: "return" },
            ];
        case "shuttle_service":
            return [
                { label: originLabel, kind: "origin" },
                ...cleanedStops.map((label) => ({ label, kind: "stop" })),
                { label: "Repeat trips as scheduled", kind: "transfer" },
                { label: originLabel, kind: "return" },
            ];
        case "multi_stop_trip":
            return [
                { label: originLabel, kind: "origin" },
                { label: primaryDestination, kind: "stop" },
                ...extraStops.map((label) => ({ label, kind: "stop" })),
                { label: originLabel, kind: "return" },
            ];
        case "dedicated_trip":
        default:
            return [
                { label: originLabel, kind: "origin" },
                ...cleanedStops.map((label) => ({ label, kind: "stop" })),
                { label: "Vehicle stays assigned until trip end", kind: "transfer" },
            ];
    }
};
