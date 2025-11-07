<script lang="ts">
	import type { ApiAssetResource } from '$resources/asset';
	import type { Asset } from '$models/asset';
	import type { AssetAccountCollection } from '$lib/resources/asset-account';
	import * as Tabs from '$ui/tabs';
	import AccountsTab from './tab/accounts.svelte';
	import AccessTab from './tab/access.svelte';
	import RequestsTab from './tab/requests.svelte';
	import SessionsTab from './tab/sessions.svelte';
	import type { UserCollection } from '$lib/resources/user';
	import type { UserGroupCollection } from '$lib/resources/user-group';
	import { page } from '$app/state';
	import { replaceState } from '$app/navigation';
	import { browser } from '$app/environment';
	import { onMount } from 'svelte';

	let { data } = $props();
	let defaultTab = $state('accounts');
	const modelResource = $derived(data.model as ApiAssetResource);
	const model = $derived(modelResource.data.attributes as Asset);
	const accounts = $derived(
		modelResource.data.relationships?.activeAccounts as AssetAccountCollection
	);
	let approverUserGroups = $derived(
		modelResource.data.relationships?.approverUserGroups as UserGroupCollection
	);
	let requesterUserGroups = $derived(
		modelResource.data.relationships?.requesterUserGroups as UserGroupCollection
	);
	let approverUsers = $derived(modelResource.data.relationships?.approverUsers as UserCollection);
	let requesterUsers = $derived(modelResource.data.relationships?.requesterUsers as UserCollection);
	let allUserGroups = $derived(data.userGroupCollection?.data as UserGroupCollection);
	let allUsers = $derived(data.userCollection?.data as UserCollection);
	const canAddAccessGrant = $derived(data.canAddAccessGrant);
	const canRemoveAccessGrant = $derived(data.canRemoveAccessGrant);
	const canTestConnection = $derived(data.canTestConnection);
	onMount(() => {
		if (page.url.hash !== '') {
			defaultTab = page.url.hash.replace('#', '');
		}
	});

	function handleTabChange(value: string) {
		defaultTab = value;
		if (browser) {
			replaceState(page.url.pathname + '#' + defaultTab, {});
		}
	}
</script>

<Tabs.Root bind:value={defaultTab} onValueChange={handleTabChange} class="gap-6">
	<Tabs.List class="h-auto p-[4px]">
		<Tabs.Trigger value="accounts" class="cursor-pointer px-5 py-1.5">Accounts</Tabs.Trigger>
		<Tabs.Trigger value="requesters" class="cursor-pointer px-5 py-1.5">Requesters</Tabs.Trigger>
		<Tabs.Trigger value="approvers" class="cursor-pointer px-5 py-1.5">Approvers</Tabs.Trigger>
		<!-- <Tabs.Trigger disabled value="requests" class="px-5 py-1.5 hover:cursor-not-allowed"
			>Requests</Tabs.Trigger
		>
		<Tabs.Trigger disabled value="sessions" class="cursor-not-allowed px-5 py-1.5"
			>Sessions</Tabs.Trigger
		> -->
	</Tabs.List>
	<Tabs.Content value="accounts">
		<AccountsTab list={accounts} {canTestConnection} />
	</Tabs.Content>
	<Tabs.Content value="requesters">
		<AccessTab
			bind:currentUserGroups={requesterUserGroups}
			bind:currentUsers={requesterUsers}
			bind:allUserGroups
			bind:allUsers
			role="requester"
			rolePural="requesters"
			{canAddAccessGrant}
			{canRemoveAccessGrant}
		/>
	</Tabs.Content>
	<Tabs.Content value="approvers">
		<AccessTab
			bind:currentUserGroups={approverUserGroups}
			bind:currentUsers={approverUsers}
			bind:allUserGroups
			bind:allUsers
			role="approver"
			rolePural="approvers"
			{canAddAccessGrant}
			{canRemoveAccessGrant}
		/>
	</Tabs.Content>
	<Tabs.Content value="requests">
		<RequestsTab />
	</Tabs.Content>
	<Tabs.Content value="sessions">
		<SessionsTab />
	</Tabs.Content>
</Tabs.Root>
