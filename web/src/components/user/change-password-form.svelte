<script lang="ts">
	import { Input } from '$ui/input';
	import * as Form from '$ui/form';
	import { toast } from 'svelte-sonner';
	import { Loader2 } from '@lucide/svelte';
	import { ChangePasswordSchema } from '$validations/user';
	import { superForm } from 'sveltekit-superforms';
	import { zodClient } from 'sveltekit-superforms/adapters';
	import SuperDebug from 'sveltekit-superforms';

	let { data } = $props();

	const form = superForm(data, {
		validators: zodClient(ChangePasswordSchema),
		delayMs: 100,
		resetForm: true,
		onUpdate({ form, result }) {
			if (!form.valid) {
				return;
			}
			if (result.type === 'success') {
				toast.success(result.data.message);
			} else if (result.type === 'failure') {
				toast.error(result.data.error);
			}
		}
	});

	const { form: formData, enhance, submitting, delayed } = form;
</script>

<form method="POST" action="?/changePassword" use:enhance class="max-w-xs space-y-6">
	<Form.Field {form} name="currentPassword">
		<Form.Control>
			<Form.Label>Current Password</Form.Label>
			<Input
				type="password"
				name="currentPassword"
				bind:value={$formData.currentPassword}
				disabled={$submitting}
			/>
		</Form.Control>
		<Form.FieldErrors />
	</Form.Field>
	<Form.Field {form} name="newPassword">
		<Form.Control>
			<Form.Label>New Password</Form.Label>
			<Input
				type="password"
				name="newPassword"
				bind:value={$formData.newPassword}
				disabled={$submitting}
			/>
		</Form.Control>
		<Form.FieldErrors />
	</Form.Field>
	<Form.Field {form} name="confirmNewPassword">
		<Form.Control>
			<Form.Label>Confirm New Password</Form.Label>
			<Input
				type="password"
				name="confirmNewPassword"
				bind:value={$formData.confirmNewPassword}
				disabled={$submitting}
			/>
		</Form.Control>
		<Form.FieldErrors />
	</Form.Field>
	<Form.Button type="submit" disabled={$submitting}>
		{#if $delayed}
			<Loader2 className="size-4 animate-spin" />
		{/if}
		Change Password
	</Form.Button>
</form>
