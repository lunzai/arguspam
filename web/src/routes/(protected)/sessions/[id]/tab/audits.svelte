<script lang="ts">
	import * as Card from '$ui/card';
	import { capitalizeWords } from '$utils/string';
	let { model, audits } = $props();
</script>

<Card.Root class="w-full">
	<Card.Header>
		<Card.Title class="text-lg">Audit Logs</Card.Title>
		<Card.Description>Session's audit logs.</Card.Description>
	</Card.Header>
	<Card.Content>
		<div class="flex flex-col gap-6">qwe</div>
	</Card.Content>
</Card.Root>
