<script lang="ts">
	import { Input } from '$ui/input';
	import * as Form from '$ui/form';
	import * as Dialog from '$ui/dialog';
	import { superForm } from 'sveltekit-superforms';
	import { zodClient } from 'sveltekit-superforms/adapters';
	import { toast } from 'svelte-sonner';
	import { Button } from '$ui/button';
	import * as Select from '$ui/select';
	import { capitalizeWords } from '$utils/string';
	import { UserSchema } from '$validations/user';
	import type { User } from '$models/user';
	import Loader from '$components/loader.svelte';

	interface Props {
		isOpen: boolean;
		model: User;
		data: any;
		onSuccess: (data: User) => Promise<void>;
	}

	let {
		isOpen = $bindable(false),
		model = $bindable(),
		data = $bindable(),
		onSuccess = async (data: User) => {}
	}: Props = $props();

	const form = superForm(data, {
		validators: zodClient(UserSchema),
		delayMs: 100,
		async onUpdate({ form, result }) {
			if (!form.valid) {
				return;
			}
			if (result.type === 'success') {
				toast.success(result.data.message);
				await onSuccess(result.data.model as User);
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
	<Dialog.Content class="sm:max-w-2xl" interactOutsideBehavior="ignore">
		{#if $submitting}
			<Loader show={$submitting} />
		{/if}
		<form class="space-y-6" method="POST" action="?/save" use:enhance>
			<input type="hidden" name="id" value={model?.id} />
			<Dialog.Header>
				<Dialog.Title>Update Profile</Dialog.Title>
				<Dialog.Description>Update user profile.</Dialog.Description>
			</Dialog.Header>
			<div class="space-y-6">
				<Form.Field {form} name="email">
					<Form.Control>
						<Form.Label>Email</Form.Label>
						<Input type="email" name="email" bind:value={$formData.email} disabled={$submitting} />
					</Form.Control>
					<Form.FieldErrors />
				</Form.Field>
				<Form.Field {form} name="name">
					<Form.Control>
						<Form.Label>Name</Form.Label>
						<Input type="text" name="name" bind:value={$formData.name} disabled={$submitting} />
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
