<script lang="ts">
	import { Input } from '$ui/input';
	import * as Select from '$ui/select';
	import * as Form from '$ui/form';
	import * as Dialog from '$ui/dialog';
	import { superForm } from 'sveltekit-superforms';
	import { zodClient } from 'sveltekit-superforms/adapters';
	import { toast } from 'svelte-sonner';
	import { capitalizeWords } from '$utils/string';
	import { Button } from '$ui/button';
	import type { Request } from '$models/request';
	import Loader from '$components/loader.svelte';
	import { Calendar } from '$ui/calendar';
	import * as Popover from '$ui/popover';
	import {
		CalendarDate,
		getLocalTimeZone,
		parseAbsoluteToLocal,
		parseDate,
		parseTime,
		fromDate as fd
	} from '@internationalized/date';
	import { ChevronDown } from '@lucide/svelte';
	import { ApproveSchema } from '$lib/validations/request';
	import { Textarea } from '$ui/textarea';
	import { RequestScopeToolTips, RiskRatingToolTips } from '$components/tooltip';

	interface Props {
		isOpen: boolean;
		data: any;
		onSuccess: (data: Request) => Promise<void>;
	}

	let {
		isOpen = $bindable(false),
		data = $bindable(),
		onSuccess = async (data: Request) => {}
	}: Props = $props();

	const form = superForm(data, {
		validators: zodClient(ApproveSchema),
		delayMs: 100,
		async onUpdate({ form, result }) {
			if (!form.valid && Object.keys(form.errors).length > 0) {
				return;
			}
			if (result.type === 'success') {
				toast.success(result.data.message);
				await onSuccess(result.data.model as Request);
				isOpen = false;
			} else if (result.type === 'failure') {
				toast.error(result.data.error);
			}
		}
	});

	const { form: formData, enhance, submitting, reset, errors } = form;

	let fromCalendarIsOpen = $state(false);
	let toCalendarIsOpen = $state(false);
	let zonedStartDatetime = $state(
		parseAbsoluteToLocal($formData.start_datetime.toISOString()).toString()
	);
	let zonedEndDatetime = $state(
		parseAbsoluteToLocal($formData.end_datetime.toISOString()).toString()
	);
	let fromDate = $state<CalendarDate | undefined>(parseDate(zonedStartDatetime.split('T')[0]));
	let fromTime = $state<string | undefined>(
		parseTime(zonedStartDatetime.split('T')[1].split('+')[0]).toString()
	);
	let toDate = $state<CalendarDate | undefined>(parseDate(zonedEndDatetime.split('T')[0]));
	let toTime = $state<string | undefined>(
		parseTime(zonedEndDatetime.split('T')[1].split('+')[0]).toString()
	);

	function handleCancel() {
		isOpen = false;
		fromCalendarIsOpen = false;
		toCalendarIsOpen = false;
		reset();
	}

	$effect(() => {
		if (fromDate && fromTime) {
			const startDatetime = fromDate.toDate(getLocalTimeZone());
			startDatetime.setHours(parseInt(fromTime.split(':')[0]), parseInt(fromTime.split(':')[1]));
			$formData.start_datetime = startDatetime;
		}
		if (toDate && toTime) {
			const endDatetime = toDate.toDate(getLocalTimeZone());
			endDatetime.setHours(parseInt(toTime.split(':')[0]), parseInt(toTime.split(':')[1]));
			$formData.end_datetime = endDatetime;
		}
	});
</script>

