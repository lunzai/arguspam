<script lang="ts">
	import * as Card from '$ui/card';
	import { Button } from '$ui/button';
	import { Pencil, Trash2 } from '@lucide/svelte';
	import * as DL from '$components/description-list';
	import { relativeDateTime } from '$utils/date';
	import { StatusBadge } from '$components/badge';
    import type { ApiAssetResource } from '$resources/asset';
	import type { Asset } from '$models/asset';
    import type { AssetAccountCollection } from '$lib/resources/asset-account';
    import * as Tabs from '$ui/tabs';
    import AccountsTab from './tab/accounts.svelte';
    import AccessGrantsTab from './tab/access-grant.svelte';
    import RequestsTab from './tab/requests.svelte';
    import SessionsTab from './tab/sessions.svelte';
    
	let { data } = $props();
	const modelResource = $derived(data.model as ApiAssetResource);
	const model = $derived(modelResource.data.attributes as Asset);
    const accounts = $derived(modelResource.data.relationships?.accounts as AssetAccountCollection);
	const modelName = 'assets';
	const modelTitle = 'Asset';
    
</script>

<h1 class="text-2xl font-medium capitalize">{modelTitle} - #{model.id} - {model.name}</h1>
<Card.Root class="w-full">
	<Card.Header>
		<Card.Title class="text-lg">{modelTitle}</Card.Title>
		<Card.Description>View {modelTitle.toLowerCase()} details.</Card.Description>
		<Card.Action>
			<Button
				variant="outline"
				class="transition-all duration-200 hover:bg-blue-50 hover:text-blue-500"
				href={`/${modelName}/${model.id}/edit`}
			>
				<Pencil class="h-4 w-4" />
				Edit
			</Button>
			<Button
				variant="outline"
				class="text-destructive border-red-200 transition-all duration-200 hover:bg-red-50 hover:text-red-500"
				href={`/${modelName}/${model.id}/delete`}
			>
				<Trash2 class="h-4 w-4" />
				Delete
			</Button>
		</Card.Action>
	</Card.Header>
	<Card.Content>
		<DL.Root divider={null}>
			<DL.Row>
				<DL.Label>ID</DL.Label>
				<DL.Content>{model.id}</DL.Content>
			</DL.Row>
			<DL.Row>
				<DL.Label>Name</DL.Label>
				<DL.Content>{model.name}</DL.Content>
			</DL.Row>
			<DL.Row>
				<DL.Label>Description</DL.Label>
				<DL.Content>{model.description || '-'}</DL.Content>
			</DL.Row>
			<DL.Row>
				<DL.Label>DBMS</DL.Label>
				<DL.Content>{model.dbms.toUpperCase()}</DL.Content>
			</DL.Row>
			<DL.Row>
				<DL.Label>Host</DL.Label>
				<DL.Content>{model.host}</DL.Content>
			</DL.Row>
			<DL.Row>
				<DL.Label>Port</DL.Label>
				<DL.Content>{model.port}</DL.Content>
			</DL.Row>
			<DL.Row>
				<DL.Label>Status</DL.Label>
				<DL.Content>
					<StatusBadge bind:status={model.status} class="text-sm" />
				</DL.Content>
			</DL.Row>
			<DL.Row>
				<DL.Label>Created At</DL.Label>
				<DL.Content>
					{relativeDateTime(model.created_at)}
				</DL.Content>
			</DL.Row>
			<DL.Row>
				<DL.Label>Updated At</DL.Label>
				<DL.Content>
					{relativeDateTime(model.updated_at)}
				</DL.Content>
			</DL.Row>
		</DL.Root>
	</Card.Content>
</Card.Root>

<Tabs.Root value="accounts" class="gap-6">
    <Tabs.List class="p-[4px] h-auto">
        <Tabs.Trigger value="accounts" class="px-5 py-1.5">Accounts</Tabs.Trigger>
        <Tabs.Trigger value="access-grants" class="px-5 py-1.5">Access Grants</Tabs.Trigger>
        <Tabs.Trigger value="requests" class="px-5 py-1.5">Requests</Tabs.Trigger>
        <Tabs.Trigger value="sessions" class="px-5 py-1.5">Sessions</Tabs.Trigger>
    </Tabs.List>
    <Tabs.Content value="accounts">
        <AccountsTab asset={model} list={accounts} />
    </Tabs.Content>
    <Tabs.Content value="access-grants">
        <AccessGrantsTab />
    </Tabs.Content>
    <Tabs.Content value="requests">
        <RequestsTab />
    </Tabs.Content>
    <Tabs.Content value="sessions">
        <SessionsTab />
    </Tabs.Content>
</Tabs.Root>