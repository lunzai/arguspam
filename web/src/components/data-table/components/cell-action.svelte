<script lang="ts">
    import type { Component } from "svelte";
    import { Button } from "$ui/button";
    import { cn } from "$lib/utils";

    type Action = {
        label: string;
        icon?: Component;
        href?: string;
        onClick?: () => void;
        variant?: 'outline' | 'default' | 'destructive' | 'ghost' | 'link';
        size?: 'default' | 'sm' | 'lg' | 'icon';
        class?: string;
    }

    interface Props {
        actions?: Action[];
        className?: string;
    }

    let {
        actions = [],
        className = '',
        ...restProps
    }: Props = $props();
</script>

<div class={cn("flex", className)} {...restProps}>
    {#each actions as action}
        <Button 
            variant={action.variant || 'default'} 
            size={action.size || 'default'} 
            onclick={() => action.onClick}
            class={cn('duration-150 transition-all', action.class)}
            href={action.href || undefined}
        >
            {#if action.icon}
                <action.icon class="w-4 h-4" />
            {/if}
            {action.label}
        </Button>
    {/each}
</div>