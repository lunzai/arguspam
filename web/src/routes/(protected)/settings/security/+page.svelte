<script lang="ts">
	import ChangePasswordForm from '$components/user/change-password-form.svelte';
	import * as Card from '$ui/card';
	import TwoFactorFormDialog from '$components/2fa/two-factor-form-dialog.svelte';
	import TwoFactorDisableDialog from '$components/2fa/two-factor-disable-dialog.svelte';
	import { Switch } from '$ui/switch';
	import { shortDateTime } from '$utils/date';
	import { ShieldPlus } from '@lucide/svelte';
	import Loader from '$components/loader.svelte';
	import { invalidate } from '$app/navigation';
	import { toast } from 'svelte-sonner';
	import { Button } from '$ui/button';
	import { onMount } from 'svelte';
	import { page } from '$app/state';

	let { data } = $props();
	let twoFactorIsLoading = $state(false);
	let twoFactorEnabled = $derived(data.user.two_factor_enabled);
	let twoFactorEnrolled = $derived(twoFactorEnabled && data.user.two_factor_confirmed_at !== null);
	let showTwoFactorSetup = $state(false);

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
				data.user.two_factor_enabled = checked;
				if (checked) {
					toast.success('Two-factor authentication enabled');
				} else {
					toast.warning('Two-factor authentication disabled');
				}
				await invalidate('settings:security');
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

	onMount(() => {
		if (data.user.two_factor_enabled && !data.user.two_factor_confirmed_at) {
			toast.warning('Please verify your two-factor authentication to continue.', {
				duration: Number.POSITIVE_INFINITY
			});
		}
		if (page.url.hash !== '') {
			window.scrollTo({
				top: document.getElementById(page.url.hash.slice(1))?.offsetTop,
				behavior: 'smooth'
			});
		}
	});
</script>

<div class="space-y-6">
	<Card.Root class="w-full relative">
		<Card.Header class="">
			<Card.Title class="text-lg">Password</Card.Title>
			<Card.Description>Change your password.</Card.Description>
			<Card.Action></Card.Action>
		</Card.Header>
		<Card.Content>
			<ChangePasswordForm data={data.changePasswordForm} />
		</Card.Content>
	</Card.Root>

	<Card.Root class="relative w-full" id="2fa">
		<Loader show={twoFactorIsLoading} />
		<Card.Header>
			<Card.Title class="text-lg">Two-Factor Authentication</Card.Title>
			<Card.Description>Enable or disable two-factor authentication.</Card.Description>
			<Card.Action>
				<Switch
					disabled={twoFactorEnrolled}
					class="data-[state=checked]:bg-green-500"
					checked={data.user.two_factor_enabled}
					onCheckedChange={handleTwoFactorChange}
				/>
			</Card.Action>
		</Card.Header>
		{#if twoFactorEnabled}
			<Card.Content>
				<div class="space-y-6">
					{#if !twoFactorEnrolled}
						{#if showTwoFactorSetup}
							<TwoFactorFormDialog
								data={data.twoFactorVerifyForm}
								qrCode={data.qrCode}
								isCurrentUser={true}
								bind:isSubmitting={twoFactorIsLoading}
								bind:showTwoFactorSetup
								onSuccess={handleVerifyTwoFactor}
							/>
						{/if}
					{:else}
						<blockquote
							class="space-y-1 border-l-4 border-green-300 bg-green-50 py-3 pl-4 text-green-500"
						>
							<p>
								You have already setup two-factor authentication on {shortDateTime(
									data.user.two_factor_confirmed_at ?? ''
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
