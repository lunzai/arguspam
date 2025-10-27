<script lang="ts">
	import * as Card from '$ui/card';
	import * as DL from '$components/description-list';
	import { relativeDateTime, shortDateTimeRange } from '$utils/date';
	import * as Progress from '$components/vertical-progress';
	import {
		Check,
		Bot,
		LucideClipboardCheck,
		X,
		ClipboardX,
		MonitorX,
		SquareTerminal,
		Play
	} from '@lucide/svelte';
	import type { Session } from '$models/session';

	interface Props {
		model: Session;
	}

	let { model }: Props = $props();

	const isTerminated = $derived(model.status == 'terminated');
	const isCancelled = $derived(model.status == 'cancelled');
	const isExpired = $derived(model.status == 'expired');
	const isEnded = $derived(model.status == 'ended');
	const isScheduled = $derived(model.status == 'scheduled');
	const hasEnded = $derived(isEnded || isTerminated || isCancelled || isExpired);
	const hasStart = $derived(model.started_at !== null);
	const isExpiredOrCancelled = $derived(isExpired || isCancelled);
</script>

<Card.Root class="w-full">
	<Card.Content class="">
		<div class="">
			<Progress.Root className="pl-0 ml-3">
				<Progress.Row
					icon={Check}
					title="Scheduled"
					description={relativeDateTime(model.created_at, false)}
					color="green"
				/>

				{#if isScheduled}
					<Progress.Row
						icon={Play}
						title="To Start"
						description={relativeDateTime(model.created_at, false)}
						color="blue"
					/>
				{/if}

				{#if hasStart}
					<Progress.Row
						icon={Check}
						title="Started"
						description={relativeDateTime(model.created_at, false)}
						color="green"
					/>
				{/if}

				{#if !isTerminated}
					<Progress.Row
						icon={Bot}
						title={model.end_datetime ? 'Ended' : 'To End'}
						description={model.end_datetime
							? relativeDateTime(model.end_datetime, false)
							: relativeDateTime(model.scheduled_end_datetime, false) + ' remaining'}
						color={model.end_datetime ? 'green' : model.start_datetime ? 'blue' : 'gray'}
						disabled={isExpiredOrCancelled}
					/>
				{/if}

				<!-- {#if hasStart && !hasEnded}
					<Progress.Row
						icon={MonitorX}
						title="To End"
						description={relativeDateTime(model.scheduled_end_datetime, false)}
						color="blue"
					/>
				{/if} -->

				<!-- {#if isEnded}
					<Progress.Row
						icon={MonitorX}
						title="Ended"
						description={relativeDateTime(model.ended_at, false)}
						color="green"
					/>
				{/if} -->

				{#if isTerminated}
					<Progress.Row
						icon={MonitorX}
						title="Terminated"
						description={relativeDateTime(model.terminated_at, false)}
						color="yellow"
					/>
				{/if}

				{#if isCancelled}
					<Progress.Row
						icon={ClipboardX}
						title="Cancelled"
						description={relativeDateTime(model.cancelled_at, false)}
						color="gray"
					/>
				{/if}

				{#if isExpired}
					<Progress.Row
						icon={ClipboardX}
						title="Expired"
						description={relativeDateTime(model.expired_at, false)}
						color="gray"
					/>
				{/if}

				<Progress.Row
					icon={Bot}
					title={model.ai_reviewed_at ? 'AI Audited' : 'AI Audit'}
					description={isExpiredOrCancelled ? '-' : relativeDateTime(model.ai_reviewed_at, false)}
					color={model.ai_reviewed_at ? 'green' : 'gray'}
					disabled={isExpiredOrCancelled}
				/>
			</Progress.Root>
		</div>
	</Card.Content>
</Card.Root>
