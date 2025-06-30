<script lang="ts">
    import { Button } from '$ui/button';
    import { Trash2 } from '@lucide/svelte';
    import { cn } from '$lib/utils';
    import type { Component } from 'svelte';
    import * as AlertDialog from '$ui/alert-dialog';

    interface Props {
        label?: string;
        onConfirm: (setOpen: (open: boolean) => void) => void;
        className?: string;
        icon?: Component;
        iconClass?: string;
        title?: string;
        description?: string;
        cancelButtonLabel?: string;
        deleteButtonLabel?: string;
    }

    let { 
        onConfirm, 
        label, 
        className = '', 
        icon : Icon = Trash2,
        iconClass = '',
        title = 'Are you sure?',
        description,
        cancelButtonLabel = 'Cancel',
        deleteButtonLabel = 'Delete',
    }: Props = $props();
    
    let isOpen = $state(false);
    
    const setOpen = (open: boolean) => {
        isOpen = open;
    }
</script>

<AlertDialog.Root bind:open={isOpen}>
    <AlertDialog.Trigger>
        <Button variant="outline" class={cn('text-destructive border-red-200 transition-all duration-200 hover:bg-red-50 hover:text-red-500', className)}>
            <Icon class={cn('h-4 w-4', iconClass)} />
            {#if label}
                {label}
            {/if}
        </Button>
    </AlertDialog.Trigger>
    <AlertDialog.Content>
        <AlertDialog.Header>
            <AlertDialog.Title>
                {title}
            </AlertDialog.Title>
            {#if description}
                <AlertDialog.Description>
                    {description}
                </AlertDialog.Description>
            {/if}
        </AlertDialog.Header>
        <AlertDialog.Footer>
            <AlertDialog.Cancel>
                {cancelButtonLabel}
            </AlertDialog.Cancel>
            <AlertDialog.Action onclick={() => onConfirm(setOpen)}>
                {deleteButtonLabel}
            </AlertDialog.Action>
        </AlertDialog.Footer>
    </AlertDialog.Content>
</AlertDialog.Root>