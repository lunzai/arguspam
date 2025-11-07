<script lang="ts">
	import * as Card from '$ui/card';
	import { Button } from '$ui/button';
	import { Link2, LoaderCircle, ClockFading, Crown, Unplug } from '@lucide/svelte';
	import type { AssetAccountCollection } from '$resources/asset-account';
	import { relativeDateTime, shortDateTime } from '$utils/date';
	import * as Avatar from '$ui/avatar';
	import * as AlertDialog from '$ui/alert-dialog';

	interface Props {
		list: AssetAccountCollection;
		canTestConnection: boolean;
	}

	let { list, canTestConnection }: Props = $props();
	let testConnectionDialogIsOpen = $state(false);
	let testConnectionIsLoading = $state(false);
	let testConnectionIsSuccess = $state(false);

	async function handleTestConnection() {
		testConnectionIsSuccess = false;
		testConnectionIsLoading = true;
		try {
			const response = await fetch('?/testConnection', {
				method: 'POST',
				body: new FormData()
			});
			const result = await response.json();
			if (result.type === 'success') {
				testConnectionIsSuccess = true;
			} else {
				testConnectionIsSuccess = false;
			}
			testConnectionDialogIsOpen = true;
		} catch (error) {
			testConnectionIsSuccess = false;
			testConnectionDialogIsOpen = true;
		} finally {
			testConnectionIsLoading = false;
		}
	}
</script>

<AlertDialog.Root open={testConnectionDialogIsOpen}>
	<AlertDialog.Content>
		<AlertDialog.Header>
			<AlertDialog.Title>Test Connection</AlertDialog.Title>
			<AlertDialog.Description>
				Connection test
				{#if testConnectionIsSuccess}
					is <span class="font-semibold text-green-500">successful</span>
				{:else}
					is <span class="font-semibold text-red-500">failed</span>
				{/if}
			</AlertDialog.Description>
		</AlertDialog.Header>
		<AlertDialog.Footer>
			<AlertDialog.Action onclick={() => (testConnectionDialogIsOpen = false)}
				>Close</AlertDialog.Action
			>
		</AlertDialog.Footer>
	</AlertDialog.Content>
</AlertDialog.Root>

<Card.Root class="w-full">
	<Card.Header>
		<Card.Title class="text-lg">Accounts</Card.Title>
		<Card.Description>Asset's accounts.</Card.Description>
		<Card.Action></Card.Action>
	</Card.Header>
	<Card.Content>
		<div class="flex flex-col gap-6">
			{#each list as { attributes: row }, index (row.id)}
				<div class="flex items-center gap-4 align-middle">
					<Avatar.Root class="size-8 rounded-lg">
						<Avatar.Fallback class="rounded-lg {row.type == 'admin' ? 'text-indigo-500' : ''}">
							{#if row.type == 'admin'}
								<Crown class="size-4" />
							{:else}
								<ClockFading class="size-4" />
							{/if}
						</Avatar.Fallback>
					</Avatar.Root>
					<div class="flex flex-1 flex-col gap-0.5">
						<span class="truncate font-medium">{row.username}</span>
						<span class="text-muted-foreground truncate text-xs">
							{row.type == 'admin' ? 'Administrator Account' : 'Temporary Account'}
							{#if row.type == 'jit'}
								{#if row.expires_at}
									| Expires in {shortDateTime(row.expires_at)}
								{:else}
									| Expired {relativeDateTime(row.expires_at)}
								{/if}
							{/if}
						</span>
					</div>
					<div>
						{#if row.type == 'admin' && canTestConnection}
							<Button
								variant="outline"
								class="transition-all duration-200 hover:cursor-pointer {testConnectionIsSuccess
									? 'border-green-300 bg-green-50 text-green-500 hover:border-green-300 hover:bg-green-100 hover:text-green-500'
									: ''}"
								onclick={handleTestConnection}
								disabled={testConnectionIsLoading}
							>
								{#if testConnectionIsLoading}
									<LoaderCircle class="h-4 w-4 animate-spin" /> Testing Connection...
								{:else}
									{#if testConnectionIsSuccess}
										<Link2 class="h-4 w-4" />
									{:else}
										<Unplug class="h-4 w-4" />
									{/if}
									Test Connection
								{/if}
							</Button>
						{/if}
					</div>
				</div>
			{:else}
				<div class="flex h-full items-center justify-center">
					<p class="text-sm text-gray-500">No users found</p>
				</div>
			{/each}
		</div>
	</Card.Content>
</Card.Root>
