<script lang="ts">
	import * as Card from '$ui/card';
	import * as DL from '$components/description-list';
	import { StatusBadge } from '$components/badge';
	import { AssetToolTips } from '$components/tooltip';
	import { relativeDateTime, shortDateTime, shortDateTimeRange } from '$utils/date';
	import { Separator } from '$ui/separator';
	import { Button } from '$ui/button';
	import {
		Play,
		ClipboardX,
		MonitorX,
		ChevronDown,
		Eye,
		EyeClosed,
		KeyRound
	} from '@lucide/svelte';
	import type { Session } from '$models/session';
	import type { SessionPermission } from '$resources/session';
	import type { User } from '$models/user';
	import type { Asset } from '$models/asset';
	import type { Request } from '$models/request';
	import { slide } from 'svelte/transition';
	import * as AlertDialog from '$ui/alert-dialog';
	import { enhance } from '$app/forms';
	import { toast } from 'svelte-sonner';
	import Loader from '$components/loader.svelte';
	import { invalidate } from '$app/navigation';

	interface Props {
		model: Session;
		permissions: SessionPermission;
		requester: User;
		asset: Asset;
		request: Request;
		approver: User;
		user: User;
	}

	let { model, permissions, requester, asset, request, approver, user }: Props = $props();

	const canStart = $derived(permissions.canStart && model.status == 'scheduled');
	const canCancel = $derived(permissions.canCancel && model.status == 'scheduled');
	const canEnd = $derived(permissions.canEnd && model.status == 'started');
	const canTerminate = $derived(
		permissions.canTerminate && model.status == 'started' && user.id != model.requester_id
	);
	const canRetrieveSecret = $derived(permissions.canRetrieveSecret && model.status == 'started' && user.id == model.requester_id);
	const showActions = $derived(canStart || canCancel || canEnd || canTerminate);

	// const canStart = true;
	// const canCancel = true;
	// const canEnd = true;
	// const canTerminate = true;
	// const showActions = $derived(canStart || canCancel || canEnd || canTerminate);

	let startDialogIsOpen = $state(false);
	let startDialogIsLoading = $state(false);
	let cancelDialogIsOpen = $state(false);
	let cancelDialogIsLoading = $state(false);
	let endDialogIsOpen = $state(false);
	let endDialogIsLoading = $state(false);
	let terminateDialogIsOpen = $state(false);
	let terminateDialogIsLoading = $state(false);
	let retrieveSecretDialogIsOpen = $state(false);
	let retrieveSecretDialogIsLoading = $state(false);
	let secret = $state({
		username: '',
		password: ''
	});
	let showSecretPassword = $state(false);
	let showMore = $state(false);

	async function retrieveSecret() {
		retrieveSecretDialogIsLoading = true;
		try {
			const response = await fetch(`/api/secret/${model.id}`, { method: 'POST' });
			const data = await response.json();
			if (data.success) {
				secret = {
					username: data.data.username,
					password: data.data.password
				};
			} else {
				toast.error(data.error);
			}
		} catch (error) {
			toast.error('Failed to retrieve secret');
		} finally {
			retrieveSecretDialogIsLoading = false;
		}
	}
</script>

<AlertDialog.Root
	bind:open={retrieveSecretDialogIsOpen}
	onOpenChange={(open) => {
		if (!open) {
			secret = {
				username: '',
				password: ''
			};
		}
	}}
