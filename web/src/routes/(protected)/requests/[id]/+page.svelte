<script lang="ts">
	import * as Card from '$ui/card';
	import { Button } from '$ui/button';
	import {
		Ban,
		Check,
		ClipboardX,
		Siren,
		LucideClipboardCheck,
		X,
		SquareTerminal,
		Bot,
		ArrowRightToLine,
		Sparkles,
		FileCheckCorner,
        FileXCorner,
		ClockFading
	} from '@lucide/svelte';
	import * as DL from '$components/description-list';
	import { shortDateTime, shortDateTimeRange, relativeDateTime } from '$utils/date';
	import type { ApiRequestResource } from '$resources/request';
	import type { Request } from '$models/request';
	import type { Asset } from '$models/asset';
	import type { User } from '$models/user';
	import ApproveFormDialog from './approve-form-dialog.svelte';
	import RejectFormDialog from './reject-form-dialog.svelte';
	import { invalidateAll } from '$app/navigation';
	import { AssetToolTips } from '$components/tooltip';
	import * as AlertDialog from '$ui/alert-dialog';
	import Loader from '$components/loader.svelte';
	import { enhance } from '$app/forms';
	import { toast } from 'svelte-sonner';
	import * as VerticalProgress from '$components/vertical-progress';
	import type { Session } from '$lib/models/session';
	import { PageTitle } from '$components/page-title';
    import { StatusBadge } from '$components/badge';
    import { RiskBadge } from '$components/badge';
    import { nl2br } from '$lib/utils/string';
    import { formatDistanceStrict } from 'date-fns';
	import { Progress } from '$components/progress';
    import * as Alert from '$ui/alert';
    import * as ButtonGroup from '$ui/button-group';

	let { data } = $props();
	const permissions = $derived(data.permissions);
	const canViewSession = $derived(data.canViewSession);
	const modelResource = $derived(data.model as ApiRequestResource);
	const model = $derived(modelResource.data.attributes as Request);
	const asset = $derived(modelResource.data.relationships?.asset?.attributes as Asset);
	const requester = $derived(modelResource.data.relationships?.requester?.attributes as User);
    const approver = $derived(modelResource.data.relationships?.approver?.attributes as User);
	const session = $derived(modelResource.data.relationships?.session?.attributes as Session);
    const cancelledBy = $derived(modelResource.data.relationships?.cancelled_by?.attributes as User);
    const rejectedBy = $derived(modelResource.data.relationships?.rejecter?.attributes as User);

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

    function getRiskProgress(risk: 'low' | 'medium' | 'high' | 'critical') {
        switch (risk) {
        case 'low':
            return 25;
        case 'medium':
            return 50;
        case 'high':
            return 75;
        case 'critical':
            return 100;
        }
    }

    function getRiskProgressColor(risk: 'low' | 'medium' | 'high' | 'critical') {
        switch (risk) {
        case 'low':
            return 'bg-green-500';
        case 'medium':
            return 'bg-yellow-500';
        case 'high':
            return 'bg-amber-500';
        case 'critical':
            return 'bg-red-500';
        }
    }

    const aiRiskProgress = $derived(getRiskProgress(model.ai_risk_rating));
    const aiRiskProgressColor = $derived(getRiskProgressColor(model.ai_risk_rating));
    const approverRiskProgress = $derived(getRiskProgress(model.approver_risk_rating));
    const approverRiskProgressColor = $derived(getRiskProgressColor(model.approver_risk_rating));
</script>

<ApproveFormDialog
	bind:isOpen={approveDialogIsOpen}
	data={data.approveForm}
	onSuccess={async (data: Request) => {
		invalidateAll();
		approveDialogIsOpen = false;
	}}
/>

<RejectFormDialog
	bind:isOpen={rejectDialogIsOpen}
	data={data.rejectForm}
	onSuccess={async (data: Request) => {
		invalidateAll();
		rejectDialogIsOpen = false;
	}}
