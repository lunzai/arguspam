<script lang="ts">
	import type { UserResource } from '$lib/resources/user.js';
	import type { User } from '$models/user';
	import * as Card from '$ui/card';
	import { Button } from '$ui/button';
	import { Pencil, Trash2, MailCheck, ShieldOff, ShieldCheck, ShieldPlus, ShieldAlert, MoreHorizontal, SquareAsterisk, RotateCcwKey } from '@lucide/svelte';
	import { Separator } from '$ui/separator';
	import * as DL from '$components/description-list';
	import { relativeDateTime, shortDateTime } from '$utils/date';
	import { StatusBadge, RedBadge, GreenBadge, YellowBadge } from '$components/badge';
	import type { ResourceItem } from '$resources/api';
	import type { Org } from '$models/org';
	import { Badge } from '$ui/badge';
	import type { UserGroup } from '$models/user-group';
	import type { Role } from '$models/role';
	import * as DropdownMenu from '$ui/dropdown-menu';
	import { Switch } from '$ui/switch';
	import Loader from '$components/loader.svelte';
	import { toast } from 'svelte-sonner';
	import { slide } from 'svelte/transition';
	import { Input } from '$ui/input';
	import * as InputOTP from '$ui/input-otp';
	import { REGEXP_ONLY_DIGITS } from 'bits-ui';
	import TwoFactorFormDialog from './two-factor-form-dialog.svelte';
	import { invalidate } from '$app/navigation';
	import { enhance } from '$app/forms';

	let { data } = $props();
	const currentUser = $derived(data.data.user as User);
	const modelResource = $derived(data.model as UserResource);
	const modelUser = $derived(modelResource.data.attributes as User);
	const qrCode = $derived(data.qrCode);
	let twoFactorEnabled = $derived(modelUser.two_factor_enabled);
	let twoFactorEnrolled = $derived(twoFactorEnabled && modelUser.two_factor_confirmed_at !== null);
	let resetPasswordIsLoading = $state(false);
	let twoFactorIsLoading = $state(false);
	let showTwoFactorSetup = $state(false);
	let twoFactorCode = $state('');
	const isCurrentUser = $derived(currentUser.id == modelUser.id);

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
</script>

<!-- <h1 class="text-2xl font-medium">{modelTitle} - #{model.id} - {model.name}</h1> -->

<div class="space-y-6">
	<Card.Root class="w-full relative">
		<Loader show={resetPasswordIsLoading} />
		<Card.Header class="">
			<Card.Title class="text-lg">Reset Password</Card.Title>
			<Card.Description>Reset the user's password.</Card.Description>
			<Card.Action>
				<Button variant="outline">
					<RotateCcwKey class="h-4 w-4" />
					Reset User Password
				</Button>
			</Card.Action>
		</Card.Header>
	</Card.Root>

	<Card.Root class="w-full relative">
		<Loader show={twoFactorIsLoading} />
		<Card.Header>
			<Card.Title class="text-lg">Two-Factor Authentication</Card.Title>
			<Card.Description>Enable or disable two-factor authentication.</Card.Description>
			<Card.Action>
				<Switch 
					disabled={twoFactorEnrolled}
					class="data-[state=checked]:bg-green-500" 
					bind:checked={modelUser.two_factor_enabled}
					onCheckedChange={handleTwoFactorChange}
				/>
			</Card.Action>
		</Card.Header>
		{#if twoFactorEnabled}
			<Card.Content>
				<div class="space-y-6">
					{#if !twoFactorEnrolled}
						<blockquote class="border-l-4 border-blue-300 bg-blue-50 text-blue-500 pl-4 py-3 space-y-1">
							<p>User will be required to setup two-factor authentication when they next login.</p>
							<p>To setup two-factor authentication, please click the button below.</p>
						</blockquote>
						{#if showTwoFactorSetup || isCurrentUser}
							<TwoFactorFormDialog
								bind:data={data.twoFactorVerifyForm}
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
								class="hover:bg-green-50 hover:text-green-500 hover:border-green-200 transition-all duration-200">
								<ShieldPlus class="h-4 w-4" />
								Setup User 2FA
							</Button>
						{/if}
					{:else}
						<blockquote class="border-l-4 border-green-300 bg-green-50 text-green-500 pl-4 py-3 space-y-1">
							<p>User has already setup two-factor authentication on {shortDateTime(modelUser.two_factor_confirmed_at ?? '')}.</p>
							<p>To remove two-factor authentication, please click the button below.</p>
						</blockquote>
						<form 
							action="?/removeTwoFactor" 
							method="POST" 
							use:enhance={() => {
								twoFactorIsLoading = true;
								return ({ result, update }) => {
									console.log('result', result);
									if (result.type === 'success') {
										toast.success('Two-factor authentication removed');
									} else {
										toast.warning('Failed to remove two-factor authentication');
									}
									twoFactorIsLoading = false;
									update();
								}
							}}
						>
							<Button type="submit" variant="outline" class="text-destructive hover:bg-red-50 hover:text-red-500 hover:border-red-200 transition-all duration-200">
								<ShieldOff class="h-4 w-4" />
								Remove User 2FA
							</Button>
						</form>
					{/if}
				</div>
			</Card.Content>
		{/if}
	</Card.Root>
</div>