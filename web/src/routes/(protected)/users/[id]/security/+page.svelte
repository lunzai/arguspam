<script lang="ts">
	import type { ApiUserResource } from '$resources/user';
	import type { User } from '$models/user';
	import * as Card from '$ui/card';
	import { Button } from '$ui/button';
	import { RotateCcwKey, ShieldPlus } from '@lucide/svelte';
	import { shortDateTime } from '$utils/date';
	import { Switch } from '$ui/switch';
	import Loader from '$components/loader.svelte';
	import { toast } from 'svelte-sonner';
	import TwoFactorFormDialog from '$components/2fa/two-factor-form-dialog.svelte';
	import { invalidate } from '$app/navigation';
	import TwoFactorDisableDialog from '$components/2fa/two-factor-disable-dialog.svelte';
	import PasswordResetDialog from './password-reset-form-dialog.svelte';

	let { data } = $props();
	const currentUser = $derived(data.authUser as User);
	const modelResource = $derived(data.model as ApiUserResource);
	const modelUser = $derived(modelResource.data.attributes as User);
	const qrCode = $derived(data.qrCode);
	let twoFactorEnabled = $derived(modelUser.two_factor_enabled);
	let twoFactorEnrolled = $derived(twoFactorEnabled && modelUser.two_factor_confirmed_at !== null);
	let resetPasswordIsLoading = $state(false);
	let twoFactorIsLoading = $state(false);
	let showTwoFactorSetup = $state(false);
	let resetPasswordDialogIsOpen = $state(false);
	let isCurrentUser = $derived(currentUser.id == modelUser.id);

	async function handleTwoFactorChange(checked: boolean) {
		try {
			twoFactorIsLoading = true;
			const formData = new FormData();
			formData.append('enabled', checked ? '1' : '0');
			const response = await fetch('?/updateTwoFactor', {
				method: 'POST',
				body: formData
			});
			const result = await response.json();
			if (result.type === 'success') {
				data.model.data.attributes.two_factor_enabled = checked;
				if (checked) {
					toast.success('Two-factor authentication enabled');
				} else {
					toast.warning('Two-factor authentication disabled');
				}
				await invalidate('user:view:security');
			} else {
				toast.error('Failed to update two-factor authentication');
			}
		} catch (error: any) {
			toast.error('Failed to update two-factor authentication');
		} finally {
			twoFactorIsLoading = false;
		}
	}

	async function handleVerifyTwoFactor() {
		twoFactorIsLoading = false;
	}

	async function handleResetPassword() {
		resetPasswordIsLoading = false;
	}
</script>

<!-- <h1 class="text-2xl font-medium">{modelTitle} - #{model.id} - {model.name}</h1> -->

<PasswordResetDialog
	data={data.resetPasswordForm}
	bind:resetPasswordIsLoading
	bind:isOpen={resetPasswordDialogIsOpen}
	onSuccess={handleResetPassword}
/>

<div class="space-y-6">
	<Card.Root class="relative w-full">
		<Loader show={resetPasswordIsLoading} />
		<Card.Header class="">
			<Card.Title class="text-lg">Reset Password</Card.Title>
			<Card.Description>Reset the user's password.</Card.Description>
			<Card.Action>
				<Button
					variant="outline"
					onclick={() => {
						resetPasswordDialogIsOpen = true;
					}}
				>
					<RotateCcwKey class="h-4 w-4" />
					Reset User Password
				</Button>
			</Card.Action>
		</Card.Header>
	</Card.Root>

	<Card.Root class="relative w-full">
		<Loader show={twoFactorIsLoading} />
		<Card.Header>
			<Card.Title class="text-lg">Two-Factor Authentication</Card.Title>
			<Card.Description>Enable or disable two-factor authentication.</Card.Description>
			<Card.Action>
				<Switch
					disabled={twoFactorEnrolled}
					class="data-[state=checked]:bg-green-500"
					checked={modelUser.two_factor_enabled}
					onCheckedChange={handleTwoFactorChange}
				/>
			</Card.Action>
		</Card.Header>
		{#if twoFactorEnabled}
			<Card.Content>
				<div class="space-y-6">
					{#if !twoFactorEnrolled}
						<blockquote
							class="space-y-1 border-l-4 border-blue-300 bg-blue-50 py-3 pl-4 text-blue-500"
						>
							<p>User will be required to setup two-factor authentication when they next login.</p>
							<p>To setup two-factor authentication, please click the button below.</p>
						</blockquote>
						{#if showTwoFactorSetup || isCurrentUser}
							<TwoFactorFormDialog
								data={data.twoFactorVerifyForm}
								{qrCode}
								{isCurrentUser}
								bind:isSubmitting={twoFactorIsLoading}
								bind:showTwoFactorSetup
								onSuccess={handleVerifyTwoFactor}
							/>
						{:else}
							<Button
								onclick={() => {
									showTwoFactorSetup = true;
								}}
								variant="outline"
								class="transition-all duration-200 hover:border-green-200 hover:bg-green-50 hover:text-green-500"
							>
								<ShieldPlus class="h-4 w-4" />
								Setup User 2FA
							</Button>
						{/if}
					{:else}
						<blockquote
							class="space-y-1 border-l-4 border-green-300 bg-green-50 py-3 pl-4 text-green-500"
						>
							<p>
								User has already setup two-factor authentication on {shortDateTime(
									modelUser.two_factor_confirmed_at ?? new Date()
								)}.
							</p>
							<p>To remove two-factor authentication, please click the button below.</p>
						</blockquote>
						<TwoFactorDisableDialog bind:twoFactorIsLoading />
					{/if}
				</div>
			</Card.Content>
		{/if}
	</Card.Root>
</div>
