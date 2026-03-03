/**
 * Unified Console Logger Service
 * Automatically active in local/development, disabled in production
 * 
 * Usage:
 * import ConsoleLogger from '@/Modules/shared/infrastructure/ConsoleLogger';
 * ConsoleLogger.log('Message');
 * ConsoleLogger.error('Error message');
 */

class ConsoleLoggerService {
    private isEnabled: boolean;
    private environment: string;
    private readonly enabledEnvironments = ['local', 'development'];

    constructor() {
        this.environment = this.resolveEnvironment();
        this.isEnabled = this.enabledEnvironments.includes(this.environment.toLowerCase());
    }

    /**
     * Resolve the current application environment
     */
    private resolveEnvironment(): string {
        if (typeof window !== 'undefined' && window.__APP_ENV__) {
            return window.__APP_ENV__;
        }
        // @ts-ignore
        return import.meta.env.VITE_APP_ENV || import.meta.env.MODE || 'production';
    }

    /**
     * Check if logging is enabled
     */
    isLoggingEnabled(): boolean {
        return this.isEnabled;
    }

    /**
     * Get current environment
     */
    getEnvironment(): string {
        return this.environment;
    }

    /**
     * Toggle logging on/off manually
     */
    setEnabled(enabled: boolean): void {
        this.isEnabled = enabled;
    }

    /**
     * Standard console.log
     */
    log(...args: unknown[]): void {
        if (this.isEnabled) {
            console.log(args);
        }
    }

    /**
     * Error level logging
     * Enhanced to display AxiosError details clearly
     */
    error(...args: unknown[]): void {
        if (!this.isEnabled) return;

        args.forEach(arg => {
            // Handle context objects with nested error
            if (arg && typeof arg === 'object' && 'error' in arg && this.isAxiosError((arg as any).error)) {
                const context = arg as any;
                const { config, response, message, code } = context.error;
                
                console.error({
                    tag: context.tag || 'AxiosError',
                    url: context.url || config?.url,
                    method: config?.method?.toUpperCase(),
                    status: response?.status || 'No Response',
                    message: message,
                    code: code,
                    params: context.params,
                    id: context.id,
                    responseData: response?.data,
                });
            } else if (this.isAxiosError(arg)) {
                const { config, response, message, code } = arg;

                console.error({
                    tag: 'AxiosError',
                    method: config?.method?.toUpperCase(),
                    url: config?.url,
                    status: response?.status || 'No Response',
                    message: message,
                    code: code,
                    responseData: response?.data,
                });
            } else {
                console.error({ tag: 'Error', details: arg });
            }
        });
    }

    private isAxiosError(arg: any): arg is any {
        return !!(arg && arg.isAxiosError);
    }
    /**
     * Warning level logging
     * Enhanced to display AxiosError details clearly
     */
    warn(...args: unknown[]): void {
        if (!this.isEnabled) return;

        args.forEach(arg => {
            if (arg && typeof arg === 'object' && 'error' in arg && this.isAxiosError((arg as any).error)) {
                const context = arg as any;
                const { config, response, message, code } = context.error;

                console.warn({
                    tag: context.tag || 'AxiosError',
                    url: context.url || config?.url,
                    method: config?.method?.toUpperCase(),
                    status: response?.status || 'No Response',
                    message: message,
                    code: code,
                    params: context.params,
                    id: context.id,
                    responseData: response?.data,
                });
            } else if (this.isAxiosError(arg)) {
                const { config, response, message, code } = arg;

                console.warn({
                    tag: 'AxiosError',
                    method: config?.method?.toUpperCase(),
                    url: config?.url,
                    status: response?.status || 'No Response',
                    message: message,
                    code: code,
                    responseData: response?.data,
                });
            } else {
                console.warn(`[${this.getTimestamp()}] WARNING:`, arg);
            }
        });
    }

    /**
     * Info level logging
     * Enhanced to display AxiosError details clearly
     */
    info(...args: unknown[]): void {
        if (!this.isEnabled) return;

        args.forEach(arg => {
            if (arg && typeof arg === 'object' && 'error' in arg && this.isAxiosError((arg as any).error)) {
                const context = arg as any;
                const { config, response, message, code } = context.error;

                console.info({
                    tag: context.tag || 'AxiosError',
                    url: context.url || config?.url,
                    method: config?.method?.toUpperCase(),
                    status: response?.status || 'No Response',
                    message: message,
                    code: code,
                    params: context.params,
                    id: context.id,
                    responseData: response?.data,
                });
            } else if (this.isAxiosError(arg)) {
                const { config, response, message, code } = arg;

                console.info({
                    tag: 'AxiosError',
                    method: config?.method?.toUpperCase(),
                    url: config?.url,
                    status: response?.status || 'No Response',
                    message: message,
                    code: code,
                    responseData: response?.data,
                });
            } else {
                console.info(`[${this.getTimestamp()}] INFO:`, arg);
            }
        });
    }

    /**
     * Debug level logging
     * Enhanced to display AxiosError details clearly
     */
    debug(...args: unknown[]): void {
        if (!this.isEnabled) return;

        args.forEach(arg => {
            if (arg && typeof arg === 'object' && 'error' in arg && this.isAxiosError((arg as any).error)) {
                const context = arg as any;
                const { config, response, message, code } = context.error;

                console.debug({
                    tag: context.tag || 'AxiosError',
                    url: context.url || config?.url,
                    method: config?.method?.toUpperCase(),
                    status: response?.status || 'No Response',
                    message: message,
                    code: code,
                    params: context.params,
                    id: context.id,
                    responseData: response?.data,
                });
            } else if (this.isAxiosError(arg)) {
                const { config, response, message, code } = arg;

                console.debug({
                    tag: 'AxiosError',
                    method: config?.method?.toUpperCase(),
                    url: config?.url,
                    status: response?.status || 'No Response',
                    message: message,
                    code: code,
                    responseData: response?.data,
                });
            } else {
                console.debug(`[${this.getTimestamp()}] DEBUG:`, arg);
            }
        });
    }

    /**
     * Table logging for better data visualization
     */
    table(data: unknown): void {
        if (this.isEnabled) {
            console.table(data);
        }
    }

    /**
     * Group related logs
     */
    group(label: string): void {
        if (this.isEnabled) {
            console.group(`📁 ${label}`);
        }
    }

    /**
     * End console group
     */
    groupEnd(): void {
        if (this.isEnabled) {
            console.groupEnd();
        }
    }

    /**
     * Measure performance
     */
    time(label: string): void {
        if (this.isEnabled) {
            console.time(label);
        }
    }

    /**
     * End performance measurement
     */
    timeEnd(label: string): void {
        if (this.isEnabled) {
            console.timeEnd(label);
        }
    }

    /**
     * Assert condition and log if false
     */
    assert(condition: boolean, message: string): void {
        if (this.isEnabled) {
            console.assert(condition, `${this.getTimestamp()} - ${message}`);
        }
    }

    /**
     * Get formatted timestamp
     */
    private getTimestamp(): string {
        const now = new Date();
        return now.toLocaleTimeString('en-US', {
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            fractionalSecondDigits: 3,
        });
    }
}

// Export singleton instance
export default new ConsoleLoggerService();
