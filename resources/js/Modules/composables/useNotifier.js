export function useNotifier() {
    const notify = (message, type = 'success', options = {}) => {
        window.dispatchEvent(
            new CustomEvent('cbc:notify', {
                detail: {
                    message,
                    type,
                    duration: options.duration,
                },
            })
        );
    };

    return {
        notify,
        success: (message, options = {}) => notify(message, 'success', options),
        error: (message, options = {}) => notify(message, 'error', options),
        warning: (message, options = {}) => notify(message, 'warning', options),
    };
}
