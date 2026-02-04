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
            console.log(`[${this.getTimestamp()}]`, ...args);
        }
    }

    /**
     * Error level logging
     */
    error(...args: unknown[]): void {
        if (this.isEnabled) {
            console.error(`[${this.getTimestamp()}] ❌ ERROR:`, ...args);
        }
    }

    /**
     * Warning level logging
     */
    warn(...args: unknown[]): void {
        if (this.isEnabled) {
            console.warn(`[${this.getTimestamp()}] ⚠️ WARNING:`, ...args);
        }
    }

    /**
     * Info level logging
     */
    info(...args: unknown[]): void {
        if (this.isEnabled) {
            console.info(`[${this.getTimestamp()}] ℹ️ INFO:`, ...args);
        }
    }

    /**
     * Debug level logging
     */
    debug(...args: unknown[]): void {
        if (this.isEnabled) {
            console.debug(`[${this.getTimestamp()}] 🐛 DEBUG:`, ...args);
        }
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
