<script lang="ts">
	import { Button } from "$ui/button/index.js";
	import { enhance } from '$app/forms';
	import { toast } from "svelte-sonner";
	import { auth } from '$lib/stores/auth';

	let {
		variant = "outline",
		size = "sm",
		class: className = "",
		...restProps
	} = $props();

	let isLoading = $state(false);
</script>

<form 
	method="POST" 
	action="/auth/logout"
	use:enhance={() => {
		isLoading = true;
		return async ({ result, update }) => {
			isLoading = false;
			if (result.type === 'redirect') {
				// Clear client-side auth state
				auth.clear();
				toast.success('Logged out successfully');
			}
			await update();
		};
	}}
>
	<Button 
		type="submit" 
		{variant} 
		{size} 
		class={className}
		disabled={isLoading}
		{...restProps}
	>
		{#if isLoading}
			<span class="i-lucide-loader-2 animate-spin mr-2"></span>
			Logging out...
		{:else}
			Logout
		{/if}
	</Button>
</form> 