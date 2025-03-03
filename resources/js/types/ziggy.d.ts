declare module 'ziggy-js' {
    import { Config, Router } from 'ziggy-js';
    
    export function route(
        name?: string,
        params?: any,
        absolute?: boolean,
        config?: Config
    ): string;

    export type { Config, Router };
} 