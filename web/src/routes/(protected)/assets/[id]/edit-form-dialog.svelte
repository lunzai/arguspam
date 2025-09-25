<script lang="ts">
	import { Input } from '$ui/input';
	import * as Select from '$ui/select';
	import { Textarea } from '$ui/textarea';
	import * as Form from '$ui/form';
	import * as Dialog from '$ui/dialog';
	import { superForm } from 'sveltekit-superforms';
	import { zodClient } from 'sveltekit-superforms/adapters';
	import { toast } from 'svelte-sonner';
	import { capitalizeWords } from '$utils/string';
	import { Button } from '$ui/button';
	import type { Asset } from '$models/asset';
	import Loader from '$components/loader.svelte';
	import { AssetUpdateSchema } from '$lib/validations/asset';

	interface Props {
		isOpen: boolean;
		model: Asset;
		data: any;
		onSuccess: (data: Asset) => Promise<void>;
	}

	let {
		isOpen = $bindable(false),
		model = $bindable(),
		data = $bindable(),
		onSuccess = async (data: Asset) => {}
	}: Props = $props();

	const form = superForm(data, {
		validators: zodClient(AssetUpdateSchema),
		delayMs: 100,
		async onUpdate({ form, result }) {
			if (!form.valid) {
				return;
			}
			if (result.type === 'success') {
				toast.success(result.data.message);
				await onSuccess(result.data.model as Asset);
				isOpen = false;
			} else if (result.type === 'failure') {
				toast.error(result.data.error);
			}
		}
	});

	const { form: formData, enhance, submitting } = form;

	function handleCancel() {
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
			<input type="hidden" name="id" value={model.id} />
			<input type="hidden" name="org_id" value={$formData.org_id} />
			<Dialog.Header>
				<Dialog.Title>Edit Asset</Dialog.Title>
				<Dialog.Description>Edit asset details.</Dialog.Description>
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
				<Form.Field {form} name="status">
					<Form.Control>
						<Form.Label>Status</Form.Label>
						<Select.Root
							name="status"
							type="single"
							bind:value={$formData.status}
							disabled={$submitting}
						>
							<Select.Trigger class="max-w-56">
								{$formData.status ? capitalizeWords($formData.status) : 'Select status'}
							</Select.Trigger>
							<Select.Content>
								<Select.Item value="active" label="Active" />
								<Select.Item value="inactive" label="Inactive" />
							</Select.Content>
						</Select.Root>
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
							class="min-h-18"
						/>
					</Form.Control>
					<Form.FieldErrors />
				</Form.Field>
			</div>
			<Dialog.Footer>
				<Button variant="outline" onclick={handleCancel}>Cancel</Button>
				<Button variant="default" type="submit">Update</Button>
			</Dialog.Footer>
		</form>
	</Dialog.Content>
</Dialog.Root>
