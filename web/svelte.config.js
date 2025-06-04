import adapter from '@sveltejs/adapter-node';
import { vitePreprocess } from '@sveltejs/vite-plugin-svelte';

const config = {
	preprocess: vitePreprocess(),
	kit: {
		adapter: adapter(),
		alias: {
			$components: 'src/components',
			$lib: 'src/lib',
			$routes: 'src/routes',
			$types: 'src/lib/types',
			$api: 'src/lib/api',
			$services: 'src/lib/services',
			$stores: 'src/lib/stores',
			$server: 'src/lib/server',
			$ui: 'src/lib/components/ui',
			$models: 'src/lib/types/models',
		}
	}
};

export default config;
