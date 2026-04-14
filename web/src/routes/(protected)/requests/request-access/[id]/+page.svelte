<script lang="ts">
    import { PageTitle } from '$components/page-title';
    import * as Card from '$ui/card';
	import Button from '$lib/components/ui/button/button.svelte';
	import type { Asset } from '$lib/models/asset';
	import { DbmsBadge } from '$components/badge';
	import { CircleCheck, MessageSquareWarning } from '@lucide/svelte';
    import { superForm } from 'sveltekit-superforms';
    import { zod4Client } from 'sveltekit-superforms/adapters';
    import { RequesterSchema } from '$lib/validations/request';
    import { getLocalTimeZone, type CalendarDate } from '@internationalized/date';
    import Loader from '$components/loader.svelte';
    import * as Form from '$lib/components/ui/form';
    import * as Popover from '$lib/components/ui/popover';
    import { Input } from '$ui/input';
    import * as RadioGroup from '$lib/components/ui/radio-group';
    import { ChevronDown } from '@lucide/svelte';
    import { Textarea } from '$lib/components/ui/textarea';
    import { slide } from 'svelte/transition';
    import { Calendar } from '$ui/calendar';
    import { Switch } from '$ui/switch';
	import Label from '$lib/components/ui/label/label.svelte';
	import { toast } from 'svelte-sonner';
	import { goto } from '$app/navigation';

    let { data } = $props();
    const asset = $derived(data.asset.data.attributes as Asset);
    const form = superForm(data.form, {
        validators: zod4Client(RequesterSchema),
        delayMs: 100,
        async onUpdate({ form, result }) {
			if (!form.valid) {
				return;
			}
			if (result.type === 'success') {
				toast.success(result.data.message);
                reset();
				await goto(`/requests/${result.data.model.id}`);
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

<PageTitle
    title="Request Access"
    description="Submit a time-bound privileged access request for a database asset. AI risk scoring is applied automatically before approval."
>
    <Button
        variant="outline"
        class="bg-white"
        href="/requests/request-access"
    >Cancel</Button>
</PageTitle>

<form class="min-w-0 space-y-6" method="POST" use:enhance>
    <div class="flex flex-col space-y-4">
        <div class="mt-2 flex flex-col gap-6 lg:flex-row"> 
            <div class="min-w-0 flex-1">
                <Card.Root class="w-full border-0 shadow-xs">
                    <Card.Header>
                        <Card.Title class="text-xl font-bold tracking-tight">Access Details</Card.Title>
                        <Card.Description class="text-sm text-slate-500">Define the scope and duration of your session.</Card.Description>
                    </Card.Header>
                    <Card.Content class="relative">
                        <Loader show={$submitting} />
                        <div class="space-y-6 mt-4">
                            <div class="font-bold text-slate-500 uppercase text-sm tracking-wider">
                                Access Window
                            </div>
                            <div class="grid gap-6 md:grid-cols-2">
                                <Form.Field {form} name="start_datetime">
                                    <Form.Control>
                                        <Form.Label>Start Date & Time</Form.Label>
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
                                        <Form.Label>End Date & Time</Form.Label>
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

                            <div class="font-bold text-slate-500 uppercase text-sm tracking-wider">
                                Access Scope
                            </div>

                            <RadioGroup.Root name="scope" bind:value={$formData.scope} disabled={$submitting}>
                                <Label 
                                    class="items-start flex gap-4 border border-slate-100 rounded-md p-4 
                                    cursor-pointer hover:bg-slate-50 transition-all has-[[data-slot=radio-group-item][data-state=checked]]:bg-slate-50
                                    has-[[data-slot=radio-group-item][data-state=checked]]:border-slate-200"
                                    for="scope-readonly"
                                >
                                    <RadioGroup.Item value="ReadOnly" id="scope-readonly" class="data-[state=checked]:bg-slate-900" /> 
                                    <div>
                                        <span class="font-bold text-sm">Read Only</span>
                                        <div class="text-xs text-slate-500 mt-1">
                                            Query data without the ability to modify or delete records.
                                        </div>
                                    </div>
                                </Label>
                                <Label 
                                    class="items-start flex gap-4 border border-slate-100 rounded-md p-4 
                                    cursor-pointer hover:bg-slate-50 transition-all  has-[[data-slot=radio-group-item][data-state=checked]]:bg-slate-50
                                    has-[[data-slot=radio-group-item][data-state=checked]]:border-slate-200" 
                                    for="scope-readwrite"
                                >
                                    <RadioGroup.Item value="ReadWrite" id="scope-readwrite" class="data-[state=checked]:bg-slate-900" /> 
                                    <div>
                                        <span class="font-bold text-sm">Read Write</span>
                                        <div class="text-xs text-slate-500 mt-1">
                                            Full DML capabilities for application data management.
                                        </div>
                                    </div>
                                </Label>
                                <Label 
                                    class="items-start flex gap-4 border border-slate-100 rounded-md p-4 
                                    cursor-pointer hover:bg-slate-50 transition-all  has-[[data-slot=radio-group-item][data-state=checked]]:bg-slate-50
                                    has-[[data-slot=radio-group-item][data-state=checked]]:border-slate-200" 
                                    for="scope-ddl"
                                >
                                    <RadioGroup.Item value="DDL" id="scope-ddl" class="data-[state=checked]:bg-slate-900" /> 
                                    <div>
                                        <span class="font-bold text-sm">DDL</span>
                                        <div class="text-xs text-slate-500 mt-1">
                                            Schema modification permissions for migrations.
                                        </div>
                                    </div>
                                </Label>
                                <Label 
                                    class="items-start flex gap-4 border border-slate-100 rounded-md p-4 
                                    cursor-pointer hover:bg-slate-50 transition-all  has-[[data-slot=radio-group-item][data-state=checked]]:bg-slate-50
                                    has-[[data-slot=radio-group-item][data-state=checked]]:border-slate-200" 
                                    for="scope-all"
                                >
                                    <RadioGroup.Item value="All" id="scope-all" class="data-[state=checked]:bg-slate-900" /> 
                                    <div>
                                        <span class="font-bold text-sm">All</span>
                                        <div class="text-xs text-slate-500 mt-1">
                                            Superuser privileges including user management and config.
                                        </div>
                                    </div>
                                </Label>
                            </RadioGroup.Root>

                            <div class="font-bold text-slate-500 uppercase text-sm tracking-wider">
                                Access Purpose
                            </div>

                            <Form.Field {form} name="reason">
                                <Form.Control>
                                    <Form.Label>Reason</Form.Label>
                                    <Textarea
                                        name="reason"
                                        bind:value={$formData.reason}
                                        disabled={$submitting}
                                        class="min-h-20"
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
                                        class="min-h-20"
                                    />
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
                                                class="min-h-20"
                                            />
                                        </Form.Control>
                                        <Form.FieldErrors />
                                    </Form.Field>
                                </div>
                            {/if}
                        </div>
                    </Card.Content>
                    <Card.Footer>
                        <Button variant="default" size="lg" type="submit">Submit Request</Button>
                    </Card.Footer>
                </Card.Root>
            </div>
            <aside class="flex w-full flex-col gap-6 lg:w-90">
                <Card.Root class="w-full gap-0 -py-4 shadow-xs outline-0 border-0">
                    <Card.Header class="bg-slate-100 pt-4 pb-3 rounded-t-xl">
                        <Card.Title class="font-extrabold text-xs uppercase tracking-widest text-slate-500">Selected Asset</Card.Title>
                    </Card.Header>
                    <Card.Content class="bg-slate-50/50 py-6 rounded-b-xl">
                        <h3 class="font-bold mb-1">{asset.name}</h3>
                        <div class="font-mono text-slate-500 text-sm mb-2">{asset.host}:{asset.port}</div>
                        <div class="mb-3">
                            <DbmsBadge dbms={asset.dbms} class="font-bold" />
                        </div>
                        <div class="text-slate-500 text-sm">{asset.description}</div>
                    </Card.Content>
                </Card.Root>

                <Card.Root class="w-full bg-slate-800 border-0 shadow-xs">
                    <Card.Content class="text-slate-400">
                        <div class="flex items-center gap-2">
                            <MessageSquareWarning strokeWidth={2.5} />
                            <h3 class="font-bold">Secure Access Guide</h3>
                        </div>
                        <ul class="mt-6 space-y-2">
                            <li class="flex gap-3 leading-relaxed opacity-90 text-sm">
                                <CircleCheck class="size-4 shrink-0 mt-1" />
                                <span>Request the minimum window needed to complete your task.</span>
                            </li>
                            <li class="flex gap-3 leading-relaxed opacity-90 text-sm">
                                <CircleCheck class="size-4 shrink-0 mt-1" />
                                <span>Use Read-Only whenever possible for investigation.</span>
                            </li>
                            <li class="flex gap-3 leading-relaxed opacity-90 text-sm">
                                <CircleCheck class="size-4 shrink-0 mt-1" />
                                <span>All sessions are audited and recorded for compliance.</span>
                            </li>
                        </ul>
                    </Card.Content>
                </Card.Root>
            </aside>
        </div>
    </div>
</form>