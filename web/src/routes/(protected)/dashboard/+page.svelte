<script lang="ts">
	import type { PageData } from './$types';
	import * as Card from '$ui/card';
	import LineChart from './charts/line-chart.svelte';
	import PieChart from './charts/pie-chart.svelte';

	let { data }: { data: PageData } = $props();
	const dashboardData = $derived(data.dashboard);
</script>

<h1 class="text-2xl font-medium capitalize">Dashboard</h1>

<div class="grid grid-cols-2 gap-4 lg:grid-cols-4 xl:grid-cols-5">
	<Card.Root class="@container/card rounded-md py-4 shadow-none">
		<Card.Header class="px-4">
			<Card.Description>Users</Card.Description>
			<Card.Title class="flex pt-5 text-4xl font-semibold tabular-nums">
				{dashboardData.user_count}
			</Card.Title>
			<Card.Action></Card.Action>
		</Card.Header>
	</Card.Root>

	<Card.Root class="@container/card hidden rounded-md py-4 shadow-none xl:block">
		<Card.Header class="px-4">
			<Card.Description>User Groups</Card.Description>
			<Card.Title class="pt-5 text-4xl font-semibold tabular-nums">
				{dashboardData.user_group_count}
			</Card.Title>
			<Card.Action></Card.Action>
		</Card.Header>
	</Card.Root>

	<Card.Root class="@container/card rounded-md py-4 shadow-none">
		<Card.Header class="px-4">
			<Card.Description>Assets</Card.Description>
			<Card.Title class="pt-5 text-4xl font-semibold tabular-nums">
				{dashboardData.asset_count}
			</Card.Title>
			<Card.Action></Card.Action>
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
			<Card.Action></Card.Action>
		</Card.Header>
	</Card.Root>

	<Card.Root class="@container/card rounded-md py-4 shadow-none">
		<Card.Header class="px-4">
			<Card.Description>Sessions</Card.Description>
			<Card.Title class="pt-5 text-4xl font-semibold tabular-nums">
				{dashboardData.session_count}
			</Card.Title>
			<Card.Action></Card.Action>
		</Card.Header>
	</Card.Root>
</div>

<div class="grid auto-rows-fr grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-4">
	<PieChart id="asset-distribution" data={dashboardData.asset_distribution} title="Asset DBMS" />

	<PieChart
		id="request-scope-distribution"
		data={dashboardData.request_scope_distribution}
		title="Request Scope"
	/>

	<PieChart
		id="request-approver-risk-rating-distribution"
		data={dashboardData.request_approver_risk_rating_distribution}
		title="Request Approver Risk Rating"
	/>

	<PieChart
		id="session-audit-flag-distribution"
		data={dashboardData.session_audit_flag_distribution}
		title="Session Audit Flag"
	/>
</div>

<div class="grid grid-cols-1 gap-5 xl:grid-cols-2">
	<LineChart
		id="request-status"
		data={dashboardData.request_status_count}
		title="Request"
		description="Showing request status for the last"
		yaxisLabel="Number of Requests"
		filename="argus-request-status"
	/>

	<LineChart
		id="session-status"
		data={dashboardData.session_status_count}
		title="Session"
		description="Showing session status for the last"
		yaxisLabel="Number of Sessions"
		filename="argus-session-status"
	/>

	<LineChart
		id="session-flag"
		data={dashboardData.session_flag_count}
		title="Session Flags"
		description="Showing session flags for the last"
		yaxisLabel="Number of Sessions"
		filename="argus-session-flag"
	/>
</div>
