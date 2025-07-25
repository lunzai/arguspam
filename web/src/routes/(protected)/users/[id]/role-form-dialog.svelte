<script lang="ts">
	import * as Dialog from '$ui/dialog';
	import * as Form from '$ui/form';
	import * as Select from '$ui/select';
	import { UserUpdateRolesSchema } from '$validations/user';
	import type { RoleResource } from '$resources/role';
	import type { User } from '$models/user';
	import { superForm } from 'sveltekit-superforms';
	import { zodClient } from 'sveltekit-superforms/adapters';
	import { toast } from 'svelte-sonner';
	import { Button } from '$ui/button';
	import Loader from '$components/loader.svelte';

	interface Props {
		isOpen: boolean;
		roles: RoleResource[];
		data: any;
		onSuccess: (data: User) => Promise<void>;
	}

	let {
		isOpen = $bindable(false),
		roles = $bindable(),
		data = $bindable(),
		onSuccess = async (data: User) => {}
	}: Props = $props();

	const form = superForm(data, {
		validators: zodClient(UserUpdateRolesSchema),
		delayMs: 100,
		resetForm: false,
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
	let selectedRoles = $derived(
		roles.filter((role) => $formData.roleIds.includes(role.attributes.id.toString()))
	);
</script>

<Dialog.Root bind:open={isOpen}>
	<Dialog.Content>
		<Loader show={$submitting} />
		<Dialog.Header>
			<Dialog.Title>Update Roles</Dialog.Title>
			<Dialog.Description>Update user roles.</Dialog.Description>
		</Dialog.Header>
		<form method="POST" action="?/roles" class="space-y-6" use:enhance>
			<div class="space-y-6">
				<Form.Field {form} name="roleIds">
					<Form.Control>
						<Form.Label>Roles</Form.Label>
						<Select.Root name="roleIds" type="multiple" bind:value={$formData.roleIds}>
							<Select.Trigger class="w-full">
								{#if $formData.roleIds.length > 0}
									{selectedRoles.map((role) => role.attributes.name).join(', ')}
								{:else}
									Select role(s)
								{/if}
							</Select.Trigger>
							<Select.Content>
								{#each roles as role}
									<Select.Item value={role.attributes.id.toString()} label={role.attributes.name} />
								{/each}
							</Select.Content>
						</Select.Root>
					</Form.Control>
					<Form.FieldErrors />
				</Form.Field>
				<Dialog.Footer>
					<Button variant="outline" onclick={handleCancel}>Cancel</Button>
					<Button variant="default" type="submit">Save</Button>
				</Dialog.Footer>
			</div>
		</form>
	</Dialog.Content>
</Dialog.Root>
