<script lang="ts">
	import { Input } from '$ui/input';
	import * as Select from '$ui/select';
	import * as Form from '$ui/form';
	import * as Dialog from '$ui/dialog';
	import { superForm } from 'sveltekit-superforms';
	import { zod4Client } from 'sveltekit-superforms/adapters';
	import { toast } from 'svelte-sonner';
	import { capitalizeWords } from '$utils/string';
	import { Button } from '$ui/button';
	import type { Request } from '$models/request';
	import type { Asset } from '$models/asset';
	import Loader from '$components/loader.svelte';
	import { Calendar } from '$ui/calendar';
	import * as Popover from '$ui/popover';
	import { getLocalTimeZone, type CalendarDate } from '@internationalized/date';
	import { Database, Network, ChevronDown, CircleHelp } from '@lucide/svelte';
	import { Separator } from '$ui/separator';
	import { RequesterSchema } from '$lib/validations/request';
	import { Textarea } from '$ui/textarea';
	import { Switch } from '$ui/switch';
	import * as Tooltip from '$lib/components/ui/tooltip/index.js';
	import { slide } from 'svelte/transition';
	import { untrack } from 'svelte';

	interface Props {
		isOpen: boolean;
		asset: Asset;
		data: any;
		onSuccess: (data: Request) => Promise<void>;
	}

	let {
		isOpen = $bindable(false),
		asset = $bindable(),
		data = $bindable(),
		onSuccess = async (data: Request) => {}
	}: Props = $props();

	const form = superForm(data, {
		validators: zod4Client(RequesterSchema),
		delayMs: 100,
		async onUpdate({ form, result }) {
			if (!form.valid) {
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
	let fromDate = $state<CalendarDate | undefined>(undefined);
	let fromTime = $state<string | undefined>(undefined);
	let toDate = $state<CalendarDate | undefined>(undefined);
	let toTime = $state<string | undefined>(undefined);

	function handleCancel() {
		isOpen = false;
	}

	$effect(() => {
		if (asset?.id) {
			resetForm();
			$formData.asset_id = asset?.id;
			$formData.org_id = asset?.org_id;
		}
	});

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

	function resetForm() {
		fromCalendarIsOpen = false;
		toCalendarIsOpen = false;
		fromDate = undefined;
		toDate = undefined;
		fromTime = undefined;
		toTime = undefined;
		reset();
	}
</script>

<Dialog.Root bind:open={isOpen}>
	<Dialog.Content
		class="max-h-[90vh] overflow-y-auto sm:max-w-2xl"
		interactOutsideBehavior="ignore"
	>
		{#if $submitting}
			<Loader show={$submitting} />
		{/if}
		<form class="min-w-0 space-y-6" method="POST" use:enhance>
			<input type="hidden" name="asset_id" bind:value={$formData.asset_id} />
			<input type="hidden" name="org_id" bind:value={$formData.org_id} />
			<Dialog.Header>
				<Dialog.Title>Add Request</Dialog.Title>
				<div class="flex items-center justify-between gap-x-3 bg-gray-50 px-6 py-4 text-sm">
					<div class="flex min-w-0 flex-col gap-1.5">
						<span class="truncate font-semibold">{asset.name}</span>
						{#if asset.description}
							<span class="text-muted-foreground truncate">
								{asset.description}
							</span>
						{/if}
						<div class="flex min-h-6 items-center gap-1.5 truncate text-sm/5 text-gray-500">
							<span class="flex items-center gap-2">
								<Database class="h-3 w-3" />
								{asset.dbms}
							</span>
							<Separator class="border-gray-200" orientation="vertical" />
							<span class="flex items-center gap-2">
								<Network class="h-3 w-3" />
								{asset.host}:{asset.port}
							</span>
						</div>
					</div>
				</div>
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
				<Form.Field {form} name="reason">
					<Form.Control>
						<Form.Label>Reason</Form.Label>
						<Textarea
							name="reason"
							bind:value={$formData.reason}
							disabled={$submitting}
							class="min-h-18"
						/>
					</Form.Control>
					<Form.FieldErrors />
				</Form.Field>

				<Form.Field {form} name="intended_query">
					<Form.Control>
						<Form.Label>Intended Query</Form.Label>
						<Textarea
							name="intended_query"
							bind:value={$formData.intended_query}
							disabled={$submitting}
							class="min-h-18"
						/>
					</Form.Control>
					<Form.FieldErrors />
				</Form.Field>
				<Form.Field {form} name="scope">
					<Form.Control>
						<Form.Label>
							Scope
							<Tooltip.Root>
								<Tooltip.Trigger>
									<CircleHelp class="size-3" />
								</Tooltip.Trigger>
								<Tooltip.Content>
									<div class="">
										<ul>
											<li>
												<span class="font-bold">ReadOnly:</span> View data only (SELECT queries)
											</li>
											<li>
												<span class="font-bold">ReadWrite:</span> View and modify data (SELECT, INSERT,
												UPDATE, DELETE)
											</li>
											<li>
												<span class="font-bold">DDL:</span> Manage database structure (ReadWrite + ALTER,
												DROP tables/indexes)
											</li>
											<li>
												<span class="font-bold">All:</span> Full database access (all operations and
												administrative functions)
											</li>
										</ul>
									</div>
								</Tooltip.Content>
							</Tooltip.Root>
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
								<Select.Item value="All" label="All" />
							</Select.Content>
						</Select.Root>
					</Form.Control>
					<Form.FieldErrors />
				</Form.Field>

				<Form.Field {form} name="is_access_sensitive_data">
					<Form.Control>
						<div class="flex gap-2">
							<Switch
								name="is_access_sensitive_data"
								bind:checked={$formData.is_access_sensitive_data}
								disabled={$submitting}
							/>
							<Form.Label>Access Sensitive Data</Form.Label>
						</div>
					</Form.Control>
					<Form.FieldErrors />
				</Form.Field>

				{#if $formData.is_access_sensitive_data}
					<div transition:slide={{ duration: 200 }}>
						<Form.Field {form} name="sensitive_data_note">
							<Form.Control>
								<Form.Label>Sensitive Data Note</Form.Label>
								<Textarea
									name="sensitive_data_note"
									bind:value={$formData.sensitive_data_note}
									disabled={$submitting}
									class="min-h-18"
								/>
							</Form.Control>
							<Form.FieldErrors />
						</Form.Field>
					</div>
				{/if}
			</div>
			<Dialog.Footer>
				<Button variant="outline" onclick={handleCancel}>Cancel</Button>
				<Button variant="default" type="submit">Save</Button>
			</Dialog.Footer>
		</form>
	</Dialog.Content>
</Dialog.Root>
