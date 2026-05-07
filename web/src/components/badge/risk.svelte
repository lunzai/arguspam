<script lang="ts">
    import { Badge } from '$ui/badge';
    import { cn } from '$lib/utils';

    let { 
        risk,
        class: className,
        captialize = true,
        ...restProps
    }: { 
        risk: string,
        class: string,
        captialize?: boolean,
    } = $props();

    let textClass: string = 'text-slate-500';
    let borderClass: string = 'border-slate-200';
    let bgClass: string = 'bg-slate-100';
    let dotClass: string = 'bg-slate-500';

    switch (risk.toLowerCase()) {
        case 'low':
            textClass = 'text-green-500';
            borderClass = 'border-green-200';
            bgClass = 'bg-green-100';
            dotClass = 'bg-green-500';
            break;
        case 'medium':
            textClass = 'text-blue-500';
            borderClass = 'border-blue-200';
            bgClass = 'bg-blue-100';
            dotClass = 'bg-blue-500';
            break;
        case 'high':
            textClass = 'text-orange-500';
            borderClass = 'border-orange-200';
            bgClass = 'bg-orange-100';
            dotClass = 'bg-orange-500';
            break;
        case 'critical':
            textClass = 'text-red-500';
            borderClass = 'border-red-200';
            bgClass = 'bg-red-100';
            dotClass = 'bg-red-500';
            break;
        default:
            textClass = 'text-slate-500';
            borderClass = 'border-slate-200';
            bgClass = 'bg-slate-100';
            dotClass = 'bg-slate-500';
            break;
    }
</script>

<span class="{cn('inline-flex items-center gap-2 border rounded-full text-xs px-2.5 py-1', borderClass, bgClass, textClass, className)}" {...restProps}>
    <span class={cn('inline-block w-2 h-2 rounded-full', dotClass)}></span>
    <span 
        class={cn('font-black', textClass)}
        class:uppercase={captialize}
    >{risk}</span>
</span>
