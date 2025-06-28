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

<Badge variant="outline" class={cn('capitalize text-gray-500 bg-gray-50 border-gray-200', className)} {...restProps}>
    {@render children?.()}
</Badge>