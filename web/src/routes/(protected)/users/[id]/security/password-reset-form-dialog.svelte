<script lang="ts">
	import { Button } from '$ui/button';
	import * as Dialog from '$ui/dialog';
	import * as Form from '$ui/form';
	import { Input } from '$ui/input';
	import { superForm } from 'sveltekit-superforms';
	import { zod4Client } from 'sveltekit-superforms/adapters';
	import { toast } from 'svelte-sonner';
	import { ResetPasswordSchema } from '$validations/user';
	import Loader from '$components/loader.svelte';

	type Props = {
		data: any;
		resetPasswordIsLoading: boolean;
		isOpen: boolean;
		onSuccess: () => Promise<void>;
	};

	let {
		data,
		resetPasswordIsLoading = $bindable(),
		isOpen = $bindable(false),
		onSuccess = async () => {}
	}: Props = $props();

	const form = superForm(data, {
		id: 'resetPasswordForm',
		validators: zod4Client(ResetPasswordSchema),
		delayMs: 100,
		async onUpdate({ form, result }) {
			if (!form.valid) {
				return;
			}
			if (result.type === 'success') {
				toast.success(result.data.message);
				await onSuccess();
				isOpen = false;
			} else if (result.type === 'failure') {
				toast.error(result.data.error);
			}
		}
	});
	const { form: formData, enhance, submitting } = form;

	$effect(() => {
		resetPasswordIsLoading = $submitting;
	});
</script>

<Dialog.Root bind:open={isOpen}>
	<Dialog.Content class="max-h-[90vh] overflow-y-auto" interactOutsideBehavior="ignore">
		<Loader show={$submitting} />
		<form class="space-y-6" action="?/resetPassword" method="POST" use:enhance>
			<Dialog.Header>
				<Dialog.Title>Reset User Password</Dialog.Title>
				<Dialog.Description>Reset the user's password.</Dialog.Description>
			</Dialog.Header>
			<Form.Field {form} name="newPassword">
				<Form.Control>
					<Form.Label>New Password</Form.Label>
					<Input
						type="password"
						name="newPassword"
						bind:value={$formData.newPassword}
						autocomplete="new-password"
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
						autocomplete="new-password"
					/>
				</Form.Control>
				<Form.FieldErrors />
			</Form.Field>
			<Dialog.Footer>
				<Button
					variant="outline"
					type="button"
					onclick={() => {
						isOpen = false;
						form.reset();
					}}>Cancel</Button
				>
				<Form.Button>Reset Password</Form.Button>
			</Dialog.Footer>
		</form>
	</Dialog.Content>
</Dialog.Root>
