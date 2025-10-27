<script lang="ts">
	import * as Card from '$ui/card';
	import * as DL from '$components/description-list';
	import { AssetToolTips } from '$components/tooltip';
	import { getLocalTimeZone } from '@internationalized/date';
	import { formatDistance } from 'date-fns';
	import { ucFirst } from '$utils/string';
	import { nl2br } from '$utils/string';
	import { RequestScopeToolTips } from '$components/tooltip';
	import { relativeDateTime, shortDateTime, shortDateTimeRange } from '$utils/date';
	import { Button } from '$ui/button';
	import { ChevronDown, ClipboardCopy, ClipboardCheck } from '@lucide/svelte';
	import { slide } from 'svelte/transition';
	import { StatusBadge } from '$components/badge';
	import Badge from '$lib/components/ui/badge/badge.svelte';

	let { model, asset, requester, request, approver, flags } = $props();

	let showDetails = $state(true);
	let showRequest = $state(true);
	let showAudit = $state(true);

	const isAuditsAvailable = $derived(model.ai_reviewed_at !== null);
</script>

<div class="flex flex-col gap-6">
	<Card.Root class="w-full">
		<Card.Header
			onclick={() => (showDetails = !showDetails)}
			class="transition-all duration-200 hover:cursor-pointer"
		>
			<Card.Title class="text-lg">Session Details</Card.Title>
			<!-- <Card.Description>Session's details.</Card.Description> -->
			<Card.Action>
				<Button
					variant="outline"
					class="transition-all duration-200 hover:cursor-pointer hover:bg-blue-50 hover:text-blue-500"
				>
					<ChevronDown
						class="h-4 w-4 {showDetails ? 'rotate-180' : ''} transition-all duration-200"
					/>
				</Button>
			</Card.Action>
		</Card.Header>
		{#if showDetails}
			<div transition:slide={{ duration: 200 }}>
				<Card.Content class="space-y-4">
					<DL.Root divider={null}>
						<DL.Row>
							<DL.Label>Asset</DL.Label>
							<DL.Content>
								<AssetToolTips {asset} />
							</DL.Content>
						</DL.Row>
						<DL.Row>
							<DL.Label>{model.start_datetime ? 'Started At' : 'Scheduled Start'}</DL.Label>
							<DL.Content>
								{shortDateTime(model.start_datetime || model.scheduled_start_datetime)} ({getLocalTimeZone()})
							</DL.Content>
						</DL.Row>
						<DL.Row>
							<DL.Label>{model.end_datetime ? 'Ended At' : 'Scheduled End'}</DL.Label>
							<DL.Content>
								{shortDateTime(model.end_datetime || model.scheduled_end_datetime)} ({getLocalTimeZone()})
							</DL.Content>
						</DL.Row>
						<DL.Row>
							<DL.Label>{model.end_datetime ? 'Actual Duration' : 'Requested Duration'}</DL.Label>
							<DL.Content>
								{model.end_datetime
									? formatDistance(model.start_datetime, model.end_datetime)
									: formatDistance(model.scheduled_start_datetime, model.scheduled_end_datetime)}
							</DL.Content>
						</DL.Row>
						<DL.Row>
							<DL.Label>
								Scope
								<RequestScopeToolTips />
							</DL.Label>
							<DL.Content>
								{ucFirst(request.scope)}
							</DL.Content>
						</DL.Row>
					</DL.Root>
				</Card.Content>
			</div>
		{/if}
	</Card.Root>

	{#if isAuditsAvailable}
		<Card.Root class="w-full">
			<Card.Header
				onclick={() => (showAudit = !showAudit)}
				class="transition-all duration-200 hover:cursor-pointer"
			>
				<Card.Title class="text-lg">AI Session Audit</Card.Title>
				<!-- <Card.Description>Session's AI audit details.</Card.Description> -->
				<Card.Action>
					<Button
						variant="outline"
						class="transition-all duration-200 hover:cursor-pointer hover:bg-blue-50 hover:text-blue-500"
					>
						<ChevronDown
							class="h-4 w-4 {showAudit ? 'rotate-180' : ''} transition-all duration-200"
						/>
					</Button>
				</Card.Action>
			</Card.Header>
			{#if showAudit}
				<div transition:slide={{ duration: 200 }}>
					<Card.Content class="space-y-4">
						<DL.Root divider={null}>
							<DL.Row>
								<DL.Label>Reviewed At</DL.Label>
								<DL.Content>
									{relativeDateTime(model.ai_reviewed_at)}
								</DL.Content>
							</DL.Row>
							{#if model.session_activity_risk}
								<DL.Row>
									<DL.Label>Session Activities Risk</DL.Label>
									<DL.Content>
										<StatusBadge status={model.session_activity_risk} class="text-sm" />
									</DL.Content>
								</DL.Row>
							{/if}
							{#if model.deviation_risk}
								<DL.Row>
									<DL.Label>Deviation Risk</DL.Label>
									<DL.Content>
										<StatusBadge status={model.deviation_risk} class="text-sm" />
									</DL.Content>
								</DL.Row>
							{/if}
							{#if model.overall_risk}
								<DL.Row>
									<DL.Label>Overall Risk</DL.Label>
									<DL.Content>
										<StatusBadge status={model.overall_risk} class="text-sm" />
									</DL.Content>
								</DL.Row>
							{/if}
							{#if model.human_audit_required !== null}
								<DL.Row>
									<DL.Label>Human Audit</DL.Label>
									<DL.Content>
										<StatusBadge
											status={model.human_audit_required ? 'required' : 'not required'}
											class="text-sm"
										/>
										(Confidence: {model.human_audit_confidence})
									</DL.Content>
								</DL.Row>
							{/if}
							{#if flags?.length > 0}
								<DL.Row>
									<DL.Label>Flags</DL.Label>
									<DL.Content>
										<div class="flex gap-2">
											{#each flags as flag, i}
												<Badge variant="destructive" class="text-sm capitalize">
													{flag.attributes.flag}
												</Badge>
											{/each}
										</div>
									</DL.Content>
								</DL.Row>
							{/if}
							<DL.Row>
								<DL.Label>AI Note</DL.Label>
								<DL.Content>
									{@html nl2br(model.ai_note)}
								</DL.Content>
							</DL.Row>
						</DL.Root>
					</Card.Content>
				</div>
			{/if}
		</Card.Root>
	{/if}

	<Card.Root class="w-full">
		<Card.Header
			onclick={() => (showRequest = !showRequest)}
			class="transition-all duration-200 hover:cursor-pointer"
		>
			<Card.Title class="text-lg">Request Details</Card.Title>
			<!-- <Card.Description>Session's details.</Card.Description> -->
			<Card.Action>
				<Button
					variant="outline"
					class="transition-all duration-200 hover:cursor-pointer hover:bg-blue-50 hover:text-blue-500"
				>
					<ChevronDown
						class="h-4 w-4 {showRequest ? 'rotate-180' : ''} transition-all duration-200"
					/>
				</Button>
			</Card.Action>
		</Card.Header>
		{#if showRequest}
			<div transition:slide={{ duration: 200 }}>
				<Card.Content class="space-y-4">
					<DL.Root divider={null}>
						<DL.Row>
							<DL.Label>Requested Period</DL.Label>
							<DL.Content>
								{shortDateTimeRange(request.start_datetime, request.end_datetime)} ({getLocalTimeZone()})
							</DL.Content>
						</DL.Row>
						<DL.Row>
							<DL.Label>Duration</DL.Label>
							<DL.Content>{formatDistance(request.start_datetime, request.end_datetime)}</DL.Content
							>
						</DL.Row>
						<DL.Row>
							<DL.Label>
								Scope
								<RequestScopeToolTips />
							</DL.Label>
							<DL.Content>
								{ucFirst(request.scope)}
							</DL.Content>
						</DL.Row>
						<DL.Row>
							<DL.Label>Reason</DL.Label>
							<DL.Content>{@html nl2br(request.reason)}</DL.Content>
						</DL.Row>
						<DL.Row>
							<DL.Label>Intended Query</DL.Label>
							<DL.Content>{@html nl2br(request.intended_query)}</DL.Content>
						</DL.Row>
						{#if request.sensitive_data_note}
							<DL.Row>
								<DL.Label>Sensitive Data Note</DL.Label>
								<DL.Content>{@html nl2br(request.sensitive_data_note)}</DL.Content>
							</DL.Row>
						{/if}
						{#if request.ai_risk_rating}
							<DL.Row>
								<DL.Label>AI Risk Rating</DL.Label>
								<DL.Content>
									<StatusBadge status={request.ai_risk_rating} class="text-sm" />
								</DL.Content>
							</DL.Row>
						{/if}
						{#if request.ai_note}
							<DL.Row>
								<DL.Label>AI Note</DL.Label>
								<DL.Content>
									{@html nl2br(request.ai_note)}
								</DL.Content>
							</DL.Row>
						{/if}
						{#if request.approver_note}
							<DL.Row>
								<DL.Label>Approver Note</DL.Label>
								<DL.Content>
									{@html nl2br(request.approver_note)}
								</DL.Content>
							</DL.Row>
						{/if}
					</DL.Root>
					<div>
						<Button
							variant="outline"
							class="transition-all duration-200 hover:cursor-pointer hover:bg-blue-50 hover:text-blue-500"
							href={`/requests/${request.id}`}
						>
							Go To Request
						</Button>
					</div>
					<div class="flex flex-col gap-3 rounded-md bg-gray-50 p-6 text-sm text-gray-500">
						<div class="flex items-center gap-2">
							<ClipboardCopy class="h-4 w-4" /> Requested By
							<span class="font-medium">{requester.name} ({requester.email})</span>
							at {shortDateTime(request.created_at)} ({getLocalTimeZone()}).
						</div>
						<div class="flex items-center gap-2">
							<ClipboardCheck class="h-4 w-4" /> Approved By
							<span class="font-medium">{approver.name} ({approver.email})</span>
							at {shortDateTime(request.approved_at)} ({getLocalTimeZone()}).
						</div>
					</div>
				</Card.Content>
			</div>
		{/if}
	</Card.Root>
</div>
