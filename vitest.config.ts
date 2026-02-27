import { defineConfig, mergeConfig } from 'vitest/config';
import viteConfig from './vite.config';

// configure vitest and merge with existing vite config
export default mergeConfig(
    viteConfig,
    defineConfig({
        test: {
            reporters: 'verbose',
            environment: 'jsdom',
        },
    }),
);
