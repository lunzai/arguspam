import adapter from '@sveltejs/adapter-node';
import { vitePreprocess } from '@sveltejs/vite-plugin-svelte';

const config = {
	preprocess: vitePreprocess(),
	kit: {
		adapter: adapter(),
		version: {
			name:
				process.env.VITE_APP_VERSION || process.env.npm_package_version || Date.now().toString(),
			pollInterval: 300000
		},
		csrf: {
			checkOrigin: true,
			trustedOrigins: [process.env.PUBLIC_API_URL, process.env.ORIGIN].filter(Boolean)
		},
		csp: {
			mode: 'auto', // or 'hash' depending on your needs
			directives: {
				'default-src': ['self'],
				'script-src': ['self'],
				'style-src': ['self', 'unsafe-inline', 'https://fonts.googleapis.com'],
				'font-src': ['self', 'data:', 'https://fonts.gstatic.com'],
				'img-src': ['self', 'data:', 'https:'],
				'connect-src': ['self', process.env.PUBLIC_API_URL].filter(Boolean),
				'frame-ancestors': ['none'],
				'base-uri': ['self'],
				'form-action': ['self'],
				'object-src': ['none'],
				'upgrade-insecure-requests': true // Force HTTPS
			}
		},
		output: {
			preloadStrategy: 'modulepreload' // Better performance for modern browsers
		},
		alias: {
			$components: 'src/components',
			$lib: 'src/lib',
			$routes: 'src/routes',
			$api: 'src/lib/api',
			$services: 'src/lib/services',
			$stores: 'src/lib/stores',
			$server: 'src/lib/server',
			$ui: 'src/lib/components/ui',
			$models: 'src/lib/models',
			$utils: 'src/lib/utils',
			$requests: 'src/lib/requests',
			$resources: 'src/lib/resources',
			$validations: 'src/lib/validations'
		}
	}
};

export default config;
