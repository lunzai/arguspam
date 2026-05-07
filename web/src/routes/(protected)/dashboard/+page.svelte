<script lang="ts">
	import type { PageData } from './$types';
	import * as Card from '$ui/card';
	import LineChart from './charts/line-chart.svelte';
	import PieChart from './charts/pie-chart.svelte';

	let { data }: { data: PageData } = $props();
	const dashboardData = $derived(data.dashboard);
</script>

<!-- <h1 class="text-2xl font-medium capitalize">Dashboard</h1> -->

<div class="grid grid-cols-2 gap-4 lg:grid-cols-4 xl:grid-cols-5">
	<Card.Root class="@container/card rounded-xl py-4 border-0 shadow-xs">
		<Card.Header class="px-4">
			<Card.Description class="text-sm font-medium text-slate-500">Users</Card.Description>
			<Card.Title class="flex pt-2.5 tabular-nums text-3xl font-black tracking-tight text-slate-950">
				{dashboardData.user_count}
			</Card.Title>
			<Card.Action></Card.Action>
		</Card.Header>
	</Card.Root>

	<Card.Root class="@container/card hidden rounded-xl py-4 border-0 shadow-xs xl:block">
		<Card.Header class="px-4">
			<Card.Description class="text-sm font-medium text-slate-500">User Groups</Card.Description>
			<Card.Title class="flex pt-2.5 tabular-nums text-3xl font-black tracking-tight text-slate-950">
				{dashboardData.user_group_count}
			</Card.Title>
			<Card.Action></Card.Action>
		</Card.Header>
	</Card.Root>

	<Card.Root class="@container/card rounded-xl py-4 border-0 shadow-xs">
		<Card.Header class="px-4">
			<Card.Description class="text-sm font-medium text-slate-500">Assets</Card.Description>
			<Card.Title class="flex pt-2.5 tabular-nums text-3xl font-black tracking-tight text-slate-950">
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

	<Card.Root class="@container/card rounded-xl py-4 border-0 shadow-xs">
		<Card.Header class="px-4">
			<Card.Description class="text-sm font-medium text-slate-500">Requests</Card.Description>
			<Card.Title class="flex pt-2.5 tabular-nums text-3xl font-black tracking-tight text-slate-950">
				{dashboardData.request_count}
			</Card.Title>
			<Card.Action></Card.Action>
		</Card.Header>
	</Card.Root>

	<Card.Root class="@container/card rounded-xl py-4 border-0 shadow-xs">
		<Card.Header class="px-4">
			<Card.Description class="text-sm font-medium text-slate-500">Sessions</Card.Description>
			<Card.Title class="flex pt-2.5 tabular-nums text-3xl font-black tracking-tight text-slate-950">
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
