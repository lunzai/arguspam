<script lang="ts">
	import { page } from '$app/state';
	import { goto } from '$app/navigation';
    function goBack() {
        goto(-1);
    }
</script>
<div class="h-svh w-full">
    <div class="m-auto flex h-full w-full flex-col items-center justify-center gap-2">
        <h1 class="text-[length:7rem] leading-tight font-bold">{page.status}</h1>
        <span class="font-medium">{page.error.message}</span>
        {#if page.status === 403}
        <p class="text-muted-foreground text-center">
            You don't have necessary permission to view this resource.
        </p>
        {:else if page.status === 404}
        <p class="text-muted-foreground text-center">
            The page you are looking for does not exist.
        </p>
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
            <button on:click={goBack} class="
                inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md 
                text-sm font-medium transition-colors focus-visible:outline-hidden focus-visible:ring-1 
                focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 
                [&amp;_svg]:pointer-events-none [&amp;_svg]:size-4 [&amp;_svg]:shrink-0 border 
                border-input bg-background shadow-xs hover:bg-accent hover:text-accent-foreground 
                h-9 px-4 py-2">
                Go Back
            </button>
            <a class="
                inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm 
                font-medium transition-colors focus-visible:outline-hidden focus-visible:ring-1 
                focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 
                [&amp;_svg]:pointer-events-none [&amp;_svg]:size-4 [&amp;_svg]:shrink-0 bg-primary 
                text-primary-foreground shadow-sm hover:bg-primary/90 h-9 px-4 py-2" href="/">
                Back to Home
            </a>
        </div>
    </div>
</div>