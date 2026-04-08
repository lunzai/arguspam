<script lang="ts">
    import { cn } from '$lib/utils.js';
    import type { HTMLAttributes } from 'svelte/elements';

    let {
        class: className,
        title = '',
        description = '',
        children,
        titleClassName,
        descriptionClassName = '',
        ...restProps
    }: HTMLAttributes<HTMLDivElement> & {
        title: string;
        description?: string | null;
        titleClassName?: string;
        descriptionClassName?: string;
    } = $props();
</script>

<div class="{cn('flex items-center justify-between', className)}" {...restProps}>
    <div class="space-y-2">
        <h1 class="{cn('text-3xl font-extrabold tracking-tight', titleClassName)}">{title}</h1>
        {#if description}
            <p class="{cn('text-sm max-w-xl text-slate-500', descriptionClassName)}">{description}</p>
        {/if}
    </div>
    {@render children?.()}
</div>