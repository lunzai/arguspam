<script lang="ts">
    import { Button } from '$ui/button';
    import { cn } from '$lib/utils';
    import type { Component } from 'svelte';

    type Props = {
        label: string;
        icon?: Component;
        href?: string;
        onclick?: () => void;
        variant?: 'outline' | 'default' | 'destructive' | 'ghost' | 'link';
        size?: 'default' | 'sm' | 'lg' | 'icon';
        className?: string;
    };

    let { 
        label, 
        icon: Icon, 
        href, 
        onclick, 
        className = '', 
        variant = 'outline', 
        size = 'sm', 
        ...restProps 
    }: Props = $props();
</script>
<Button {variant} {size} {href} onclick={onclick} class={cn('text-gray-500', className)} {...restProps}>
    {#if Icon}
        <Icon class="h-4 w-4" />
    {/if}
    {label}
</Button>