<Dialog.Root bind:open={isOpen}>
	<Dialog.Content
		class="max-h-[90vh] overflow-y-auto sm:max-w-2xl"
		interactOutsideBehavior="ignore"
	>
		{#if $submitting}
			<Loader show={$submitting} />
		{/if}
		<form class="min-w-0 space-y-6" method="POST" action="?/approve" use:enhance>
			<Dialog.Header>
				<Dialog.Title>Approve Request</Dialog.Title>
			</Dialog.Header>
			<div class="space-y-6">
				<div class="grid gap-6 md:grid-cols-2">
					<Form.Field {form} name="start_datetime">
						<Form.Control>
							<Form.Label>Start</Form.Label>
							<div class="grid grid-cols-2 gap-2">
								<Popover.Root bind:open={fromCalendarIsOpen}>
									<Popover.Trigger id="start_datetime">
										{#snippet child({ props })}
											<Button
												{...props}
												variant="outline"
												class="w-full justify-between font-normal"
											>
												{fromDate
													? fromDate.toDate(getLocalTimeZone()).toLocaleDateString()
													: 'Select date'}
												<ChevronDown class="size-4" />
											</Button>
										{/snippet}
									</Popover.Trigger>
									<Popover.Content>
										<Calendar
											type="single"
											captionLayout="dropdown"
											bind:value={fromDate}
											onValueChange={() => {
												fromCalendarIsOpen = false;
											}}
										/>
									</Popover.Content>
								</Popover.Root>
								<Input type="time" bind:value={fromTime} disabled={$submitting} class="block!" />
							</div>
							<input type="hidden" name="start_datetime" bind:value={$formData.start_datetime} />
						</Form.Control>
						<Form.FieldErrors />
					</Form.Field>

					<Form.Field {form} name="end_datetime">
						<Form.Control>
							<Form.Label>End</Form.Label>
							<div class="grid grid-cols-2 gap-2">
								<Popover.Root bind:open={toCalendarIsOpen}>
									<Popover.Trigger id="end_datetime">
										{#snippet child({ props })}
											<Button
												{...props}
												variant="outline"
												class="w-full justify-between font-normal"
											>
												{toDate
													? toDate.toDate(getLocalTimeZone()).toLocaleDateString()
													: 'Select date'}
												<ChevronDown class="size-4" />
											</Button>
										{/snippet}
									</Popover.Trigger>
									<Popover.Content>
										<Calendar
											type="single"
											captionLayout="dropdown"
											bind:value={toDate}
											onValueChange={() => {
												toCalendarIsOpen = false;
											}}
										/>
									</Popover.Content>
								</Popover.Root>
								<Input type="time" bind:value={toTime} disabled={$submitting} class="block!" />
							</div>
							<input type="hidden" name="end_datetime" bind:value={$formData.end_datetime} />
						</Form.Control>
						<Form.FieldErrors />
						{#if $errors.duration}
							<p class="text-destructive text-sm font-medium">{$errors.duration}</p>
						{/if}
					</Form.Field>
				</div>

				<Form.Field {form} name="scope">
					<Form.Control>
						<Form.Label>
							Scope
							<RequestScopeToolTips />
						</Form.Label>
						<Select.Root
							name="scope"
							type="single"
							bind:value={$formData.scope}
							disabled={$submitting}
						>
							<Select.Trigger class="w-full">
								{$formData.scope ? capitalizeWords($formData.scope) : 'Select Scope'}
							</Select.Trigger>
							<Select.Content>
								<Select.Item value="ReadOnly" label="ReadOnly" />
								<Select.Item value="ReadWrite" label="ReadWrite" />
								<Select.Item value="DDL" label="DDL" />
								<Select.Item value="DML" label="DML" />
								<Select.Item value="All" label="All" />
							</Select.Content>
						</Select.Root>
					</Form.Control>
					<Form.FieldErrors />
				</Form.Field>

				<Form.Field {form} name="approver_risk_rating">
					<Form.Control>
						<Form.Label>
							Approver Risk Rating
							<RiskRatingToolTips />
						</Form.Label>
						<Select.Root
							name="approver_risk_rating"
							type="single"
							bind:value={$formData.approver_risk_rating}
							disabled={$submitting}
						>
							<Select.Trigger class="w-full">
								{$formData.approver_risk_rating
									? capitalizeWords($formData.approver_risk_rating)
									: 'Select Risk Rating'}
							</Select.Trigger>
							<Select.Content>
								<Select.Item value="low" label="Low" />
								<Select.Item value="medium" label="Medium" />
								<Select.Item value="high" label="High" />
								<Select.Item value="critical" label="Critical" />
							</Select.Content>
						</Select.Root>
					</Form.Control>
					<Form.FieldErrors />
				</Form.Field>

				<Form.Field {form} name="approver_note">
					<Form.Control>
						<Form.Label>Approver Note</Form.Label>
						<Textarea
							name="approver_note"
							bind:value={$formData.approver_note}
							disabled={$submitting}
							class="min-h-18"
						/>
					</Form.Control>
					<Form.FieldErrors />
				</Form.Field>
			</div>
			<Dialog.Footer>
				<Button variant="outline" onclick={handleCancel}>Cancel</Button>
				<Button variant="default" type="submit">Approve</Button>
			</Dialog.Footer>
		</form>
	</Dialog.Content>
</Dialog.Root>
