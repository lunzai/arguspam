<script lang="ts">
    import { Badge } from '$ui/badge';
    import { cn } from '$lib/utils';

    type Props = {
        children: () => any;
        class?: string;
    }

    let {
        children,
        class: className,
        ...restProps
    } : Props = $props();
</script>

<Badge variant="outline" class={cn('capitalize text-red-500 bg-red-50 border-red-200', className)} {...restProps}>
    {@render children?.()}
</Badge>