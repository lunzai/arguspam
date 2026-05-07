<script lang="ts">
	import * as Card from '$ui/card';
	import * as DL from '$components/description-list';
	import type { ApiPermissionResource } from '$resources/permission';
	import type { Permission } from '$models/permission';
	import { PageTitle } from '$components/page-title';

	let { data } = $props();
	const modelResource = $derived(data.model as ApiPermissionResource);
	const model = $derived(modelResource.data.attributes as Permission);
	const modelName = 'permissions';
	const modelTitle = 'Permission';
</script>

<div class="flex flex-row space-x-2 items-center">  
    <div class="text-sm text-slate-500 uppercase tracking-wider font-semibold">
        PERMISSION #{model.id}
    </div>
</div>
<PageTitle
    title={model.description}
    class="-mt-4"
/>


<Card.Root class="w-full border-0 shadow-xs">
	<Card.Header>
		<Card.Title class="text-xl font-bold tracking-tight">{modelTitle}</Card.Title>
		<Card.Description>View {modelTitle.toLowerCase()} details.</Card.Description>
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
			<!-- <DL.Row>
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
			</DL.Row> -->
		</DL.Root>
	</Card.Content>
</Card.Root>
