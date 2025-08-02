<script lang="ts">
	import type { ApiAssetResource } from '$resources/asset';
	import type { Asset } from '$models/asset';
	import type { AssetAccountCollection } from '$lib/resources/asset-account';
	import * as Tabs from '$ui/tabs';
	import AccountsTab from './tab/accounts.svelte';
	import RequestersTab from './tab/requester.svelte';
	import ApproversTab from './tab/approver.svelte';
	import RequestsTab from './tab/requests.svelte';
	import SessionsTab from './tab/sessions.svelte';
	import type { UserCollection } from '$lib/resources/user';
	import type { UserGroupCollection } from '$lib/resources/user-group';

	let { data } = $props();
	const modelResource = $state(data.model as ApiAssetResource);
	const model = $state(modelResource.data.attributes as Asset);
	const accounts = $state(modelResource.data.relationships?.accounts as AssetAccountCollection);
	const approverUserGroups = $state(
		modelResource.data.relationships?.approverUserGroups as UserGroupCollection
	);
	const requesterUserGroups = $state(
		modelResource.data.relationships?.requesterUserGroups as UserGroupCollection
	);
	const approverUsers = $state(modelResource.data.relationships?.approverUsers as UserCollection);
	const requesterUsers = $state(modelResource.data.relationships?.requesterUsers as UserCollection);
</script>

<Tabs.Root value="accounts" class="gap-6">
	<Tabs.List class="h-auto p-[4px]">
		<Tabs.Trigger value="accounts" class="cursor-pointer px-5 py-1.5">Accounts</Tabs.Trigger>
		<Tabs.Trigger value="requesters" class="cursor-pointer px-5 py-1.5">Requesters</Tabs.Trigger>
		<Tabs.Trigger value="approvers" class="cursor-pointer px-5 py-1.5">Approvers</Tabs.Trigger>
		<Tabs.Trigger disabled value="requests" class="px-5 py-1.5 hover:cursor-not-allowed"
			>Requests</Tabs.Trigger
		>
		<Tabs.Trigger disabled value="sessions" class="cursor-not-allowed px-5 py-1.5"
			>Sessions</Tabs.Trigger
		>
	</Tabs.List>
	<Tabs.Content value="accounts">
		<AccountsTab asset={model} list={accounts} />
	</Tabs.Content>
	<Tabs.Content value="requesters">
		<RequestersTab {requesterUserGroups} {requesterUsers} />
	</Tabs.Content>
	<Tabs.Content value="approvers">
		<ApproversTab {approverUserGroups} {approverUsers} />
	</Tabs.Content>
	<Tabs.Content value="requests">
		<RequestsTab />
	</Tabs.Content>
	<Tabs.Content value="sessions">
		<SessionsTab />
	</Tabs.Content>
</Tabs.Root>
