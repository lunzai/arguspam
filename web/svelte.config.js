import adapter from '@sveltejs/adapter-node';
import { vitePreprocess } from '@sveltejs/vite-plugin-svelte';

const config = {
	preprocess: vitePreprocess(),
	kit: {
		adapter: adapter(),
		csrf: {
			checkOrigin: false
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
			$validations: 'src/lib/validations',
		}
	}
};

export default config;
