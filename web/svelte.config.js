import adapter from '@sveltejs/adapter-node';
import { vitePreprocess } from '@sveltejs/vite-plugin-svelte';

const config = {
	preprocess: vitePreprocess(),
	kit: {
		adapter: adapter(),
        version: {
            name: process.env.npm_package_version || Date.now().toString(),
            pollInterval: 300000 // Poll every 5 minutes for new versions
        },
		csrf: {
			trustedOrigins: [
                process.env.PUBLIC_API_URL,
                process.env.ORIGIN
            ].filter(Boolean)
		},
        csp: {
            mode: 'auto', // or 'hash' depending on your needs
            directives: {
                'default-src': ['self'],
                'script-src': ['self'], // Add nonce handling
                'style-src': ['self', 'unsafe-inline'], // May need unsafe-inline for Svelte
                'img-src': ['self', 'data:', 'https:'],
                'font-src': ['self', 'data:'],
                'connect-src': ['self', process.env.PUBLIC_API_URL].filter(Boolean),
                'frame-ancestors': ['none'], // Prevent clickjacking
                'base-uri': ['self'],
                'form-action': ['self']
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
