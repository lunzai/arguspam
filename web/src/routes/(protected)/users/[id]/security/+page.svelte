<script lang="ts">
	import type { UserResource } from '$lib/resources/user.js';
	import type { User } from '$models/user';
	import * as Card from '$ui/card';
	import { Button } from '$ui/button';
	import { Pencil, Trash2, MailCheck, ShieldOff, ShieldCheck, ShieldPlus, ShieldAlert, MoreHorizontal, SquareAsterisk, RotateCcwKey } from '@lucide/svelte';
	import { Separator } from '$ui/separator';
	import * as DL from '$components/description-list';
	import { relativeDateTime } from '$utils/date';
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

	let { data } = $props();
	const modelResource = $state(data.model as UserResource);
	const model = $derived(modelResource.data.attributes as User);
	let twoFactorEnabled = $derived(model.two_factor_enabled);
	let twoFactorEnrolled = $derived(twoFactorEnabled && model.two_factor_confirmed_at !== null);
	
	// const orgs = $derived(modelResource.data.relationships?.orgs as ResourceItem<Org>[]);
	// const userGroups = $derived(
	// 	modelResource.data.relationships?.userGroups as ResourceItem<UserGroup>[]
	// );
	// const roles = $derived(modelResource.data.relationships?.roles as ResourceItem<Role>[]);
	// const modelName = 'users';
	// const modelTitle = 'User';
	let resetPasswordIsLoading = $state(false);
	let twoFactorIsLoading = $state(false);

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
			} else {
				toast.error('Failed to update two-factor authentication');
			}
		} catch (error: any) {
			toast.error('Failed to update two-factor authentication');
		} finally {
			twoFactorIsLoading = false;
		}
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
					bind:checked={model.two_factor_enabled}
					onCheckedChange={handleTwoFactorChange}
				/>
			</Card.Action>
		</Card.Header>
		{#if twoFactorEnabled && !twoFactorIsLoading}
			<Card.Content>
				<div class="space-y-6">
					{#if !twoFactorEnrolled}
						<blockquote class="border-l-4 border-blue-300 bg-blue-50 text-blue-500 pl-4 py-3 space-y-1">
							<p>User will be required to setup two-factor authentication when they next login.</p>
							<p>To setup two-factor authentication, please click the button below.</p>
						</blockquote>
						<Button variant="outline" class="hover:bg-green-50 hover:text-green-500 hover:border-green-200 transition-all duration-200">
							<ShieldPlus class="h-4 w-4" />
							Setup User 2FA
						</Button>
					{:else}
						<Button variant="outline" class="text-destructive">
							<ShieldOff class="h-4 w-4" />
							Remove User 2FA
						</Button>
					{/if}
				</div>


				<!-- {#if twoFactorEnrolled}
					{#if model.two_factor_confirmed_at}
						<Button variant="outline" class="text-destructive">
							<ShieldOff class="h-4 w-4" />
							Remove User 2FA
						</Button>
					{:else}
						<blockquote class="text-sm text-muted-foreground">
							User will be required to setup two-factor authentication when they next login.
						</blockquote>
						<Button variant="outline" class="hover:bg-green-50 hover:text-green-500 hover:border-green-200 transition-all duration-200">
							<ShieldCheck class="h-4 w-4" />
							Setup User 2FA
						</Button>
					{/if}
				{:else}
					<Button variant="outline" class="hover:bg-green-50 hover:text-green-500 hover:border-green-200 transition-all duration-200">
						<ShieldPlus class="h-4 w-4" />
						Enable User 2FA
					</Button>
				{/if} -->
			</Card.Content>
		{/if}
	</Card.Root>
</div>