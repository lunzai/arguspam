<script lang="ts">
    import { cn } from "$lib/utils";

    type Props = {
        ref?: HTMLElement | null;
        children: () => any;
        divider?: string | null;
        class?: string;
    }

    let {
        ref = $bindable(null),
        children,
        divider = 'divide-gray-100',
        class: className,
        ...restProps
    } : Props = $props();
</script>

<div 
    bind:this={ref} 
    data-slot="description-list" 
    class={cn("border-gray-100", className)} 
    {...restProps}
>
    <dl class="{divider ? `divide-y ${divider}`:''}">
        {@render children?.()}
    </dl>
</div>