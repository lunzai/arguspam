<script lang="ts">
	import * as Card from '$ui/card';
	import { Button } from '$ui/button';
	import { Pencil, Trash2 } from '@lucide/svelte';
	import * as DL from '$components/description-list';
	import { relativeDateTime } from '$utils/date';
    import type { ApiSessionResource } from '$resources/session';
	import type { Session } from '$models/session';

	let { data } = $props();
	const modelResource = $derived(data.model as ApiSessionResource);
	const model = $derived(modelResource.data.attributes as Session);
	const modelName = 'sessions';
	const modelTitle = 'Session';
</script>

<h1 class="text-2xl font-medium capitalize">{modelTitle} - #{model.id} - {model.id}</h1>
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
				<DL.Content>{model.id}</DL.Content>
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
