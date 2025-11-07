<script lang="ts">
	import { page } from '$app/state';
	function goBack() {
		history.back();
	}
</script>

<div class="h-svh w-full">
	<div class="m-auto flex h-full w-full flex-col items-center justify-center gap-2">
		<h1 class="text-[length:7rem] leading-tight font-bold">{page.status}</h1>
		<span class="font-medium">{page.error?.message || 'An error occurred'}</span>
		{#if page.status === 403}
			<p class="text-muted-foreground text-center">
				You don't have necessary permission to view this resource.
			</p>
		{:else if page.status === 404}
			<p class="text-muted-foreground text-center">The page you are looking for does not exist.</p>
		{:else if page.status === 500}
			<p class="text-muted-foreground text-center">
				An error occurred while processing your request.
			</p>
		{:else}
			<p class="text-muted-foreground text-center">
				An error occurred while processing your request.
			</p>
		{/if}
		<div class="mt-6 flex gap-4">
			<button
				on:click={goBack}
				class="
                focus-visible:ring-ring [&amp;_svg]:pointer-events-none [&amp;_svg]:size-4 [&amp;_svg]:shrink-0 border-input bg-background
                hover:bg-accent hover:text-accent-foreground inline-flex h-9 items-center
                justify-center gap-2 rounded-md
                border px-4 py-2 text-sm
                font-medium whitespace-nowrap shadow-xs transition-colors focus-visible:ring-1
                focus-visible:outline-hidden disabled:pointer-events-none disabled:opacity-50"
			>
				Go Back
			</button>
			<a
				class="
                focus-visible:ring-ring [&amp;_svg]:pointer-events-none [&amp;_svg]:size-4 [&amp;_svg]:shrink-0 bg-primary text-primary-foreground hover:bg-primary/90
                inline-flex h-9 items-center justify-center
                gap-2 rounded-md px-4
                py-2 text-sm font-medium whitespace-nowrap
                shadow-sm transition-colors focus-visible:ring-1 focus-visible:outline-hidden disabled:pointer-events-none disabled:opacity-50"
				href="/"
			>
				Back to Home
			</a>
		</div>
	</div>
</div>
