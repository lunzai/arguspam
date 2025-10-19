<script lang="ts">
	import * as Card from '$ui/card';
	import { Button } from '$ui/button';
	import {
		Ban,
		Check,
		ClipboardX,
		Hourglass,
		LucideClipboardCheck,
		X,
		SquareTerminal,
		Bot,
		ArrowRightToLine
	} from '@lucide/svelte';
	import * as DL from '$components/description-list';
	import { shortDateTime, shortDateTimeRange, relativeDateTime } from '$utils/date';
	import type { ApiRequestResource } from '$resources/request';
	import type { Request } from '$models/request';
	import type { Asset } from '$models/asset';
	import type { User } from '$models/user';
	import ApproveFormDialog from './approve-form-dialog.svelte';
	import RejectFormDialog from './reject-form-dialog.svelte';
	import { invalidate } from '$app/navigation';
	import PageContent from './page-content.svelte';
	import { Separator } from '$ui/separator';
	import { StatusBadge } from '$components/badge';
	import { AssetToolTips } from '$components/tooltip';
	import * as AlertDialog from '$ui/alert-dialog';
	import Loader from '$components/loader.svelte';
	import { enhance } from '$app/forms';
	import { toast } from 'svelte-sonner';
	import * as Progress from '$components/vertical-progress';
	import type { Session } from '$lib/models/session';

	let { data } = $props();
	const permissions = $derived(data.permissions);
	const modelResource = $derived(data.model as ApiRequestResource);
	const model = $derived(modelResource.data.attributes as Request);
	const asset = $derived(modelResource.data.relationships?.asset?.attributes as Asset);
	const requester = $derived(modelResource.data.relationships?.requester?.attributes as User);
	const session = $derived(modelResource.data.relationships?.session?.attributes as Session);

	let approveDialogIsOpen = $state(false);
	let rejectDialogIsOpen = $state(false);
	let cancelDialogIsOpen = $state(false);
	let cancelDialogIsLoading = $state(false);

	const hasSubmitted = $derived(model.submitted_at !== null);
	const hasApproval = $derived(
		model.approved_at || model.rejected_at || model.cancelled_at || model.expired_at
	);
	const hasCancelled = $derived(model.cancelled_at !== null);
	const hasApproved = $derived(model.approved_at !== null);
	const hasRejected = $derived(model.rejected_at !== null);
	const hasExpired = $derived(model.expired_at !== null);
</script>

<ApproveFormDialog
	bind:isOpen={approveDialogIsOpen}
	data={data.approveForm}
	onSuccess={async (data: Request) => {
		await invalidate('requests:view');
		approveDialogIsOpen = false;
	}}
/>

<RejectFormDialog
	bind:isOpen={rejectDialogIsOpen}
	data={data.rejectForm}
	onSuccess={async (data: Request) => {
		await invalidate('requests:view');
		rejectDialogIsOpen = false;
	}}
/>

