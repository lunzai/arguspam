<script lang="ts">
	import { shortDateTimeRange } from '$utils/date';
	import type { ApiSessionResource } from '$resources/session';
	import type { Session } from '$models/session';
	import type { Asset } from '$models/asset';
	import type { Me, User } from '$models/user';
	import type { Request } from '$models/request';
	import type { SessionAuditCollection } from '$lib/resources/session-audit';
	import Sidebar from './sidebar.svelte';
	import Progress from './progress.svelte';
	import TabDetails from './tab/details.svelte';
	import TabAudits from './tab/audits.svelte';
	import { Separator } from '$ui/separator';
	import type { SessionFlagCollection } from '$lib/resources/session-flag';
	import * as Tabs from '$ui/tabs';

	let { data } = $props();
	const permissions = $derived(data.permissions);
	const canViewRequest = $derived(data.canViewRequest);
	const modelResource = $derived(data.model as ApiSessionResource);
	const model = $derived(modelResource.data.attributes as Session);
	const asset = $derived(modelResource.data.relationships?.asset?.attributes as Asset);
	const requester = $derived(modelResource.data.relationships?.requester?.attributes as User);
	const request = $derived(modelResource.data.relationships?.request?.attributes as Request);
	const approver = $derived(modelResource.data.relationships?.approver?.attributes as User);
	const audits = $derived(modelResource.data.relationships?.audits as SessionAuditCollection);
	const flags = $derived(modelResource.data.relationships?.flags as SessionFlagCollection);
	const me = $derived(data.me as Me);
</script>

<div class="flex flex-col space-y-4">
	<h1 class="text-2xl font-medium capitalize">
		Session - #{model.id} - {asset.name} ({shortDateTimeRange(
			model.scheduled_start_datetime,
			model.scheduled_end_datetime
		)})
	</h1>
	<Separator />
	<div class="mt-2 flex flex-col gap-6 lg:flex-row">
		<aside class="flex w-full flex-col gap-6 lg:w-64">
			<Sidebar {model} {asset} {requester} {permissions} {request} {approver} {me} />
			<Progress {model} />
		</aside>
		<Separator orientation="vertical" class="hidden lg:block" />
		<div class="min-w-0 flex-1">
			<div class="w-full">
				<Tabs.Root value="details">
					<Tabs.List>
						<Tabs.Trigger value="details" class="cursor-pointer px-5 py-1.5">Details</Tabs.Trigger>
						<Tabs.Trigger
							disabled={audits?.length === 0}
							value="audits"
							class="px-5 py-1.5 {audits?.length > 0
								? 'cursor-pointer'
								: 'hover:cursor-not-allowed'}">Audits</Tabs.Trigger
						>
					</Tabs.List>
					<Tabs.Content value="details">
						<TabDetails {model} {asset} {requester} {request} {approver} {flags} {canViewRequest} />
					</Tabs.Content>
					<Tabs.Content value="audits">
						<TabAudits {model} {audits} />
					</Tabs.Content>
				</Tabs.Root>
			</div>
		</div>
	</div>
</div>
