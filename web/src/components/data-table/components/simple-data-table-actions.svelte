<script lang="ts">
    import { Button } from '$ui/button';
    import { Ellipsis } from '@lucide/svelte';
    import { cn } from '$lib/utils';
    import * as DropdownMenu from '$ui/dropdown-menu';
	import type { Component } from 'svelte';
    import type { ButtonProps } from '$ui/button';
    import { goto } from '$app/navigation';

    interface Props {
        triggerButtonProps?: ButtonProps,
        triggerButtonIcon?: Component,
        triggerButtonIconClass?: String,
        menuItems: menuItemProps[],
        menuItemsClass?: string,
    };
    interface menuItemProps {
        label: string,
        shortcut?: string,
        icon?: Component,
        iconClass?: string,
        href?: string,
        onSelect?: () => void,
    }
    let {
        triggerButtonProps = {},
        triggerButtonIcon : TriggerButtonIcon = Ellipsis,
        triggerButtonIconClass = '',        
        menuItems,
        menuItemsClass = '',
    } : Props = $props();
</script>

<DropdownMenu.Root>
    <DropdownMenu.Trigger>
        <Button {...triggerButtonProps} class={cn('relative size-8 p-0', triggerButtonIconClass)}>
            <TriggerButtonIcon class={cn('', triggerButtonIconClass)} />
        </Button>
    </DropdownMenu.Trigger>
    <DropdownMenu.Content>
        {#each menuItems as item}
            <DropdownMenu.Item onSelect={() => {
                if (item.onSelect) {
                    return item.onSelect();
                } else if (item.href) {
                    return goto(item.href);
                }
            }} class={cn('', menuItemsClass)}>
                {#if item.icon}
                    <item.icon class={cn('', item.iconClass)} />
                {/if}
                {item.label}
            </DropdownMenu.Item>
        {/each}
    </DropdownMenu.Content>
</DropdownMenu.Root>