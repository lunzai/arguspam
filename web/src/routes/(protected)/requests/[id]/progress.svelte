<script lang="ts">
	import * as Card from '$ui/card';
	import type { Request } from '$models/request';

	let { model }: { model: Request } = $props();
</script>

<Card.Root class="w-full">
	<Card.Content class="space-y-4"></Card.Content>
</Card.Root>
