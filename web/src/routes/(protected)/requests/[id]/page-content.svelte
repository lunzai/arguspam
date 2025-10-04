<script lang="ts">
	import * as Card from '$ui/card';
	import { ClockFading, Ban } from '@lucide/svelte';
	import * as DL from '$components/description-list';
	import { relativeDateTime, shortDateTime } from '$utils/date';
	import type { ApiRequestResource } from '$resources/request';
	import type { Request } from '$models/request';
	import type { Asset } from '$models/asset';
	import { ucFirst, nl2br } from '$lib/utils/string';
	import { getLocalTimeZone } from '@internationalized/date';
	import { formatDistance } from 'date-fns';
	import { StatusBadge } from '$components/badge';
	import * as Alert from '$ui/alert';
	import { RequestScopeToolTips, RiskRatingToolTips, AssetToolTips } from '$components/tooltip';
	import type { User } from '$models/user';

	let { data } = $props();
	const modelResource = $derived(data.model as ApiRequestResource);
	const model = $derived(modelResource.data.attributes as Request);
	const asset = $derived(modelResource.data.relationships?.asset?.attributes as Asset);
	const approver = $derived(modelResource.data.relationships?.approver?.attributes as User);
	const rejecter = $derived(modelResource.data.relationships?.rejecter?.attributes as User);
</script>

<div class="flex flex-col gap-6">
	{#if model.status == 'expired'}
		<Alert.Root class="text-muted-foreground border-gray-200 bg-gray-50">
			<ClockFading />
			<Alert.Title>
				The request for {asset.name} has expired without approval or rejection.
			</Alert.Title>
		</Alert.Root>
	{/if}
	{#if model.status == 'cancelled'}
		<Alert.Root class="text-muted-foreground border-gray-200 bg-gray-50">
			<Ban />
			<Alert.Title>
				The request for {asset.name} has been cancelled.
			</Alert.Title>
		</Alert.Root>
	{/if}

	<Card.Root class="w-full">
		<Card.Header>
			<Card.Title class="text-lg">Request Details</Card.Title>
			<Card.Description>View request details.</Card.Description>
		</Card.Header>
		<Card.Content>
			<DL.Root divider={null}>
				<DL.Row>
					<DL.Label>Asset</DL.Label>
					<DL.Content>
						<AssetToolTips {asset} />
					</DL.Content>
				</DL.Row>
				<DL.Row>
					<DL.Label>Start At</DL.Label>
					<DL.Content>
						{shortDateTime(model.start_datetime)} ({getLocalTimeZone()})
					</DL.Content>
				</DL.Row>
				<DL.Row>
					<DL.Label>End At</DL.Label>
					<DL.Content>
						{shortDateTime(model.end_datetime)} ({getLocalTimeZone()})
					</DL.Content>
				</DL.Row>
				<DL.Row>
					<DL.Label>Duration</DL.Label>
					<DL.Content>{formatDistance(model.start_datetime, model.end_datetime)}</DL.Content>
				</DL.Row>
				<DL.Row>
					<DL.Label>
						Scope
						<RequestScopeToolTips />
					</DL.Label>
					<DL.Content>
						{ucFirst(model.scope)}
					</DL.Content>
				</DL.Row>
				<DL.Row>
					<DL.Label>Reason</DL.Label>
					<DL.Content>{@html nl2br(model.reason)}</DL.Content>
				</DL.Row>
				<DL.Row>
					<DL.Label>Intended Query</DL.Label>
					<DL.Content>{@html nl2br(model.intended_query)}</DL.Content>
				</DL.Row>
				{#if model.sensitive_data_note}
					<DL.Row>
						<DL.Label>Sensitive Data Note</DL.Label>
						<DL.Content>{@html nl2br(model.sensitive_data_note)}</DL.Content>
					</DL.Row>
				{/if}
			</DL.Root>
		</Card.Content>
	</Card.Root>

	{#if model.status == 'approved' || model.status == 'rejected'}
		<Card.Root class="w-full">
			<Card.Header>
				<Card.Title class="text-lg">Approval Details</Card.Title>
				<Card.Description>View approval details.</Card.Description>
			</Card.Header>
			<Card.Content>
				<DL.Root divider={null}>
					<DL.Row>
						<DL.Label>Approval Status</DL.Label>
						<DL.Content>
							<StatusBadge bind:status={model.status} class="text-sm" />
						</DL.Content>
					</DL.Row>
					{#if model.approver_risk_rating}
						<DL.Row>
							<DL.Label
								>Approver Risk Rating
								<RiskRatingToolTips />
							</DL.Label>
							<DL.Content>
								<StatusBadge bind:status={model.approver_risk_rating} class="text-sm" />
							</DL.Content>
						</DL.Row>
					{/if}
					{#if model.approver_note}
						<DL.Row>
							<DL.Label>Approver Note</DL.Label>
							<DL.Content>{@html nl2br(model.approver_note)}</DL.Content>
						</DL.Row>
					{/if}
					{#if approver}
						<DL.Row>
							<DL.Label>Approved At</DL.Label>
							<DL.Content>{relativeDateTime(model.approved_at)}</DL.Content>
						</DL.Row>
						<DL.Row>
							<DL.Label>Approved By</DL.Label>
							<DL.Content>{approver.name} ({approver.email})</DL.Content>
						</DL.Row>
					{/if}
					{#if rejecter}
						<DL.Row>
							<DL.Label>Rejected At</DL.Label>
							<DL.Content>{relativeDateTime(model.rejected_at)}</DL.Content>
						</DL.Row>
						<DL.Row>
							<DL.Label>Rejected By</DL.Label>
							<DL.Content>{rejecter.name} ({rejecter.email})</DL.Content>
						</DL.Row>
					{/if}
				</DL.Root>
			</Card.Content>
		</Card.Root>
	{/if}

	{#if model.ai_note || model.ai_risk_rating}
		<Card.Root class="w-full">
			<Card.Header>
				<Card.Title class="text-lg">AI Evaluation</Card.Title>
				<Card.Description>View AI evaluation details.</Card.Description>
			</Card.Header>
			<Card.Content>
				<DL.Root divider={null}>
					{#if model.ai_risk_rating}
						<DL.Row>
							<DL.Label
								>AI Risk Rating
								<RiskRatingToolTips />
							</DL.Label>
							<DL.Content>
								<StatusBadge bind:status={model.ai_risk_rating} class="text-sm" />
							</DL.Content>
						</DL.Row>
					{/if}
					{#if model.ai_note}
						<DL.Row>
							<DL.Label>AI Note</DL.Label>
							<DL.Content>{@html nl2br(model.ai_note)}</DL.Content>
						</DL.Row>
					{/if}
				</DL.Root>
			</Card.Content>
		</Card.Root>
	{/if}
</div>
