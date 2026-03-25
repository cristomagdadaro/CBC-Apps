export function extractRequestErrorMessage(error, fallback = 'An unexpected error occurred.') {
    if (error?.response?.data?.message) {
        return error.response.data.message;
    }

    if (error?.message) {
        return error.message;
    }

    return fallback;
}

export function normalizeRequestDisplayText(value, fallback = 'N/A') {
    if (value === null || value === undefined) {
        return fallback;
    }

    const normalized = String(value).trim();

    return normalized === '' ? fallback : normalized;
}