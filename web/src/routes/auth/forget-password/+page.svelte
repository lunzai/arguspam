<script lang="ts">
	import { Input } from '$ui/input';
	import * as Form from '$ui/form';
	import { toast } from 'svelte-sonner';
	import { Loader2 } from '@lucide/svelte';
	import { ChangePasswordSchema } from '$validations/user';
	import { superForm } from 'sveltekit-superforms';
	import { zod4Client } from 'sveltekit-superforms/adapters';

	let { data } = $props();

	const form = superForm(data.form, {
		validators: zod4Client(ChangePasswordSchema),
		delayMs: 100,
		resetForm: false,
		onUpdate({ form, result }) {
			if (form.valid && result.type === 'success') {
				toast.success(result.data.message);
			} else if (result.type === 'failure') {
				toast.error(result.data.error);
			}
		}
	});

	const { form: formData, enhance, submitting, delayed } = form;
</script>

<div class="bg-background flex min-h-svh flex-col items-center justify-center gap-6 p-6 md:p-10">
	<div class="w-full max-w-sm">
		<form method="POST" use:enhance class="max-w-xl space-y-6">
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
	</div>
</div>
