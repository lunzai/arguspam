import adapter from '@sveltejs/adapter-node';
import { vitePreprocess } from '@sveltejs/vite-plugin-svelte';

// Detect if we're in development mode
const isDev = process.env.NODE_ENV === 'development' || process.env.DEV === 'true';
// Get Vite port for WebSocket HMR connection (defaults to 5173)
const vitePort = process.env.VITE_PORT || '5173';

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
				// 'unsafe-hashes' allows hashed inline scripts
				// 'unsafe-inline' is needed for inline event handlers (onclick, onchange, etc.)
				// Note: 'unsafe-hashes' does NOT work for event handlers, only for <script> tags
				// In Svelte, event handlers are compiled at build time, so they're safe (not from user input)
				// However, ideally Svelte should compile these to addEventListener for better CSP compliance
				'script-src': [
					'self',
					'unsafe-hashes', // For hashed inline scripts
					'unsafe-inline' // Required for inline event handlers (onclick, onchange, etc.)
					// Security note: This is relatively safe because Svelte compiles handlers at build time
					// Consider refactoring to use addEventListener() for stricter CSP in the future
				],
				'style-src': ['self', 'unsafe-inline', 'https://fonts.googleapis.com'],
				'font-src': ['self', 'data:', 'https://fonts.gstatic.com'],
				'img-src': ['self', 'data:', 'https:'],
				// In development, allow Vite HMR WebSocket connections
				'connect-src': [
					'self',
					process.env.PUBLIC_API_URL,
					// Allow localhost connections in dev (for API and Vite HMR)
					// CSP doesn't support wildcards, so we need to specify the exact WebSocket URL
					...(isDev ? [`ws://localhost:${vitePort}`, `ws://127.0.0.1:${vitePort}`] : [])
				].filter(Boolean),
				'frame-ancestors': ['none'],
				'base-uri': ['self'],
				'form-action': ['self'],
				'object-src': ['none'],
				// Only force HTTPS in production
				...(isDev ? {} : { 'upgrade-insecure-requests': true })
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
