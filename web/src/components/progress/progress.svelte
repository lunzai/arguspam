<script lang="ts">
    import { Progress as ProgressPrimitive } from 'bits-ui';
    import { cn } from '$lib/utils';
    import { onMount } from "svelte";
    import { Tween } from "svelte/motion";
    import { cubicIn, cubicOut } from "svelte/easing";

    interface Props {
        ref?: HTMLDivElement | null;
        value: number;
        max?: number;
        class?: string;
        innerClass?: string;
        animateDuration?: number;
    }
    let {
        ref = $bindable<HTMLDivElement | null>(null),
        value,
        max = 100,
        class: className = '',
        innerClass = '',
        animateDuration = 500,
        ...restProps
    }: Props = $props();

    const tween = new Tween(0, { duration: 1000, easing: cubicOut });

    onMount(() => {
        const timer = setTimeout(() => tween.set(value), animateDuration);
        return () => clearTimeout(timer);
    });

</script>

<ProgressPrimitive.Root
    bind:ref
    data-slot="progress"
    class={cn("bg-muted h-2.5 shadow-mini-inset rounded-full relative flex w-full items-center overflow-x-hidden", className)}
    value={Math.round(tween.current)}
    {max}
    {...restProps}
>
  <div
    data-slot="progress-indicator"
    class={cn("bg-primary size-full flex-1 transition-all shadow-mini-inset", innerClass)}
    style="transform: translateX(-{100 - (100 * (tween.current ?? 0)) / (max ?? 1)}%)"
  ></div>
</ProgressPrimitive.Root>
