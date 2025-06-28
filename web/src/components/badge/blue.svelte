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

<Badge variant="outline" class={cn('capitalize text-blue-500 bg-blue-50 border-blue-200', className)} {...restProps}>
    {@render children?.()}
</Badge>