<script lang="ts">
	import * as HoverCard from '$ui/hover-card';
	import type { Component } from 'svelte';
    import { MessageSquareMore } from '@lucide/svelte';
    import { cn } from '$lib/utils';

	interface Props {
		triggerLabel?: string;
        hoverContent?: string;
        triggerProps?: any;
        hoverContentClassName?: string;
        hoverContentProps?: any;
        triggerIcon?: Component;
        triggerIconPosition?: 'left' | 'right';
        triggerIconProps?: any;
        showIcon?: boolean;
        triggerClassName?: string;
        hoverContentWrapperClassName?: string;
	}

	let { 
        triggerLabel = '', 
        hoverContent = '', 
        triggerProps = {}, 
        hoverContentClassName = '',
        hoverContentProps = {}, 
        triggerIcon : Icon = MessageSquareMore, 
        triggerIconPosition = 'right', 
        triggerIconProps = {},
        triggerClassName = '',
        hoverContentWrapperClassName = '',
        showIcon = true, 
        ...restProps 
    }: Props = $props();
</script>

<HoverCard.Root {...restProps}>
    <HoverCard.Trigger class={cn('flex items-center gap-2 underline-offset-4 hover:underline focus-visible:outline-2 focus-visible:outline-offset-8 focus-visible:outline-black', triggerClassName)} {...triggerProps}>
        {#if showIcon && triggerIconPosition === 'left'}
            <Icon class={cn('w-4 h-4', triggerIconProps)} />
        {/if}
        <span>{triggerLabel}</span>
        {#if showIcon && triggerIconPosition === 'right'}
        <Icon class={cn('w-4 h-4', triggerIconProps)} />
        {/if}
    </HoverCard.Trigger>
    <HoverCard.Content class={cn('w-80', hoverContentClassName)} {...hoverContentProps}>
        <div class={cn('flex justify-between space-x-4', hoverContentWrapperClassName)}>
            {hoverContent}
        </div>
    </HoverCard.Content>
</HoverCard.Root>