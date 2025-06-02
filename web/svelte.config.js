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
			$types: 'src/types',
			$ui: 'src/lib/components/ui'
		}
	}
};

export default config;
