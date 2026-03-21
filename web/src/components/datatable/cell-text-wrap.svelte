<script lang="ts">
    interface Props {
        text: string;
        className?: string;
    }

    let { text, className = 'max-w-80' }: Props = $props();
</script>

<div class="wrap-break-word whitespace-break-spaces {className}">{text}</div>