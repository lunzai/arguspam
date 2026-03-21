<script lang="ts">
    import type { Asset } from '$lib/models/asset';

    interface Props {
        asset: Asset;
    }

    let { asset }: Props = $props();
</script>

<div>{asset.name}</div>
<div class="text-muted-foreground text-xs truncate">
    {asset.host}:{asset.port}
</div>