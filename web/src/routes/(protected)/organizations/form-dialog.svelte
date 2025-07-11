<script lang="ts">
	import { Input } from '$ui/input';
	import * as Select from '$ui/select';
	import { Textarea } from '$ui/textarea';
	import * as Form from '$ui/form';
	import * as Dialog from '$ui/dialog';
	import { superForm } from 'sveltekit-superforms';
	import { zodClient } from 'sveltekit-superforms/adapters';
	import { OrgSchema } from '$validations/org';
	import { toast } from 'svelte-sonner';
	import { capitalizeWords } from '$utils/string';
	import { Button } from '$ui/button';
	import { Loader2 } from '@lucide/svelte';
	import type { Org } from '$models/org';

	interface Props {
		isOpen: boolean;
		model: Org;
		data: any;
		onSuccess: (data: Org) => Promise<void>;
	}

	let {
		isOpen = $bindable(false),
		model = $bindable(),
		data = $bindable(),
		onSuccess = async (data: Org) => {}
	}: Props = $props();

	let isNewRecord = $derived(!model?.id);

	const form = superForm(data, {
		validators: zodClient(OrgSchema),
		delayMs: 100,
		async onUpdate({ form, result }) {
			if (!form.valid) {
				return;
			}
			if (result.type === 'success') {
				toast.success(result.data.message);
				await onSuccess(result.data.model as Org);
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
	<Dialog.Content class="sm:max-w-2xl" interactOutsideBehavior="ignore">
		{#if $submitting}
			<div
				class="absolute inset-0 z-10 flex items-center justify-center rounded-lg bg-gray-50/50 transition-all"
			>
				<Loader2 class="h-8 w-8 animate-spin text-gray-300" />
			</div>
		{/if}
		<form class="space-y-6" method="POST" action="?/save" use:enhance>
			<input type="hidden" name="id" value={model?.id} />
			<Dialog.Header>
				<Dialog.Title>{isNewRecord ? 'Add Organization' : 'Edit Organization'}</Dialog.Title>
				<Dialog.Description>
					{isNewRecord ? 'Add organization details.' : 'Edit organization details.'}
				</Dialog.Description>
			</Dialog.Header>
			<div class="space-y-6">
				<Form.Field {form} name="name">
					<Form.Control>
						<Form.Label>Name</Form.Label>
						<Input type="text" name="name" bind:value={$formData.name} disabled={$submitting} />
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
				<Form.Field {form} name="status">
					<Form.Control>
						<Form.Label>Status</Form.Label>
						<Select.Root
							name="status"
							type="single"
							bind:value={$formData.status}
							disabled={$submitting}
						>
							<Select.Trigger class="w-64">
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
			</div>
			<Dialog.Footer>
				<Button variant="outline" onclick={handleCancel}>Cancel</Button>
				<Button variant="default" type="submit">Save</Button>
			</Dialog.Footer>
		</form>
	</Dialog.Content>
</Dialog.Root>
