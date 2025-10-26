<script lang="ts">
	import * as Card from '$ui/card';
	import { capitalizeWords } from '$utils/string';
	let { model, audits } = $props();
	import * as Item from '$ui/item';
	import { shortDateTime } from '$lib/utils/date';
</script>

<Card.Root class="w-full">
	<Card.Header>
		<Card.Title class="text-lg">Audit Logs</Card.Title>
		<!-- <Card.Description>Session's audit logs.</Card.Description> -->
	</Card.Header>
	<Card.Content>
		{#if audits?.length > 0}
			<Item.Group>
				{#each audits as audit, index (audit.attributes.id)}
					<Item.Root>
						<Item.Content class="gap-2 font-mono">
							<Item.Title class="font-normal">
								{audit.attributes.query}
							</Item.Title>
							<Item.Description>
								{#if audit.attributes.count > 1}
									First: {shortDateTime(audit.attributes.first_timestamp)} • Last: {shortDateTime(
										audit.attributes.last_timestamp
									)} • Count: {audit.attributes.count} • Command Type: {capitalizeWords(
										audit.attributes.command_type
									)}
								{:else}
									Timestamp: {shortDateTime(audit.attributes.first_timestamp)} • Command Type: {capitalizeWords(
										audit.attributes.command_type
									)}
								{/if}
							</Item.Description>
						</Item.Content>
					</Item.Root>
					{#if index !== audits.length - 1}
						<Item.Separator />
					{/if}
				{/each}
			</Item.Group>
		{/if}
	</Card.Content>
</Card.Root>
