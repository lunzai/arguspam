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

<Badge variant="outline" class={cn('capitalize text-yellow-500 bg-yellow-50 border-yellow-200', className)} {...restProps}>
    {@render children?.()}
</Badge>