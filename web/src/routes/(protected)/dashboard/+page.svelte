<script lang="ts">
	import type { PageData } from './$types';
	import * as Card from '$ui/card';
	import { LoaderCircle } from '@lucide/svelte';

	let { data }: { data: PageData } = $props();
	let dashboardData = $derived(data.dashboard);
</script>

<h1 class="text-2xl font-medium capitalize">Dashboard</h1>

<div class="grid grid-cols-2 gap-3 lg:grid-cols-3 xl:grid-cols-4">

	<Card.Root class="@container/card rounded-md py-4 shadow-none">
		<Card.Header class="px-4">
			<Card.Description>Users</Card.Description>
			<Card.Title class="pt-5 text-4xl font-semibold tabular-nums flex">
				{dashboardData.user_count} 
			</Card.Title>
			<Card.Action>
			</Card.Action>
		</Card.Header>
	</Card.Root>

	<Card.Root class="@container/card rounded-md py-4 shadow-none">
		<Card.Header class="px-4">
			<Card.Description>User Groups</Card.Description>
			<Card.Title class="pt-5 text-4xl font-semibold tabular-nums">
				{dashboardData.user_group_count}
			</Card.Title>
			<Card.Action>
			</Card.Action>
		</Card.Header>
	</Card.Root>

	<Card.Root class="@container/card rounded-md py-4 shadow-none">
		<Card.Header class="px-4">
			<Card.Description>Assets</Card.Description>
			<Card.Title class="pt-5 text-4xl font-semibold tabular-nums">
				{dashboardData.asset_count}
			</Card.Title>
			<Card.Action>
			</Card.Action>
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
			<Card.Description>Requests</Card.Description>
			<Card.Title class="pt-5 text-4xl font-semibold tabular-nums">
				{dashboardData.request_count}
			</Card.Title>
			<Card.Action>
			</Card.Action>
		</Card.Header>
	</Card.Root>

	<Card.Root class="@container/card rounded-md py-4 shadow-none">
		<Card.Header class="px-4">
			<Card.Description>Sessions</Card.Description>
			<Card.Title class="pt-5 text-4xl font-semibold tabular-nums">
				{dashboardData.session_count}
			</Card.Title>
			<Card.Action>
			</Card.Action>
		</Card.Header>
	</Card.Root>

	<Card.Root class="@container/card rounded-md py-4 shadow-none">
		<Card.Header class="px-4">
			<Card.Description>Pending Requests</Card.Description>
			<Card.Title class="pt-5 text-4xl font-semibold tabular-nums">
				{dashboardData.pending_request_count}
			</Card.Title>
		</Card.Header>
	</Card.Root>

	<Card.Root class="@container/card rounded-md py-4 shadow-none">
		<Card.Header class="px-4">
			<Card.Description>Scheduled Sessions</Card.Description>
			<Card.Title class="pt-5 text-4xl font-semibold tabular-nums">
				{dashboardData.scheduled_session_count}
			</Card.Title>
			<Card.Action>
			</Card.Action>
		</Card.Header>
	</Card.Root>

	<!-- <Card.Root class="@container/card rounded-md py-4 shadow-none">
		<Card.Header class="px-4">
			<Card.Description>Average Session Duration</Card.Description>
			<Card.Title class="pt-5 text-4xl font-semibold tabular-nums">10:00</Card.Title>
		</Card.Header>
	</Card.Root> -->
</div>