/>

<AlertDialog.Root bind:open={cancelDialogIsOpen}>
	<AlertDialog.Content>
		<AlertDialog.Header>
			<AlertDialog.Title>Are you sure you want to cancel this request?</AlertDialog.Title>
            <AlertDialog.Description>
                <p>
                    This action cannot be undone. Cancelling this request will prevent access to the asset and
                    no JIT credentials will be created.
                </p>
                <p class="mt-3 mb-3">You'll need to submit a new request if you need access again.</p>
            </AlertDialog.Description>
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
							invalidateAll();
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


<div class="flex flex-row space-x-2 items-center">
    <StatusBadge status={model.status} />
    <div class="text-sm text-slate-500 uppercase tracking-wider font-semibold">
        REQUEST #{model.id}
    </div>
</div>
<PageTitle
	title={asset.name}
	description="Requested from {shortDateTimeRange(
		model.start_datetime,
		model.end_datetime
	)}"
    class="-mt-4"
>
    <div class="flex justify-between gap-2">
        <ButtonGroup.Root>
            {#if canViewSession && session}
                <Button
                    variant="outline"
                    class="flex justify-between transition-all duration-200 px-4! py-2.5 hover:cursor-pointer bg-white hover:border-blue-200 hover:bg-blue-50 hover:text-blue-500"
                    href="/sessions/{session.id}"
                >
                    <div class="ml-4 flex-1 text-center">Go To Session</div>
                    <ArrowRightToLine class="h-4 w-4" />
                </Button>
            {/if}
            {#if permissions.canApprove}
                <Button
                    variant="outline"
                    class="transition-all duration-200 h-auto py-2.5 px-4! bg-white hover:border-blue-200 hover:bg-blue-50 hover:text-blue-500"
                    onclick={() => (approveDialogIsOpen = true)}
                >
                    <Check class="h-4 w-4" />
                    Approve
                </Button>
                <Button
                    variant="outline"
                    class="transition-all duration-200 h-auto py-2.5 px-4! bg-white hover:border-red-200 hover:bg-red-50 hover:text-red-500"
                    onclick={() => (rejectDialogIsOpen = true)}
                >
                    <Ban class="h-4 w-4" />
                    Reject
                </Button>
            {/if}
        </ButtonGroup.Root>
        <ButtonGroup.Root>
            {#if permissions.canCancel}
                <Button
                    variant="outline"
                    class="transition-all duration-200 h-auto py-2.5 px-4! bg-white hover:bg-slate-700 hover:text-slate-50"
                    onclick={() => (cancelDialogIsOpen = true)}
                >
                    <ClipboardX class="h-4 w-4" />
                    Cancel
                </Button>
            {/if} 
        </ButtonGroup.Root>
    </div>
</PageTitle>

{#if model.status == 'expired'}
    <Alert.Root class="text-muted-foreground border-blue-200 bg-blue-50">
        <ClockFading />
        <Alert.Title>
            The request for <b>{asset.name}</b> has expired without approval or rejection.
        </Alert.Title>
    </Alert.Root>
{/if}
{#if model.status == 'cancelled'}
    <Alert.Root class="text-muted-foreground border-blue-200 bg-blue-50">
        <Ban />
        <Alert.Title>
            The request for <b>{asset.name}</b> has been cancelled by <b>{cancelledBy.name}</b> on <b>{shortDateTime(model.cancelled_at)}</b>.
        </Alert.Title>
    </Alert.Root>
{/if}

<div class="flex flex-col space-y-4">
    <div class="mt-2 flex flex-col gap-6 lg:flex-row"> 
        <div class="min-w-0 flex-1 flex flex-col space-y-6">
            
            <Card.Root class="w-full border-0 shadow-xs lg:h-full">
                <Card.Header>
                    <Card.Title class="text-xl font-bold tracking-tight">Request Parameters</Card.Title>
                </Card.Header>
                <Card.Content class="relative">
                    <div class="grid grid-cols-2 gap-4">
                        <DL.Root divider={null}>
                            <DL.Row class="grid-cols-3!">
                                <DL.Label>Requested Asset</DL.Label>
                                <DL.Content class="col-span-2!"><AssetToolTips {asset} /></DL.Content>
                            </DL.Row>
                            <DL.Row class="grid-cols-3!">
                                <DL.Label>Start Date/Time</DL.Label>
                                <DL.Content class="col-span-2!">{shortDateTime(model.start_datetime)}</DL.Content>
                            </DL.Row>
                            <DL.Row class="grid-cols-3!">
                                <DL.Label>End Date/Time</DL.Label>
                                <DL.Content class="col-span-2!">{shortDateTime(model.end_datetime)}</DL.Content>
                            </DL.Row>
                            <DL.Row class="grid-cols-3!">
                                <DL.Label>Duration</DL.Label>
                                <DL.Content class="col-span-2!">{formatDistanceStrict(model.start_datetime, model.end_datetime)}</DL.Content>
                            </DL.Row>
                            <DL.Row class="grid-cols-3!">
                                <DL.Label>Scope</DL.Label>
                                <DL.Content class="col-span-2!">{model.scope}</DL.Content>
                            </DL.Row>
                            <DL.Row class="grid-cols-3!">
                                <DL.Label>Status</DL.Label>
                                <DL.Content class="col-span-2!"><StatusBadge status={model.status} /></DL.Content>
                            </DL.Row>
                        </DL.Root>
                        <DL.Root divider={null}>
                            <DL.Row class="grid-cols-3!">
                                <DL.Label>Requester</DL.Label>
                                <DL.Content class="col-span-2!">{requester.name} ({requester.email})</DL.Content>
                            </DL.Row>
                            <DL.Row class="grid-cols-3!">
                                <DL.Label>Requested At</DL.Label>
                                <DL.Content class="col-span-2!">{shortDateTime(model.created_at)}</DL.Content>
                            </DL.Row>
                            {#if model.rejected_at}
                                <DL.Row class="grid-cols-3!">
                                    <DL.Label>Rejected By</DL.Label>
                                    <DL.Content class="col-span-2!">{rejectedBy?.name} ({rejectedBy?.email})</DL.Content>
                                </DL.Row>
                                <DL.Row class="grid-cols-3!">
                                    <DL.Label>Rejected At</DL.Label>
                                    <DL.Content class="col-span-2!">{shortDateTime(model.rejected_at)}</DL.Content>
                                </DL.Row>
                            {:else if model.cancelled_at}
                                <DL.Row class="grid-cols-3!">
                                    <DL.Label>Cancelled By</DL.Label>
                                    <DL.Content class="col-span-2!">{cancelledBy?.name} ({cancelledBy?.email})</DL.Content>
                                </DL.Row>
                                <DL.Row class="grid-cols-3!">
                                    <DL.Label>Cancelled At</DL.Label>
                                    <DL.Content class="col-span-2!">{shortDateTime(model.cancelled_at)}</DL.Content>
                                </DL.Row>
                            {:else if model.expired_at}
                                <DL.Row class="grid-cols-3!">
                                    <DL.Label>Expired By</DL.Label>
                                    <DL.Content class="col-span-2!">Sytem</DL.Content>
                                </DL.Row>
                                <DL.Row class="grid-cols-3!">
                                    <DL.Label>Expired At</DL.Label>
                                    <DL.Content class="col-span-2!">{shortDateTime(model.expired_at)}</DL.Content>
                                </DL.Row>
                            {:else}
                                <DL.Row class="grid-cols-3!">
                                    <DL.Label>Approved By</DL.Label>
                                    {#if model.approved_at}
                                        <DL.Content class="col-span-2!">{approver?.name} ({approver?.email})</DL.Content>
                                    {:else}
                                        <DL.Content class="col-span-2!">-</DL.Content>
                                    {/if}
                                </DL.Row>
                            
                                <DL.Row class="grid-cols-3!">
                                    <DL.Label>Approved At</DL.Label>
                                    {#if model.approved_at}
                                        <DL.Content class="col-span-2!">{shortDateTime(model.approved_at)}</DL.Content>
                                    {:else}
                                        <DL.Content class="col-span-2!">-</DL.Content>
                                    {/if}
                                </DL.Row> 
                            {/if}
                        </DL.Root>
                    </div>
                    
                </Card.Content>
            </Card.Root>

            {#if model.approved_at || model.rejected_at}

                <Card.Root class="w-full border-0 shadow-xs">
                    <Card.Header class="flex items-center gap-2">
                        {#if model.approved_at}
                            <FileCheckCorner strokeWidth={2.3} />
                        {:else}
                            <FileXCorner strokeWidth={2.3} />
                        {/if}
                        <Card.Title class="text-xl font-bold tracking-tight grow">{model.approved_at ? 'Approval' : 'Rejection'} Details</Card.Title>
                        {#if model.approved_at}
                            <span class="font-semibold tracking-wider uppercase text-sm text-slate-400">Risk</span>
                            <RiskBadge risk={model.approver_risk_rating} class="uppercase" />
                        {/if}
                    </Card.Header>
                    <Card.Content class="relative">
                        <div class="bg-slate-50 rounded p-4 border-l-4 text-slate-600 text-sm">
                            {@html nl2br(model.approver_note)}
                        </div>
                    </Card.Content>
                </Card.Root>

            {/if}

            <Card.Root class="w-full border-0 shadow-xs">
                <Card.Header class="flex items-center gap-2">
                    <Sparkles strokeWidth={2.3} class="" />
                    <Card.Title class="text-xl font-bold tracking-tight grow">AI Evaluation</Card.Title>
                    <span class="font-semibold tracking-wider uppercase text-sm text-slate-400">Risk</span>
                    <RiskBadge risk={model.ai_risk_rating} class="uppercase" />
                </Card.Header>
                <Card.Content class="relative">
                    <div class="bg-slate-50 rounded p-4 border-l-4 text-slate-600 text-sm">
                        {@html nl2br(model.ai_note)}
                    </div>
                </Card.Content>
            </Card.Root>

            {#if model.is_access_sensitive_data}
                <Card.Root class="w-full border-0 shadow-xs bg-red-50">
                    <Card.Header class="flex items-center gap-2">
                        <Card.Title class="text-xl font-bold tracking-tight text-red-500 grow">Sensitive Data Access</Card.Title>
                        <div class="bg-red-500/10 rounded-lg p-2">
                            <Siren class="h-4 w-4 text-red-500" />
                        </div>
                    </Card.Header>
                    <Card.Content class="relative text-red-400 text-sm">
                        {@html nl2br(model.sensitive_data_note)}
                    </Card.Content>
                </Card.Root>
            {/if}

            <Card.Root class="w-full border-0 shadow-xs">
                <Card.Header>
                    <Card.Title class="text-xl font-bold tracking-tight">Request Reason</Card.Title>
                </Card.Header>
                <Card.Content class="relative text-sm">
                    {@html nl2br(model.reason)}
                </Card.Content>
            </Card.Root>

            <Card.Root class="w-full border-0 shadow-xs">
                <Card.Header>
                    <Card.Title class="text-xl font-bold tracking-tight">Intended Query</Card.Title>
                </Card.Header>
                <Card.Content class="relative">
                    <div class="bg-slate-50 rounded p-4 text-slate-500 font-mono text-sm">
                        {@html nl2br(model.intended_query)}
                    </div>
                </Card.Content>
            </Card.Root>
        </div>
        <aside class="flex w-full flex-col gap-6 lg:w-90">
            <Card.Root class="w-full border-0 shadow-xs bg-slate-800">
                <Card.Header>
                    <Card.Title class="text-xl font-bold tracking-tight text-slate-50">
                        Risk Assessment
                    </Card.Title>
                </Card.Header>
                <Card.Content class="relative text-slate-50">
                    <div class="mb-8">
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <span class="font-bold">AI Risk</span>
                            <StatusBadge status={model.ai_risk_rating} class="uppercase text-xs" />
                        </div>
                        <div>
                            <Progress 
                                value={aiRiskProgress} 
                                innerClass={aiRiskProgressColor} 
                                class="bg-slate-700"
                            />
                        </div>
                    </div>
                    {#if model.approved_at}
                        <div class="mb-8">
                            <div class="flex items-center justify-between gap-2 mb-3">
                                <span class="font-bold">Approver Risk</span>
                                <StatusBadge status={model.approver_risk_rating} class="uppercase text-xs" />
                            </div>
                            <div>
                                <Progress 
                                    value={approverRiskProgress} 
                                    innerClass={approverRiskProgressColor} 
                                    class="bg-slate-700" 
                                />
                            </div>
                        </div>
                    {/if}
                    <div>
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <span class="font-bold">Sensitive Data Access</span>
                            {#if model.is_access_sensitive_data}
                                <StatusBadge status="yes" class="uppercase text-xs bg-red-100 border-red-500 text-red-500" />
                            {:else}
                                <StatusBadge status="no" class="uppercase text-xs bg-green-100 border-green-500 text-green-500" />
                            {/if}
                        </div>
                    </div>
                </Card.Content>
            </Card.Root>

            <Card.Root class="w-full border-0 shadow-xs">
                <Card.Header>
                    <Card.Title class="text-xl font-bold tracking-tight">Request Timeline</Card.Title>
                </Card.Header>
                <Card.Content class="relative">
                
                    <VerticalProgress.Root className="pl-0 ml-5">
                        <VerticalProgress.Row
                            icon={Check}
                            title="Request Created"
                            description={relativeDateTime(model.created_at, false)}
                            color={'green'}
                        />
                        <VerticalProgress.Row
                            icon={hasSubmitted ? Check : Bot}
                            title="AI Evaluation"
                            description={relativeDateTime(model.submitted_at, false)}
                            color={hasSubmitted ? 'green' : 'blue'}
                        />
                        {#if !hasApproval}
                            <VerticalProgress.Row
                                icon={LucideClipboardCheck}
                                title="Pending Approval"
                                description="-"
                                color={hasSubmitted ? 'blue' : 'gray'}
                            />
                        {/if}
                        {#if model.status == 'approved'}
                            <VerticalProgress.Row
                                icon={Check}
                                title="Approved"
                                description={relativeDateTime(model.approved_at, false)}
                                color="green"
                            />
                        {/if}
                        {#if model.status == 'rejected'}
                            <VerticalProgress.Row
                                icon={X}
                                title="Rejected"
                                description={relativeDateTime(model.rejected_at, false)}
                                color="red"
                            />
                        {/if}
                        {#if model.status == 'cancelled'}
                            <VerticalProgress.Row
                                icon={ClipboardX}
                                title="Request Cancelled"
                                description={relativeDateTime(model.cancelled_at, false)}
                                color="gray"
                            />
                        {/if}
                        {#if model.status == 'expired'}
                            <VerticalProgress.Row
                                icon={ClipboardX}
                                title="Expired"
                                description={relativeDateTime(model.expired_at, false)}
                                color="gray"
                            />
                        {/if}
                        <VerticalProgress.Row
                            icon={session ? Check : SquareTerminal}
                            title="Session Created"
                            description={relativeDateTime(session?.created_at, false)}
                            color={session ? 'green' : hasApproved ? 'blue' : 'gray'}
                            disabled={hasCancelled || hasExpired || hasRejected}
                        />
                    </VerticalProgress.Root>

                </Card.Content>
            </Card.Root>
        </aside>
    </div>
</div>
