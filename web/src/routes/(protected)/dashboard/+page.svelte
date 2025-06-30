<script lang="ts">
	import type { PageData } from './$types';
	import * as Card from '$ui/card';
	import { Badge } from '$ui/badge';
	import { TrendingDown } from '@lucide/svelte';

	let { data }: { data: PageData } = $props();
</script>

<h1 class="text-2xl font-medium capitalize">Dashboard</h1>

<div class="grid grid-cols-2 lg:grid-cols-3 gap-3">

	<Card.Root class="@container/card rounded-md py-4 shadow-none">
		<Card.Header class="px-4">
			<Card.Description>
				Total Assets
			</Card.Description>
			<Card.Title class="text-4xl font-semibold tabular-nums pt-5">
				12
			</Card.Title>
			<!-- <Card.Action>
				<Badge variant="outline" class="text-sm">
				  <TrendingDown />
				  +12.5%
				</Badge>
			</Card.Action> -->
		</Card.Header>
		<!-- <Card.Content class="px-4">
			Content
		</Card.Content> -->
	</Card.Root>

	<Card.Root class="@container/card rounded-md py-4 shadow-none">
		<Card.Header class="px-4">
			<Card.Description>
				Total Requests
			</Card.Description>
			<Card.Title class="text-4xl font-semibold tabular-nums pt-5">
				50
			</Card.Title>
		</Card.Header>
	</Card.Root>

	<Card.Root class="@container/card rounded-md py-4 shadow-none">
		<Card.Header class="px-4">
			<Card.Description>
				Total Sessions
			</Card.Description>
			<Card.Title class="text-4xl font-semibold tabular-nums pt-5">
				30
			</Card.Title>
		</Card.Header>
	</Card.Root>

	<Card.Root class="@container/card rounded-md py-4 shadow-none">
		<Card.Header class="px-4">
			<Card.Description>
				Pending Requests
			</Card.Description>
			<Card.Title class="text-4xl font-semibold tabular-nums pt-5">
				5
			</Card.Title>
		</Card.Header>
	</Card.Root>

	<Card.Root class="@container/card rounded-md py-4 shadow-none">
		<Card.Header class="px-4">
			<Card.Description>
				Upcoming Sessions
			</Card.Description>
			<Card.Title class="text-4xl font-semibold tabular-nums pt-5">
				5
			</Card.Title>
		</Card.Header>
	</Card.Root>

	<Card.Root class="@container/card rounded-md py-4 shadow-none">
		<Card.Header class="px-4">
			<Card.Description>
				Average Session Duration
			</Card.Description>
			<Card.Title class="text-4xl font-semibold tabular-nums pt-5">
				10:00
			</Card.Title>
		</Card.Header>
	</Card.Root>

</div>
