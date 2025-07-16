<script lang="ts">
	import { enhance } from '$app/forms';
	import { toast } from 'svelte-sonner';
	import { Button } from '$ui/button';
	import { ShieldOff } from '@lucide/svelte';
	import * as AlertDialog from '$ui/alert-dialog';

	let { twoFactorIsLoading = $bindable() } = $props();
	let open = $state(false);
</script>

<AlertDialog.Root>
	<AlertDialog.Trigger>
		<Button
			variant="outline"
			class="text-destructive transition-all duration-200 hover:border-red-200 hover:bg-red-50 hover:text-red-500"
		>
			<ShieldOff class="h-4 w-4" />
			Remove User 2FA
		</Button>
	</AlertDialog.Trigger>
	<AlertDialog.Content>
		<AlertDialog.Title>Remove User 2FA</AlertDialog.Title>
		<AlertDialog.Description>
			Are you sure you want to remove two-factor authentication from this user?
		</AlertDialog.Description>
		<AlertDialog.Footer>
			<form
				action="?/removeTwoFactor"
				method="POST"
				use:enhance={() => {
					twoFactorIsLoading = true;
					return ({ result, update }) => {
						if (result.type === 'success') {
							toast.success('Two-factor authentication removed');
						} else {
							toast.warning('Failed to remove two-factor authentication');
						}
						twoFactorIsLoading = false;
						open = false;
						update();
					};
				}}
			>
				<AlertDialog.Cancel type="button">Cancel</AlertDialog.Cancel>
				<AlertDialog.Action type="submit">Remove User 2FA</AlertDialog.Action>
			</form>
		</AlertDialog.Footer>
	</AlertDialog.Content>
</AlertDialog.Root>