<AlertDialog.Root bind:open={cancelDialogIsOpen}>
	<AlertDialog.Content>
		<AlertDialog.Header>
			<AlertDialog.Title>Are you sure?</AlertDialog.Title>
		</AlertDialog.Header>
		<AlertDialog.Footer>
			<form
				method="POST"
				action="?/cancel"
				use:enhance={({ cancel }) => {
					cancelDialogIsLoading = true;
					return async ({ result, update }) => {
						if (result.type === 'success') {
							toast.success('Request cancelled successfully');
							invalidate('requests:view');
							cancel();
						} else {
							toast.error('Failed to cancel request');
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

<div class="flex flex-col space-y-4">
	<h1 class="text-2xl font-medium capitalize">
		Request - #{model.id} - {asset.name} ({shortDateTimeRange(
			model.start_datetime,
			model.end_datetime
		)})
	</h1>
	<Separator />
	<div class="mt-2 flex flex-col gap-6 lg:flex-row">
		<aside class="flex w-full flex-col gap-6 lg:w-64">
			{@render sidebar()}
			<Card.Root class="w-full">
				<Card.Content class="">
					<div class="">
						<Progress.Root className="pl-0 ml-3">
							<Progress.Row
								icon={Check}
								title="Request Created"
								description={relativeDateTime(model.created_at, false)}
								color={'green'}
							/>
							<Progress.Row
								icon={hasSubmitted ? Check : Bot}
								title="AI Evaluation"
								description={relativeDateTime(model.submitted_at, false)}
								color={hasSubmitted ? 'green' : 'blue'}
							/>
							{#if !hasApproval}
								<Progress.Row
									icon={LucideClipboardCheck}
									title="Pending Approval"
									description="-"
									color={hasSubmitted ? 'blue' : 'gray'}
								/>
							{/if}
							{#if model.status == 'approved'}
								<Progress.Row
									icon={Check}
									title="Approved"
									description={relativeDateTime(model.approved_at, false)}
									color="green"
								/>
							{/if}
							{#if model.status == 'rejected'}
								<Progress.Row
									icon={X}
									title="Rejected"
									description={relativeDateTime(model.rejected_at, false)}
									color="red"
								/>
							{/if}
							{#if model.status == 'cancelled'}
								<Progress.Row
									icon={ClipboardX}
									title="Request Cancelled"
									description={relativeDateTime(model.cancelled_at, false)}
									color="gray"
								/>
							{/if}
							{#if model.status == 'expired'}
								<Progress.Row
									icon={ClipboardX}
									title="Expired"
									description={relativeDateTime(model.expired_at, false)}
									color="gray"
								/>
							{/if}
							<Progress.Row
								icon={session ? Check : SquareTerminal}
								title="Session Created"
								description={relativeDateTime(session?.created_at, false)}
								color={session ? 'green' : hasApproved ? 'blue' : 'gray'}
								disabled={hasCancelled || hasExpired || hasRejected}
							/>
						</Progress.Root>
					</div>
				</Card.Content>
			</Card.Root>
		</aside>
		<Separator orientation="vertical" class="hidden lg:block" />
		<div class="min-w-0 flex-1">
			<PageContent {data} />
		</div>
	</div>
</div>

{#snippet sidebar()}
	<Card.Root class="w-full">
		<Card.Header>
			<Card.Title class="text-lg">#{model.id} - {asset.name}</Card.Title>
		</Card.Header>
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
				<DL.Row orientation="vertical">
					<DL.Label>Requested At</DL.Label>
					<DL.Content>
						{shortDateTime(model.created_at)}
					</DL.Content>
				</DL.Row>
				{#if model.status == 'cancelled'}
					<DL.Row orientation="vertical">
						<DL.Label>Cancelled At</DL.Label>
						<DL.Content>
							{shortDateTime(model.cancelled_at as Date)}
						</DL.Content>
					</DL.Row>
				{/if}
			</DL.Root>
		</Card.Content>
		{#if permissions.canApprove || permissions.canCancel || model.status == 'approved'}
			<Separator />
			<Card.Footer class="flex-col gap-2">
				{#if session}
					<Button
						variant="outline"
						class="flex w-full justify-between transition-all duration-200 hover:cursor-pointer hover:border-blue-200 hover:bg-blue-50 hover:text-blue-500"
						href="/sessions/{session.id}"
					>
						<div class="ml-4 flex-1 text-center">Go To Session</div>
						<ArrowRightToLine class="h-4 w-4" />
					</Button>
				{/if}
				{#if permissions.canApprove}
					<Button
						variant="outline"
						class="w-full transition-all duration-200 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-500"
						onclick={() => (approveDialogIsOpen = true)}
					>
						<Check class="h-4 w-4" />
						Approve
					</Button>
					<Button
						variant="outline"
						class="w-full transition-all duration-200 hover:border-red-200 hover:bg-red-50 hover:text-red-500"
						onclick={() => (rejectDialogIsOpen = true)}
					>
						<Ban class="h-4 w-4" />
						Reject
					</Button>
				{/if}
				{#if permissions.canCancel}
					<Button
						variant="outline"
						class="w-full transition-all duration-200 hover:border-red-200 hover:bg-red-50 hover:text-red-500"
						onclick={() => (cancelDialogIsOpen = true)}
					>
						<ClipboardX class="h-4 w-4" />
						Cancel
					</Button>
				{/if}
			</Card.Footer>
		{/if}
	</Card.Root>
{/snippet}
