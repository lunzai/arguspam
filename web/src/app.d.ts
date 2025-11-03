// See https://svelte.dev/docs/kit/types#app.d.ts
// for information about these interfaces
declare global {
	namespace App {
		interface Locals {
			authToken: string | null;
			currentOrgId: number | null;
			me: Me | null;
		}
	}
}

export {};
