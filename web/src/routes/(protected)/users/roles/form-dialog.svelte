<script lang="ts">
	import { Input } from '$ui/input';
	import { Textarea } from '$ui/textarea';
	import * as Form from '$ui/form';
	import * as Dialog from '$ui/dialog';
	import { superForm } from 'sveltekit-superforms';
	import { zodClient } from 'sveltekit-superforms/adapters';
	import { RoleSchema } from '$validations/role';
	import { toast } from 'svelte-sonner';
	import { Button } from '$ui/button';
	import type { Role } from '$models/role';
	import Loader from '$components/loader.svelte';

	interface Props {
		isOpen: boolean;
		model: Role;
		data: any;
		onSuccess: (data: Role) => Promise<void>;
	}

	let {
		isOpen = $bindable(false),
		model = $bindable(),
		data = $bindable(),
		onSuccess = async (data: Role) => {}
	}: Props = $props();

	const form = superForm(data, {
		validators: zodClient(RoleSchema),
		delayMs: 100,
		async onUpdate({ form, result }) {
			if (!form.valid) {
				return;
			}
			if (result.type === 'success') {
				toast.success(result.data.message);
				await onSuccess(result.data.model as Role);
				isOpen = false;
			} else if (result.type === 'failure') {
				toast.error(result.data.error);
			}
		}
	});

	const { form: formData, enhance, submitting } = form;

	function handleCancel() {
		form.reset();
		isOpen = false;
	}
</script>

<Dialog.Root bind:open={isOpen}>
	<Dialog.Content
		class="max-h-[90vh] overflow-y-auto sm:max-w-2xl"
		interactOutsideBehavior="ignore"
	>
		{#if $submitting}
			<Loader show={$submitting} />
		{/if}
		<form class="space-y-6" method="POST" action="?/save" use:enhance>
			<input type="hidden" name="id" value={model?.id} />
			<input type="hidden" name="is_default" value={false} />
			<Dialog.Header>
				<Dialog.Title>
					{model?.id ? 'Edit' : 'Add'} Role
				</Dialog.Title>
				<Dialog.Description>
					{model?.id ? 'Edit' : 'Add'} role details.
				</Dialog.Description>
			</Dialog.Header>
			<div class="space-y-6">
				<Form.Field {form} name="name">
					<Form.Control>
						<Form.Label>Name</Form.Label>
						<Input
							type="text"
							name="name"
							bind:value={$formData.name}
							disabled={$submitting}
							data-1p-ignore
						/>
					</Form.Control>
					<Form.FieldErrors />
				</Form.Field>
				<Form.Field {form} name="description">
					<Form.Control>
						<Form.Label>Description</Form.Label>
						<Textarea
							name="description"
							bind:value={$formData.description}
							disabled={$submitting}
							class="min-h-30"
						/>
					</Form.Control>
					<Form.FieldErrors />
				</Form.Field>
			</div>
			<Dialog.Footer>
				<Button variant="outline" onclick={handleCancel}>Cancel</Button>
				<Button variant="default" type="submit">Save</Button>
			</Dialog.Footer>
		</form>
	</Dialog.Content>
</Dialog.Root>
