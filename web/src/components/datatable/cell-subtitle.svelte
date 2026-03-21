<script lang="ts">
    interface Props {
        title?: string;
        subtitles?: string[];
        truncate?: boolean;
    }

    let { title, subtitles = [], truncate = false }: Props = $props();
</script>

{#if title}
    <div>{title}</div>
{/if}
{#each subtitles as subtitle}
    <div class="text-muted-foreground text-xs" class:truncate={truncate}>{subtitle}</div>
{/each}