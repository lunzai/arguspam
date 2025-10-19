<script lang="ts">
	import * as Card from '$ui/card';
	import * as DL from '$components/description-list';
	import { StatusBadge } from '$components/badge';
	import { AssetToolTips } from '$components/tooltip';
	import { relativeDateTime, shortDateTime, shortDateTimeRange } from '$utils/date';
	import { Separator } from '$ui/separator';
	import { Button } from '$ui/button';
	import { Play, ClipboardX, MonitorX, ChevronDown } from '@lucide/svelte';
	import type { Session } from '$models/session';
	import type { SessionPermission } from '$resources/session';
	import type { User } from '$models/user';
	import type { Asset } from '$models/asset';
	import type { Request } from '$models/request';
	import { slide } from 'svelte/transition';

	interface Props {
		model: Session;
		permissions: SessionPermission;
		requester: User;
		asset: Asset;
		request: Request;
		approver: User;
	}

	let { model, permissions, requester, asset, request, approver }: Props = $props();

	const canStart = $derived(permissions.canStart && model.status == 'scheduled');
	const canCancel = $derived(permissions.canCancel && model.status == 'scheduled');
	const canEnd = $derived(permissions.canEnd && model.status == 'started');
	const canTerminate = $derived(permissions.canTerminate && model.status == 'started');
	const showActions = $derived(canStart || canCancel || canEnd || canTerminate);

	// const canStart = true;
	// const canCancel = true;
	// const canEnd = true;
	// const canTerminate = true;
	// const showActions = $derived(canStart || canCancel || canEnd || canTerminate);

	let startDialogIsOpen = $state(false);
	let cancelDialogIsOpen = $state(false);
	let endDialogIsOpen = $state(false);
	let terminateDialogIsOpen = $state(false);
	let showMore = $state(false);
</script>

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