>
	<AlertDialog.Content>
		<AlertDialog.Header>
			<AlertDialog.Title>Your JIT Credentials</AlertDialog.Title>
		</AlertDialog.Header>
		<AlertDialog.Description>
			<DL.Root divider={null} dlClass="">
				<DL.Row>
					<DL.Label>Host</DL.Label>
					<DL.Content>
						<span class="font-mono">{asset.host}</span>
					</DL.Content>
				</DL.Row>
				<DL.Row>
					<DL.Label>Port</DL.Label>
					<DL.Content>
						<span class="font-mono">{asset.port}</span>
					</DL.Content>
				</DL.Row>
				<DL.Row>
					<DL.Label>Username</DL.Label>
					<DL.Content>
						<span class="font-mono">{secret?.username || '-'}</span>
					</DL.Content>
				</DL.Row>
				<DL.Row>
					<DL.Label>Password</DL.Label>
					<DL.Content>
						<div class="flex items-center gap-4">
							<span class="font-mono"
								>{showSecretPassword
									? secret?.password
									: '•'.repeat(secret?.password?.length || 10)}</span
							>
							<Button
								variant="ghost"
								size="icon"
								class="size-4"
								onclick={() => (showSecretPassword = !showSecretPassword)}
							>
								{#if showSecretPassword}
									<EyeClosed class="h-2 w-2" />
								{:else}
									<Eye class="h-2 w-2" />
								{/if}
							</Button>
						</div>
					</DL.Content>
				</DL.Row>
			</DL.Root>
		</AlertDialog.Description>
		<AlertDialog.Footer>
			<AlertDialog.Cancel disabled={retrieveSecretDialogIsLoading} type="reset"
				>Close</AlertDialog.Cancel
			>
		</AlertDialog.Footer>
		<Loader show={retrieveSecretDialogIsLoading} />
	</AlertDialog.Content>
</AlertDialog.Root>

<AlertDialog.Root bind:open={cancelDialogIsOpen}>
	<AlertDialog.Content>
		<AlertDialog.Header>
			<AlertDialog.Title>Are you sure?</AlertDialog.Title>
		</AlertDialog.Header>
		<AlertDialog.Description>
			<p>
				This action cannot be undone. Cancelling this session will prevent access to the asset and
				no JIT credentials will be created.
			</p>
			<p class="mt-3 mb-3">You'll need to submit a new request if you need access again.</p>
		</AlertDialog.Description>
		<AlertDialog.Footer>
			<form
				method="POST"
				action="?/cancel"
				use:enhance={({ cancel, formData }) => {
					cancelDialogIsLoading = true;
					return async ({ result, update }) => {
						if (result.type === 'success') {
							toast.success('Session cancelled successfully');
							invalidate('sessions:view');
							cancel();
						} else {
							toast.error('Failed to cancel session');
						}
						cancelDialogIsLoading = false;
						cancelDialogIsOpen = false;
					};
				}}
			>
				<AlertDialog.Cancel disabled={cancelDialogIsLoading} type="reset">Back</AlertDialog.Cancel>
				<AlertDialog.Action disabled={cancelDialogIsLoading} type="submit"
					>Confirm</AlertDialog.Action
				>
			</form>
		</AlertDialog.Footer>
		<Loader show={cancelDialogIsLoading} />
	</AlertDialog.Content>
</AlertDialog.Root>

<AlertDialog.Root bind:open={terminateDialogIsOpen}>
	<AlertDialog.Content>
		<AlertDialog.Header>
			<AlertDialog.Title>Terminate Session</AlertDialog.Title>
		</AlertDialog.Header>
		<AlertDialog.Description>
			<p>
				This action will immediately revoke access to <strong>{asset.name}</strong> and terminate the
				active session.
			</p>
			<p class="mt-3 mb-3">
				The requester's JIT credentials will be revoked and all session activities will be recorded
				for audit review.
			</p>
		</AlertDialog.Description>
		<AlertDialog.Footer>
			<form
				method="POST"
				action="?/terminate"
				use:enhance={({ cancel }) => {
					terminateDialogIsLoading = true;
					return async ({ result, update }) => {
						if (result.type === 'success') {
							toast.success('Session terminated successfully');
							invalidate('sessions:view');
							cancel();
						} else {
							toast.error('Failed to terminate session');
						}
						terminateDialogIsLoading = false;
						terminateDialogIsOpen = false;
					};
				}}
			>
				<AlertDialog.Cancel disabled={terminateDialogIsLoading} type="reset"
					>Back</AlertDialog.Cancel
				>
				<AlertDialog.Action disabled={terminateDialogIsLoading} type="submit"
					>Terminate Session</AlertDialog.Action
				>
			</form>
		</AlertDialog.Footer>
		<Loader show={terminateDialogIsLoading} />
	</AlertDialog.Content>
</AlertDialog.Root>

<AlertDialog.Root bind:open={endDialogIsOpen}>
	<AlertDialog.Content>
		<AlertDialog.Header>
			<AlertDialog.Title>End Session</AlertDialog.Title>
		</AlertDialog.Header>
		<AlertDialog.Description>
			<p>
				This action will end your active session for <strong>{asset.name}</strong> and revoke your JIT
				credentials.
			</p>
			<p class="mt-3 mb-3">
				Your session activities will be automatically reviewed and you'll be notified of the results
				once the AI analysis is complete.
			</p>
		</AlertDialog.Description>
		<AlertDialog.Footer>
			<form
				method="POST"
				action="?/end"
				use:enhance={({ cancel }) => {
					endDialogIsLoading = true;
					return async ({ result, update }) => {
						if (result.type === 'success') {
							toast.success('Session ended successfully');
							invalidate('sessions:view');
							cancel();
						} else {
							toast.error('Failed to end session');
						}
						endDialogIsLoading = false;
						endDialogIsOpen = false;
					};
				}}
			>
				<AlertDialog.Cancel disabled={endDialogIsLoading} type="reset">Back</AlertDialog.Cancel>
				<AlertDialog.Action disabled={endDialogIsLoading} type="submit"
					>End Session</AlertDialog.Action
				>
			</form>
		</AlertDialog.Footer>
		<Loader show={endDialogIsLoading} />
	</AlertDialog.Content>
</AlertDialog.Root>

<AlertDialog.Root bind:open={startDialogIsOpen}>
	<AlertDialog.Content>
		<AlertDialog.Header>
			<AlertDialog.Title>Start Session</AlertDialog.Title>
		</AlertDialog.Header>
		<AlertDialog.Description>
			<p>
				This action will start your session for <strong>{asset.name}</strong>. JIT credentials will
				be automatically created for you.
			</p>
			<p class="mt-3">
				During your session, follow all security policies, only access data necessary for your
				stated purpose, and complete your work within the approved timeframe.
			</p>
			<p class="mt-3 mb-3">
				🔴 <strong>IMPORTANT:</strong> End your session as soon as you're done. All activities are recorded
				and audited for compliance.
			</p>
		</AlertDialog.Description>
		<AlertDialog.Footer>
			<form
				method="POST"
				action="?/start"
				use:enhance={({ cancel }) => {
					startDialogIsLoading = true;
					return async ({ result, update }) => {
						if (result.type === 'success') {
							toast.success('Session started successfully');
							invalidate('sessions:view');
							cancel();
						} else {
							toast.error('Failed to start session');
						}
						startDialogIsLoading = false;
						startDialogIsOpen = false;
					};
				}}
			>
				<AlertDialog.Cancel disabled={startDialogIsLoading} type="reset">Back</AlertDialog.Cancel>
				<AlertDialog.Action disabled={startDialogIsLoading} type="submit"
					>Start Session</AlertDialog.Action
				>
			</form>
		</AlertDialog.Footer>
		<Loader show={startDialogIsLoading} />
	</AlertDialog.Content>
</AlertDialog.Root>

<Card.Root class="w-full">
	<!-- <Card.Header>
        <Card.Title class="text-lg">#{model.id} - {asset.name}</Card.Title>
    </Card.Header> -->
	<Card.Content class="space-y-4">
		<DL.Root divider={null} dlClass="space-y-4">
			<DL.Row orientation="vertical">
				<DL.Label>Asset</DL.Label>
				<DL.Content>
					<AssetToolTips {asset} />
				</DL.Content>
			</DL.Row>
			<DL.Row orientation="vertical">
				<DL.Label>Status</DL.Label>
				<DL.Content>
					<StatusBadge bind:status={model.status} class="text-sm" />
				</DL.Content>
			</DL.Row>
			<DL.Row orientation="vertical">
				<DL.Label>Requester</DL.Label>
				<DL.Content>{requester.name} ({requester.email})</DL.Content>
			</DL.Row>
		</DL.Root>
		{#if showMore}
			<div transition:slide={{ duration: 200 }}>
				<DL.Root divider={null} dlClass="space-y-4">
					<DL.Row orientation="vertical">
						<DL.Label>Approver</DL.Label>
						<DL.Content>{approver.name} ({approver.email})</DL.Content>
					</DL.Row>
					<DL.Row orientation="vertical">
						<DL.Label>Approved At</DL.Label>
						<DL.Content>
							{shortDateTime(request.approved_at)}
						</DL.Content>
					</DL.Row>
					{#if model.started_at}
						<DL.Row orientation="vertical">
							<DL.Label>Started At</DL.Label>
							<DL.Content>
								{shortDateTime(model.started_at)}
							</DL.Content>
						</DL.Row>
					{/if}
					{#if model.status == 'cancelled'}
						<DL.Row orientation="vertical">
							<DL.Label>Cancelled At</DL.Label>
							<DL.Content>
								{shortDateTime(model.cancelled_at)}
							</DL.Content>
						</DL.Row>
					{/if}
					{#if model.status == 'ended'}
						<DL.Row orientation="vertical">
							<DL.Label>Ended At</DL.Label>
							<DL.Content>
								{shortDateTime(model.ended_at)}
							</DL.Content>
						</DL.Row>
					{/if}
					{#if model.status == 'terminated'}
						<DL.Row orientation="vertical">
							<DL.Label>Terminated At</DL.Label>
							<DL.Content>
								{shortDateTime(model.terminated_at)}
							</DL.Content>
						</DL.Row>
					{/if}
					{#if model.status == 'expired'}
						<DL.Row orientation="vertical">
							<DL.Label>Expired At</DL.Label>
							<DL.Content>
								{shortDateTime(model.expired_at)}
							</DL.Content>
						</DL.Row>
					{/if}
				</DL.Root>
			</div>
		{/if}
		<div>
			<Button
				variant="ghost"
				class="w-full transition-all duration-200 hover:border-gray-200 hover:bg-gray-50 hover:text-gray-500"
				onclick={() => (showMore = !showMore)}
			>
				<ChevronDown class="h-4 w-4 {showMore ? 'rotate-180' : ''} transition-all duration-200" />
				Show More
			</Button>
		</div>
	</Card.Content>
	{#if showActions}
		<Separator />
		<Card.Footer class="flex-col gap-2">
			{#if canRetrieveSecret}
				<Button
					variant="outline"
					class="w-full transition-all duration-200 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-500"
					onclick={async () => {
						retrieveSecretDialogIsOpen = true;
						await retrieveSecret();
					}}
				>
					<KeyRound class="h-4 w-4" />
					Retrieve Secret
				</Button>
			{/if}
			{#if canStart}
				<Button
					variant="outline"
					class="w-full transition-all duration-200 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-500"
					onclick={() => (startDialogIsOpen = true)}
				>
					<Play class="h-4 w-4" />
					Start Session
				</Button>
			{/if}
			{#if canCancel}
				<Button
					variant="outline"
					class="w-full transition-all duration-200 hover:border-red-200 hover:bg-red-50 hover:text-red-500"
					onclick={() => (cancelDialogIsOpen = true)}
				>
					<ClipboardX class="h-4 w-4" />
					Cancel Session
				</Button>
			{/if}
			{#if canEnd}
				<Button
					variant="outline"
					class="w-full transition-all duration-200 hover:border-red-200 hover:bg-red-50 hover:text-red-500"
					onclick={() => (endDialogIsOpen = true)}
				>
					<MonitorX class="h-4 w-4" />
					End Session
				</Button>
			{/if}
			{#if canTerminate}
				<Button
					variant="outline"
					class="w-full transition-all duration-200 hover:border-red-200 hover:bg-red-50 hover:text-red-500"
					onclick={() => (terminateDialogIsOpen = true)}
				>
					<MonitorX class="h-4 w-4" />
					Terminate Session
				</Button>
			{/if}
		</Card.Footer>
	{/if}
</Card.Root>
