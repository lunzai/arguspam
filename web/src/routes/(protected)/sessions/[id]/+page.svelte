<script lang="ts">
	import { shortDateTimeRange, shortDateTime, relativeDateTime } from '$utils/date';
	import type { ApiSessionResource } from '$resources/session';
	import type { Session } from '$models/session';
	import type { Asset } from '$models/asset';
	import type { Me, User } from '$models/user';
	import type { Request } from '$models/request';
	import type { SessionAuditCollection } from '$lib/resources/session-audit';
	import Actions from './actions.svelte';
	import { Progress } from '$components/progress';
	import type { SessionFlagCollection } from '$lib/resources/session-flag';
	import * as Tabs from '$ui/tabs';
    import { StatusBadge } from '$components/badge';
    import { PageTitle } from '$components/page-title';
    import * as Alert from '$ui/alert';
    import { ClockFading, MonitorX, Ban, Check, Play, Bot, ClipboardX, Siren, UserRoundCheck, UserRoundPen, Database, Sparkles, Logs } from '@lucide/svelte';
    import * as Card from '$ui/card';
    import * as VerticalProgress from '$components/vertical-progress';
    import { getLocalTimeZone } from '@internationalized/date';
    import { formatDistanceStrict } from 'date-fns';
    import { capitalizeWords, nl2br } from '$utils/string';
    import { DbmsBadge } from '$components/badge';
	import Badge from '$lib/components/ui/badge/badge.svelte';
    import * as Item from '$ui/item';

	let { data } = $props();
	const permissions = $derived(data.permissions);
	const modelResource = $derived(data.model as ApiSessionResource);
	const model = $derived(modelResource.data.attributes as Session);
	const asset = $derived(modelResource.data.relationships?.asset?.attributes as Asset);
	const requester = $derived(modelResource.data.relationships?.requester?.attributes as User);
	const request = $derived(modelResource.data.relationships?.request?.attributes as Request);
	const approver = $derived(modelResource.data.relationships?.approver?.attributes as User);
	const audits = $derived(modelResource.data.relationships?.audits as SessionAuditCollection);
    const cancelledBy = $derived(modelResource.data.relationships?.cancelledBy?.attributes as User);
    const terminatedBy = $derived(modelResource.data.relationships?.terminatedBy?.attributes as User);
	const flags = $derived(modelResource.data.relationships?.flags as SessionFlagCollection);
	const me = $derived(data.me as Me);
    const subTitle = $derived(model.end_datetime ? `Ended at ${shortDateTime(model.end_datetime)}` : `Scheduled for ${shortDateTimeRange(model.scheduled_start_datetime, model.scheduled_end_datetime)}`);
    const isTerminated = $derived(model.status == 'terminated');
	const isCancelled = $derived(model.status == 'cancelled');
	const isExpired = $derived(model.status == 'expired');
	const isEnded = $derived(model.status == 'ended');
	const isScheduled = $derived(model.status == 'scheduled');
	const hasEnded = $derived(isEnded || isTerminated || isCancelled || isExpired);
	const hasStart = $derived(model.started_at !== null);
	const isExpiredOrCancelled = $derived(isExpired || isCancelled);
	const isEndedOrTerminated = $derived(isEnded || isTerminated);

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

    const activityRiskProgress = $derived(getRiskProgress(model.session_activity_risk));
    const activityRiskProgressColor = $derived(getRiskProgressColor(model.session_activity_risk));
    const deviationRiskProgress = $derived(getRiskProgress(model.deviation_risk));
    const deviationRiskProgressColor = $derived(getRiskProgressColor(model.deviation_risk));
    const overallRiskProgress = $derived(getRiskProgress(model.overall_risk));
    const overallRiskProgressColor = $derived(getRiskProgressColor(model.overall_risk));
    const requestRiskProgress = $derived(getRiskProgress(request.ai_risk_rating));
    const requestRiskProgressColor = $derived(getRiskProgressColor(request.ai_risk_rating));
    const approverRiskProgress = $derived(getRiskProgress(request.approver_risk_rating));
    const approverRiskProgressColor = $derived(getRiskProgressColor(request.approver_risk_rating));

