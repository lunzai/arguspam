import { MediaQuery } from 'svelte/reactivity';

export class IsMobile extends MediaQuery {
	constructor(breakpoint: number = 768) {
		super(`max-width: ${breakpoint - 1}px`);
	}
}
