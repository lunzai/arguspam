<script lang="ts">
    import { shortDateTime } from '$lib/utils/date';
    import { formatDistanceStrict } from 'date-fns';

    interface Props {
        startDatetime: Date;
        endDatetime: Date;
    }

    let { startDatetime, endDatetime }: Props = $props();
</script>

<div>{shortDateTime(startDatetime)} - {shortDateTime(endDatetime)}</div>
<div class="text-muted-foreground text-xs">{formatDistanceStrict(startDatetime, endDatetime)}</div>