</script>

<div class="flex flex-row space-x-2 items-center">
    <StatusBadge status={model.status} />
    <div class="text-sm text-slate-500 uppercase tracking-wider font-semibold">
        SESSION #{model.id}
    </div>
</div>
<PageTitle
    title={asset.name}
    description={subTitle}
    class="-mt-4"
>
    <Actions {model} {asset} {permissions} {me} />
</PageTitle>

{#if model.status == 'expired'}
    <Alert.Root class="text-muted-foreground border-blue-200 bg-blue-50">
        <ClockFading />
        <Alert.Title>
            The session for <b>{asset.name}</b> has expired without being started. No JIT account was created.
        </Alert.Title>
    </Alert.Root>
{/if}
{#if model.status == 'cancelled'}
    <Alert.Root class="text-muted-foreground border-blue-200 bg-blue-50">
        <Ban />
        <Alert.Title>
            The session for <b>{asset.name}</b> has been cancelled by <b>{cancelledBy.name}</b> on <b>{shortDateTime(model.cancelled_at)}</b>. No JIT account was created.
        </Alert.Title>
    </Alert.Root>
{/if}
{#if model.status == 'terminated'}
    <Alert.Root class="text-muted-foreground border-blue-200 bg-blue-50">
        <MonitorX />
        <Alert.Title>
            The request for <b>{asset.name}</b> has been terminated by <b>{terminatedBy.name}</b> on <b>{shortDateTime(model.terminated_at)}</b>. 
        </Alert.Title>
    </Alert.Root>
{/if}

<div class="flex flex-col space-y-4">
    <div class="mt-2 flex flex-col gap-6 lg:flex-row"> 
        <div class="min-w-0 flex-1 flex flex-col space-y-6  h-full">
            
            <Card.Root class="w-full border-0 shadow-xs">
                <Card.Header>
                    <Card.Title class="text-xl font-bold tracking-tight">Session Details</Card.Title>
                </Card.Header>
                <Card.Content class="relative">
                    <div class="flex flex-col gap-8">
                        <div class="grid grid-cols-2 gap-6">
                            <div class="flex flex-col gap-2">
                                <div class="font-bold text-slate-500 uppercase text-sm tracking-wider">
                                    Asset
                                </div>
                                <div class="flex gap-3 items-center">
                                    <div class="bg-slate-100 rounded size-10 flex items-center justify-center">
                                        <Database class="size-5 text-slate-500" />
                                    </div>
                                    <div class="flex flex-col gap-1">
                                        <div class="text-sm font-bold">
                                            {asset.name}
                                        </div>
                                        <div class="">
                                            <DbmsBadge dbms={asset.dbms} class="text-xs font-mono" />
                                            <Badge variant="secondary" class="bg-slate-100 text-slate-500 font-mono">
                                                {asset.host}:{asset.port}
                                            </Badge>
                                        </div>
                                    </div>
                                </div>                            
                            </div>
                            <div class="flex flex-col gap-2">
                                <div class="font-bold text-slate-500 uppercase text-sm tracking-wider">
                                    Status
                                </div>
                                <div class="flex gap-3 items-center">
                                    <div class="bg-slate-100 rounded size-10 flex items-center justify-center">
                                        <Play class="size-5 text-slate-500" />
                                    </div>
                                    <div class="flex flex-col gap-1">
                                        <div class="text-sm font-bold">
                                            <StatusBadge status={model.status} />
                                        </div>
                                    </div>
                                </div>                            
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-6">
                            <div class="flex flex-col gap-2">
                                <div class="font-bold text-slate-500 uppercase text-sm tracking-wider">
                                    Requester
                                </div>
                                <div class="flex gap-3 items-center">
                                    <div class="bg-slate-100 rounded size-10 flex items-center justify-center">
                                        <UserRoundPen class="size-5 text-slate-500" />
                                    </div>
                                    <div class="flex flex-col gap-1">
                                        <div class="text-sm font-bold">
                                            {requester.name} ({requester.email})
                                        </div>
                                        <div class="text-xs text-muted-foreground">
                                            Requested at {shortDateTime(request.created_at)} ({getLocalTimeZone()})
                                        </div>
                                    </div>
                                </div>                                
                            </div>
                            <div class="flex flex-col gap-2">
                                <div class="font-bold text-slate-500 uppercase text-sm tracking-wider">
                                    Approver
                                </div>
                                <div class="flex gap-3 items-center">
                                    <div class="bg-slate-100 rounded size-10 flex items-center justify-center">
                                        <UserRoundCheck class="size-5 text-slate-500" />
                                    </div>
                                    <div class="flex flex-col gap-1">
                                        <div class="text-sm font-bold">
                                            {approver.name} ({approver.email})
                                        </div>
                                        <div class="text-xs text-muted-foreground">
                                            Approved at {shortDateTime(request.approved_at)} ({getLocalTimeZone()})
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        {#if request.is_access_sensitive_data}
                            <div class="bg-red-50 rounded p-4 border-l-4 text-red-500 border-red-500 text-sm">
                                <div class="flex flex-col gap-2">
                                    <div class="flex items-center gap-2 font-bold text-sm text-red-500 uppercase tracking-wider">
                                        <Siren class="h-4 w-4 text-red-500" />
                                        Sensitive Data Access
                                    </div>
                                    <div class="text-sm">
                                        {@html nl2br(request.sensitive_data_note)}
                                    </div>
                                </div>
                            </div>
                        {/if}
                        <div class="flex flex-col gap-2">
                            <div class="font-bold text-sm text-slate-500 uppercase tracking-wider">
                                Request Reason
                            </div>
                            <div class="text-sm">
                                {@html nl2br(request.reason)}
                            </div>
                        </div>
                        <div class="flex flex-col gap-2">
                            <div class="font-bold text-sm text-slate-500 uppercase tracking-wider">
                                Intended Query
                            </div>
                            <div class="bg-slate-50 rounded p-4 text-slate-500">
                                <div class="font-mono text-sm">
                                    {@html nl2br(request.intended_query)}
                                </div>
                            </div>
                        </div>
                    </div>
                </Card.Content>
            </Card.Root>
        </div>
        <aside class="flex w-full flex-col gap-6 lg:w-90">
            
            <Card.Root class="w-full border-0 shadow-xs h-full">
                <Card.Header>
                    <Card.Title class="text-xl font-bold tracking-tight">Session Timeline</Card.Title>
                </Card.Header>
                <Card.Content class="relative">
                    <div class="flex flex-col gap-8">
                        <div class="flex flex-col gap-2">
                            <div class="font-bold text-slate-500 uppercase text-sm tracking-wider">
                                Scheduled Window
                            </div>
                            <div class="flex flex-col gap-1">
                                <div class="text-sm">
                                    {shortDateTimeRange(model.scheduled_start_datetime, model.scheduled_end_datetime)}
                                </div>
                                <div class="text-xs text-muted-foreground">
                                    {formatDistanceStrict(model.scheduled_start_datetime, model.scheduled_end_datetime)}
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-col gap-2">
                            <div class="font-bold text-slate-500 uppercase text-sm tracking-wider">
                                Actual Session
                            </div>
                            <div class="flex flex-col gap-1">
                                <div class="text-sm">
                                {shortDateTimeRange(model.start_datetime, model.end_datetime)}
                                </div>
                                <div class="text-xs  text-muted-foreground">
                                    {formatDistanceStrict(model.start_datetime, model.end_datetime)}
                                </div>
                            </div>
                        </div>
                    
                        <div class="flex flex-col gap-2">
                            <div class="font-bold text-slate-500 uppercase text-sm tracking-wider">
                                Timeline
                            </div>
                            <VerticalProgress.Root className="pl-0 ml-3 mt-2">
                                <VerticalProgress.Row
                                    icon={Check}
                                    title="Scheduled"
                                    description={relativeDateTime(model.created_at, false)}
                                    color="green"
                                />
                
                                {#if isScheduled}
                                    <VerticalProgress.Row
                                        icon={Play}
                                        title="To Start"
                                        description={relativeDateTime(model.created_at, false)}
                                        color="blue"
                                    />
                                {/if}
                
                                {#if hasStart}
                                    <VerticalProgress.Row
                                        icon={Check}
                                        title="Started"
                                        description={relativeDateTime(model.created_at, false)}
                                        color="green"
                                    />
                                {/if}
                
                                {#if !isTerminated}
                                    <VerticalProgress.Row
                                        icon={Bot}
                                        title={model.end_datetime ? 'Ended' : 'To End'}
                                        description={model.end_datetime
                                            ? relativeDateTime(model.end_datetime, false)
                                            : relativeDateTime(model.scheduled_end_datetime, false) + ' remaining'}
                                        color={model.end_datetime ? 'green' : model.start_datetime ? 'blue' : 'gray'}
                                        disabled={isExpiredOrCancelled}
                                    />
                                {/if}
                
                                {#if isTerminated}
                                    <VerticalProgress.Row
                                        icon={MonitorX}
                                        title="Terminated"
                                        description={relativeDateTime(model.terminated_at, false)}
                                        color="yellow"
                                    />
                                {/if}
                
                                {#if isCancelled}
                                    <VerticalProgress.Row
                                        icon={ClipboardX}
                                        title="Cancelled"
                                        description={relativeDateTime(model.cancelled_at, false)}
                                        color="gray"
                                    />
                                {/if}
                
                                {#if isExpired}
                                    <VerticalProgress.Row
                                        icon={ClipboardX}
                                        title="Expired"
                                        description={relativeDateTime(model.expired_at, false)}
                                        color="gray"
                                    />
                                {/if}
                
                                <VerticalProgress.Row
                                    icon={Bot}
                                    title={model.ai_reviewed_at ? 'AI Audited' : 'AI Audit'}
                                    description={isExpiredOrCancelled ? '-' : relativeDateTime(model.ai_reviewed_at, false)}
                                    color={model.ai_reviewed_at ? 'green' : isEndedOrTerminated ? 'blue' : 'gray'}
                                    disabled={isExpiredOrCancelled}
                                />
                            </VerticalProgress.Root>
                        </div>
                    </div>
                </Card.Content>
            </Card.Root>
        </aside>
    </div>
</div>

<Tabs.Root value="details">
    <Tabs.List>
        <Tabs.Trigger 
            value="details" 
            class="cursor-pointer px-8 py-2 data-[state=active]:bg-white"
        >
            Details
        </Tabs.Trigger>
        <Tabs.Trigger
            disabled={audits?.length === 0}
            value="audits"
            class="px-8 py-2 data-[state=active]:bg-white 
            {audits?.length > 0
                ? 'cursor-pointer'
                : 'hover:cursor-not-allowed'}">Audits</Tabs.Trigger
        >
    </Tabs.List>
    <Tabs.Content value="details">
        <div class="flex flex-col space-y-4">
            {@render aiNotes()}
        </div>
    </Tabs.Content>
    <Tabs.Content value="audits">
        {@render queryAudit()}
    </Tabs.Content>
</Tabs.Root>


{#snippet aiNotes()}
    {#if isEndedOrTerminated}
        <div class="flex flex-col space-y-4">
            <div class="mt-2 flex flex-col gap-6 lg:flex-row"> 
                <div class="min-w-0 flex-1 flex flex-col space-y-6">
                    <Card.Root class="w-full border-0 shadow-xs h-full">
                        <Card.Header class="flex items-center gap-2">
                            <Sparkles strokeWidth={2.3} class="" />
                            <Card.Title class="text-xl font-bold tracking-tight">Session AI Intelligence</Card.Title>
                        </Card.Header>
                        <Card.Content class="relative">
                            {#if model.ai_reviewed_at}
                                <div class="flex flex-col gap-6">
                                    <div class="flex flex-col gap-2">
                                        <div class="font-bold text-slate-500 uppercase text-sm tracking-wider">
                                            Anomaly Flags
                                        </div>
                                        <div class="text-sm flex flex-wrap gap-2">
                                            {#each flags as flag}
                                                <Badge variant="secondary" class="bg-red-100 text-red-500 border-red-200 font-mono capitalize">
                                                    {flag.attributes.flag}
                                                </Badge>
                                            {:else}
                                                <div class="text-sm text-muted-foreground">-</div>
                                            {/each}
                                        </div>
                                    </div>
                                    <div class="flex flex-col gap-2">
                                        <div class="font-bold text-slate-500 uppercase text-sm tracking-wider">
                                            Query Analysis
                                        </div>
                                        <div class="text-sm">
                                            {@html nl2br(model.ai_note)}
                                        </div>
                                    </div>
                                </div>
                            {:else}
                                <div class="text-sm text-muted-foreground">Pending AI Review...</div>
                            {/if}
                        </Card.Content>
                    </Card.Root>
                </div>
                <aside class="flex w-full flex-col gap-6 lg:w-90">
                    
                    <Card.Root class="w-full border-0 shadow-xs bg-slate-800 h-full">
                        <Card.Header>
                            <Card.Title class="text-xl font-bold tracking-tight text-slate-50">
                                Session Risk Assessment
                            </Card.Title>
                        </Card.Header>
                        <Card.Content class="relative text-slate-50">
                            {#if model.ai_reviewed_at}
                                <div class="mb-8">
                                    <div class="flex items-center justify-between gap-2 mb-3">
                                        <span class="font-bold">Activity Risk</span>
                                        <StatusBadge status={model.session_activity_risk} class="uppercase text-xs" />
                                    </div>
                                    <div>
                                        <Progress 
                                            value={activityRiskProgress} 
                                            innerClass={activityRiskProgressColor} 
                                            class="bg-slate-700" 
                                        />
                                    </div>
                                </div>
                                <div class="mb-8">
                                    <div class="flex items-center justify-between gap-2 mb-3">
                                        <span class="font-bold">Deviation Risk</span>
                                        <StatusBadge status={model.deviation_risk} class="uppercase text-xs" />
                                    </div>
                                    <div>
                                        <Progress 
                                            value={deviationRiskProgress} 
                                            innerClass={deviationRiskProgressColor} 
                                            class="bg-slate-700" 
                                        />
                                    </div>
                                </div>
                                <div class="mb-8">
                                    <div class="flex items-center justify-between gap-2 mb-3">
                                        <span class="font-bold">Overall Risk</span>
                                        <StatusBadge status={model.overall_risk} class="uppercase text-xs" />
                                    </div>
                                    <div>
                                        <Progress 
                                            value={overallRiskProgress} 
                                            innerClass={overallRiskProgressColor} 
                                            class="bg-slate-700" 
                                        />
                                    </div>
                                </div>
                                <div>
                                    <div class="flex items-center justify-between gap-2 mb-3">
                                        <span class="font-bold">Human Audit</span>
                                        {#if model.human_audit_required}
                                            <StatusBadge status="required" class="uppercase text-xs bg-red-100 border-red-500 text-red-500" />
                                        {:else}
                                            <StatusBadge status="optional" class="uppercase text-xs bg-green-100 border-green-500 text-green-500" />
                                        {/if}
                                    </div>
                                </div>
                            {:else}
                                <div class="text-sm text-muted-foreground">Pending AI Review...</div>
                            {/if}
                        </Card.Content>
                    </Card.Root>

                </aside>
            </div>
        </div>
    {/if}
    <div class="flex flex-col space-y-4">
        <div class="mt-2 flex flex-col gap-6 lg:flex-row"> 
            <div class="min-w-0 flex-1 flex flex-col space-y-6">
                <Card.Root class="w-full border-0 shadow-xs h-full">
                    <Card.Header class="flex items-center gap-2">
                        <Sparkles strokeWidth={2.3} class="" />
                        <Card.Title class="text-xl font-bold tracking-tight">Request AI Intelligence</Card.Title>
                    </Card.Header>
                    <Card.Content class="relative">
                        <div>
                            <div class="text-sm">
                                {@html nl2br(request.ai_note)}
                            </div>
                        </div>
                        
                    </Card.Content>
                </Card.Root>
            </div>
            <aside class="flex w-full flex-col gap-6 lg:w-90">
                
                <Card.Root class="w-full border-0 shadow-xs bg-slate-800 h-full">
                    <Card.Header>
                        <Card.Title class="text-xl font-bold tracking-tight text-slate-50">
                            Request Risk Assessment
                        </Card.Title>
                    </Card.Header>
                    <Card.Content class="relative text-slate-50">
                        <div class="mb-8">
                            <div class="flex items-center justify-between gap-2 mb-3">
                                <span class="font-bold">Request Risk</span>
                                <StatusBadge status={request.ai_risk_rating} class="uppercase text-xs" />
                            </div>
                            <div>
                                <Progress 
                                    value={requestRiskProgress} 
                                    innerClass={requestRiskProgressColor} 
                                    class="bg-slate-700" 
                                />
                            </div>
                        </div>
                        <div class="mb-8">
                            <div class="flex items-center justify-between gap-2 mb-3">
                                <span class="font-bold">Approver Risk</span>
                                <StatusBadge status={request.approver_risk_rating} class="uppercase text-xs" />
                            </div>
                            <div>
                                <Progress 
                                    value={approverRiskProgress} 
                                    innerClass={approverRiskProgressColor} 
                                    class="bg-slate-700" 
                                />
                            </div>
                        </div>
                        <div>
                            <div class="flex items-center justify-between gap-2 mb-3">
                                <span class="font-bold">Sensitive Data Access</span>
                                {#if request.is_access_sensitive_data}
                                    <StatusBadge status="yes" class="uppercase text-xs bg-red-100 border-red-500 text-red-500" />
                                {:else}
                                    <StatusBadge status="no" class="uppercase text-xs bg-green-100 border-green-500 text-green-500" />
                                {/if}
                            </div>
                        </div>
                    </Card.Content>
                </Card.Root>

            </aside>
        </div>
    </div>
{/snippet}

{#snippet queryAudit()}
    <Card.Root class="w-full border-0 shadow-xs">
        <Card.Header class="flex items-center gap-2">
            <Logs strokeWidth={2.3} class="" />
            <Card.Title class="text-xl font-bold tracking-tight">Query Audit</Card.Title>
        </Card.Header>
        <Card.Content class="relative">
            {#if audits?.length > 0}
                <Item.Group>
                    {#each audits as audit, index (audit.attributes.id)}
                        <Item.Root>
                            <Item.Content class="gap-1.5 font-mono">
                                <Item.Title class="font-normal">
                                    {audit.attributes.query}
                                </Item.Title>
                                <Item.Description class="text-xs">
                                    {#if audit.attributes.count > 1}
                                        First: {shortDateTime(audit.attributes.first_timestamp)} • Last: {shortDateTime(
                                            audit.attributes.last_timestamp
                                        )} • Count: {audit.attributes.count} • Command Type: {capitalizeWords(
                                            audit.attributes.command_type
                                        )}
                                    {:else}
                                        Timestamp: {shortDateTime(audit.attributes.first_timestamp)} • Command Type: {capitalizeWords(
                                            audit.attributes.command_type
                                        )}
                                    {/if}
                                </Item.Description>
                            </Item.Content>
                        </Item.Root>
                        {#if index !== audits.length - 1}
                            <Item.Separator />
                        {/if}
                    {/each}
                </Item.Group>
            {/if}
        </Card.Content>
    </Card.Root>
{/snippet}