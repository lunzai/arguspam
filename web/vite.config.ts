import tailwindcss from '@tailwindcss/vite';
import { svelteTesting } from '@testing-library/svelte/vite';
import { sveltekit } from '@sveltejs/kit/vite';
import { defineConfig, loadEnv } from 'vite';

export default defineConfig(({ mode }) => {
	const env = loadEnv(mode, process.cwd(), '');

	return {
		plugins: [tailwindcss(), sveltekit()],
        build: {
			// Minification automatically removes comments in production
			minify: mode === 'production' ? 'esbuild' : false,
			reportCompressedSize: false
		},
		server: {
			allowedHosts: env.VITE_ALLOWED_HOSTS?.split(','),
			port: Number(env.VITE_PORT) || 5173,
			host: true,
			hmr: {
				protocol: 'ws',
				host: 'localhost',
				port: Number(env.VITE_PORT) || 5173,
				clientPort: Number(env.VITE_PORT) || 5173
			}
		},
		test: {
			workspace: [
				{
					extends: './vite.config.ts',
					plugins: [svelteTesting()],
					test: {
						name: 'client',
						environment: 'jsdom',
						clearMocks: true,
						include: ['src/**/*.svelte.{test,spec}.{js,ts}'],
						exclude: ['src/lib/server/**'],
						setupFiles: ['./vitest-setup-client.ts']
					}
				},
				{
					extends: './vite.config.ts',
					test: {
						name: 'server',
						environment: 'node',
						include: ['src/**/*.{test,spec}.{js,ts}'],
						exclude: ['src/**/*.svelte.{test,spec}.{js,ts}']
					}
				}
			]
		}
	};
});
