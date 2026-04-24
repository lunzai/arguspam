<script lang="ts">
	import * as Card from '$ui/card';
	import * as DL from '$components/description-list';
	import { StatusBadge } from '$components/badge';
	import { AssetToolTips } from '$components/tooltip';
	import { relativeDateTime, shortDateTime, shortDateTimeRange } from '$utils/date';
	import { Separator } from '$ui/separator';
	import { Button } from '$ui/button';
	import {
		Play,
		ClipboardX,
		MonitorX,
		Eye,
		EyeClosed,
		KeyRound,
        MonitorPlay,
        Info,
        TriangleAlert,
		MonitorOff,
		Copy,
		Check
	} from '@lucide/svelte';
	import type { Session } from '$models/session';
	import type { SessionPermission } from '$resources/session';
	import type { Me, User } from '$models/user';
	import type { Asset } from '$models/asset';
	import type { Request } from '$models/request';
	import * as AlertDialog from '$ui/alert-dialog';
	import { enhance } from '$app/forms';
	import { toast } from 'svelte-sonner';
	import Loader from '$components/loader.svelte';
	import { invalidate } from '$app/navigation';
	import * as Field from '$ui/field';
    import * as InputGroup from '$ui/input-group';
    import { UseClipboard } from '$lib/utils/use-clipboard.svelte';
	import * as ButtonGroup from '$ui/button-group';

	interface Props {
		model: Session;
		permissions: SessionPermission;
		requester: User;
		asset: Asset;
		request: Request;
		approver: User;
		me: Me;
	}

	let { model, permissions, requester, asset, request, approver, me }: Props = $props();

    const hostclipboard = new UseClipboard();
    const portclipboard = new UseClipboard();
    const usernameclipboard = new UseClipboard();
    const passwordclipboard = new UseClipboard();
	const canStart = $derived(permissions.canStart && model.status == 'scheduled');
	const canCancel = $derived(permissions.canCancel && model.status == 'scheduled');
	const canEnd = $derived(permissions.canEnd && model.status == 'started');
	const canTerminate = $derived(
		permissions.canTerminate && model.status == 'started' && me.id != model.requester_id
	);
	const canRetrieveSecret = $derived(
		permissions.canRetrieveSecret && model.status == 'started' && me.id == model.requester_id
	);
	const showActions = $derived(canStart || canCancel || canEnd || canTerminate);

	let startDialogIsOpen = $state(false);
	let startDialogIsLoading = $state(false);
	let cancelDialogIsOpen = $state(false);
	let cancelDialogIsLoading = $state(false);
	let endDialogIsOpen = $state(false);
	let endDialogIsLoading = $state(false);
	let terminateDialogIsOpen = $state(false);
	let terminateDialogIsLoading = $state(false);
	let retrieveSecretDialogIsOpen = $state(false);
	let retrieveSecretDialogIsLoading = $state(false);
	let secret = $state({
		username: 'heanluen',
		password: 'password'
	});
	let showSecretPassword = $state(false);

	async function retrieveSecret() {
		retrieveSecretDialogIsLoading = true;
		try {
			const response = await fetch(`/api/secret/${model.id}`, { method: 'POST' });
			const data = await response.json();
			if (data.success) {
				secret = {
					username: data.data.username,
					password: data.data.password
				};
			} else {
				toast.error(data.error);
			}
		} catch (error) {
			toast.error('Failed to retrieve secret');
		} finally {
			retrieveSecretDialogIsLoading = false;
		}
	}
</script>

<AlertDialog.Root
	bind:open={retrieveSecretDialogIsOpen}
	onOpenChange={(open) => {
		if (!open) {
            showSecretPassword = false;
			secret = {
				username: '',
				password: ''
			};
		}
	}}
>
	<AlertDialog.Content>
		<AlertDialog.Header>
			<AlertDialog.Title>
                <div class="flex items-center gap-2.5">
                    <div class="bg-primary rounded p-2">
                        <KeyRound class="h-4 w-4 text-white" strokeWidth={2.5} />
                    </div>
                    <span>Reveal Credentials</span>
                </div>
            </AlertDialog.Title>
            <AlertDialog.Description class="pt-3">

                <div class="bg-red-50 rounded p-4 border border-red-200 text-red-500 text-sm">
                    <div class="flex items-center gap-3">
                        <div>
                            <TriangleAlert class="h-4 w-4 text-red-500" strokeWidth={2.5} />
                        </div>
                        <span>
                            These credentials are only valid for the duration of this session. Sharing these credentials is a severe violation of security policies.
                        </span>
                    </div>
                </div>

                <Field.Group class="mt-6">
                    <div class="grid grid-cols-5 gap-6">
                        <Field.Set class="col-span-3">
                            <div class="flex flex-col gap-2">
                                <Field.Label for="host">Host</Field.Label>
                                <InputGroup.Root class="bg-white  px-1 py-1 h-auto shadow-none">
                                    <InputGroup.Input id="host" readonly type="text" bind:value={asset.host} />
                                    <InputGroup.Button
                                        aria-label="Copy"
                                        title="Copy"
                                        size="icon-xs"
                                        onclick={() => hostclipboard.copy(asset.host)}
                                    >
                                        {#if hostclipboard.copied}
                                            <Check />
                                        {:else}
                                            <Copy />
                                        {/if}
                                    </InputGroup.Button>
                                </InputGroup.Root>
                            </div>
                        </Field.Set>
                        <Field.Set class="col-span-2">
                            <div class="flex flex-col gap-2">
                                <Field.Label for="port">Port</Field.Label>
                                <InputGroup.Root class="bg-white  px-1 py-1 h-auto shadow-none">
                                    <InputGroup.Input id="port" readonly type="text" bind:value={asset.port} />
                                    <InputGroup.Button
                                        aria-label="Copy"
                                        title="Copy"
                                        size="icon-xs"
                                        onclick={() => portclipboard.copy(asset.port)}
                                    >
                                        {#if portclipboard.copied}
                                            <Check />
                                        {:else}
                                            <Copy />
                                        {/if}
                                    </InputGroup.Button>
                                </InputGroup.Root>
                            </div>
                        </Field.Set>
                    </div>
                    <Field.Set>
                        <div class="flex flex-col gap-2">
                            <Field.Label for="username">Username</Field.Label>
                            <InputGroup.Root class="bg-white  px-1 py-1 h-auto shadow-none">
                                <InputGroup.Input id="username" readonly type="text" bind:value={secret.username} />
                                <InputGroup.Button
                                    aria-label="Copy"
                                    title="Copy"
                                    size="icon-xs"
                                    onclick={() => usernameclipboard.copy(secret.username)}
                                >
                                    {#if usernameclipboard.copied}
                                        <Check />
                                    {:else}
                                        <Copy />
                                    {/if}
                                </InputGroup.Button>
                            </InputGroup.Root>
                        </div>
                    </Field.Set>
                    <Field.Set>
                        <div class="flex flex-col gap-2">
                            <Field.Label for="password">Password</Field.Label>
                            <InputGroup.Root class="bg-white  px-1 py-1 h-auto shadow-none">
                                <InputGroup.Input id="password" readonly type={showSecretPassword ? 'text' : 'password'} bind:value={secret.password} />
                                <InputGroup.Button
                                    aria-label="Copy"
                                    title="Copy"
                                    size="icon-xs"
                                    onclick={() => showSecretPassword = !showSecretPassword}
                                >
                                    {#if showSecretPassword}
                                        <Eye class="h-2 w-2" />
                                    {:else}
                                        <EyeClosed class="h-2 w-2" />
                                    {/if}
                                </InputGroup.Button>
                                <InputGroup.Button
                                    aria-label="Copy"
                                    title="Copy"
                                    size="icon-xs"
                                    onclick={() => passwordclipboard.copy(secret.password)}
                                >
                                    {#if passwordclipboard.copied}
                                        <Check />
                                    {:else}
                                        <Copy />
                                    {/if}
                                </InputGroup.Button>
                            </InputGroup.Root>
                        </div>
                    </Field.Set>
                </Field.Group>
            </AlertDialog.Description>
		</AlertDialog.Header>
		<AlertDialog.Footer>
			<AlertDialog.Cancel disabled={retrieveSecretDialogIsLoading} type="reset" class="bg-white"
				>Close</AlertDialog.Cancel
			>
		</AlertDialog.Footer>
		<Loader show={retrieveSecretDialogIsLoading} />
	</AlertDialog.Content>
</AlertDialog.Root>

<AlertDialog.Root bind:open={cancelDialogIsOpen}>
	<AlertDialog.Content>
		<AlertDialog.Header>
			<AlertDialog.Title>
                <div class="flex items-center gap-2.5">
                    <div class="bg-primary rounded p-2">
                        <MonitorOff class="h-4 w-4 text-white" strokeWidth={2.5} />
                    </div>
                    <span>Cancel Session</span>
                </div>
            </AlertDialog.Title>
            <AlertDialog.Description class="pt-3">
                <p>
                    This action cannot be undone. Cancelling this session will prevent access to the asset and
                    no JIT credentials will be created.
                </p>
                <p class="mt-3 mb-3">You'll need to submit a new request if you need access again.</p>
            </AlertDialog.Description>
		</AlertDialog.Header>
		<AlertDialog.Footer>
			<form
				method="POST"
				action="?/cancel"
				use:enhance={({ cancel, formData }) => {
					cancelDialogIsLoading = true;
					return async ({ result, update }) => {
						if (result.type === 'success') {
							toast.success('Session cancelled successfully');
							invalidate('sessions:view');
							cancel();
						} else {
							toast.error('Failed to cancel session');
						}
						cancelDialogIsLoading = false;
						cancelDialogIsOpen = false;
					};
				}}
			>
				<AlertDialog.Cancel disabled={cancelDialogIsLoading} type="reset" class="bg-white">Back</AlertDialog.Cancel>
				<AlertDialog.Action disabled={cancelDialogIsLoading} type="submit"
					>Confirm</AlertDialog.Action
				>
			</form>
		</AlertDialog.Footer>
		<Loader show={cancelDialogIsLoading} />
	</AlertDialog.Content>
</AlertDialog.Root>

<AlertDialog.Root bind:open={terminateDialogIsOpen}>
	<AlertDialog.Content class="border-0 border-b-4 border-red-500 bg-red-50">
		<AlertDialog.Header>
			<AlertDialog.Title>
                <div class="flex items-center gap-2.5 text-red-500">
                    <div class="bg-red-100 rounded p-2">
                        <MonitorX class="h-4 w-4 text-red-500" strokeWidth={2.5} />
                    </div>
                    <span>Terminate Session</span>
                </div>
            </AlertDialog.Title>
            <AlertDialog.Description class="pt-3">
                <p>
                    This action will immediately revoke access to <strong>{asset.name}</strong> and terminate the
                    active session.
                </p>
                <p class="mt-3 mb-3">
                    The requester's JIT credentials will be revoked and all session activities will be recorded
                    for audit review.
                </p>
            </AlertDialog.Description>
		</AlertDialog.Header>
		<AlertDialog.Footer>
			<form
				method="POST"
				action="?/terminate"
				use:enhance={({ cancel }) => {
					terminateDialogIsLoading = true;
					return async ({ result, update }) => {
						if (result.type === 'success') {
							toast.success('Session terminated successfully');
							invalidate('sessions:view');
							cancel();
						} else {
							toast.error('Failed to terminate session');
						}
						terminateDialogIsLoading = false;
						terminateDialogIsOpen = false;
					};
				}}
			>
				<AlertDialog.Cancel disabled={terminateDialogIsLoading} type="reset" class="bg-white"
					>Back</AlertDialog.Cancel
				>
				<AlertDialog.Action disabled={terminateDialogIsLoading} type="submit" class="bg-red-500 text-white"
					>Terminate Session</AlertDialog.Action
				>
			</form>
		</AlertDialog.Footer>
		<Loader show={terminateDialogIsLoading} />
	</AlertDialog.Content>
</AlertDialog.Root>

<AlertDialog.Root bind:open={endDialogIsOpen}>
	<AlertDialog.Content>
		<AlertDialog.Header>
			<AlertDialog.Title>
                <div class="flex items-center gap-2.5">
                    <div class="bg-primary rounded p-2">
                        <MonitorX class="h-4 w-4 text-white" strokeWidth={2.5} />
                    </div>
                    <span>End Session</span>
                </div>
            </AlertDialog.Title>
            <AlertDialog.Description class="pt-3">
                <p>
                    This action will end your active session for <strong>{asset.name}</strong> and revoke your JIT
                    credentials.
                </p>
                <p class="mt-3 mb-3">
                    Your session activities will be automatically reviewed and you'll be notified of the results
                    once the AI analysis is complete.
                </p>
            </AlertDialog.Description>
		</AlertDialog.Header>
		<AlertDialog.Footer>
			<form
				method="POST"
				action="?/end"
				use:enhance={({ cancel }) => {
					endDialogIsLoading = true;
					return async ({ result, update }) => {
						if (result.type === 'success') {
							toast.success('Session ended successfully');
							invalidate('sessions:view');
							cancel();
						} else {
							toast.error('Failed to end session');
						}
						endDialogIsLoading = false;
						endDialogIsOpen = false;
					};
				}}
			>
				<AlertDialog.Cancel disabled={endDialogIsLoading} type="reset" class="bg-white">Back</AlertDialog.Cancel>
				<AlertDialog.Action disabled={endDialogIsLoading} type="submit"
					>End Session</AlertDialog.Action
				>
			</form>
		</AlertDialog.Footer>
		<Loader show={endDialogIsLoading} />
	</AlertDialog.Content>
</AlertDialog.Root>

<AlertDialog.Root bind:open={startDialogIsOpen}>
	<AlertDialog.Content>
		<AlertDialog.Header>
			<AlertDialog.Title>
                <div class="flex items-center gap-2.5">
                    <div class="bg-primary rounded p-2">
                        <MonitorPlay class="h-4 w-4 text-white" strokeWidth={2.5} />
                    </div>
                    <span>Start Session</span>
                </div>
            </AlertDialog.Title>
            <AlertDialog.Description class="pt-3">
                <p>
                    Starting this session will provision temporary JIT credentials for: <strong>{asset.name}</strong>
                </p>
                <div class="bg-slate-100 rounded p-4 inset-shadow-xs border my-6 flex flex-col gap-3">
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex flex-col gap-1">
                            <div class="font-bold text-sm text-slate-500 uppercase tracking-wider">Asset Host & Port</div>
                            <div class="font-mono text-sm">{asset.host}:{asset.port}</div>
                        </div>
                        <div class="flex flex-col gap-1 text-right">
                            <div class="font-bold text-sm text-slate-500 uppercase tracking-wider">Protocol</div>
                            <div class="font-mono text-sm">{asset.dbms}</div>
                        </div>
                    </div>
                    <Separator />
                    <div class="flex flex-col gap-1">
                        <div class="font-bold text-sm text-slate-500 uppercase tracking-wider">Scheduled Window</div>
                        <div class="font-mono text-sm">{shortDateTimeRange(model.scheduled_start_datetime, model.scheduled_end_datetime)}</div>
                    </div>
                </div>
                <div class="bg-yellow-50 rounded p-4 border border-yellow-200 text-yellow-600 text-sm">
                    <div class="flex items-center gap-3">
                        <div>
                            <Info class="h-4 w-4 text-yellow-600" strokeWidth={2.5} />
                        </div>
                        <span>
                            All queries executed during this session will be logged and audited in accordance with global compliance policies. 
                            End your session as soon as you're done.
                        </span>
                    </div>
                </div>
            </AlertDialog.Description>
		</AlertDialog.Header>
		<AlertDialog.Footer>
			<form
				method="POST"
				action="?/start"
				use:enhance={({ cancel }) => {
					startDialogIsLoading = true;
					return async ({ result, update }) => {
						if (result.type === 'success') {
							toast.success('Session started successfully');
							invalidate('sessions:view');
							cancel();
						} else {
							toast.error('Failed to start session');
						}
						startDialogIsLoading = false;
						startDialogIsOpen = false;
					};
				}}
			>
				<AlertDialog.Cancel disabled={startDialogIsLoading} type="reset" class="bg-white">Back</AlertDialog.Cancel>
				<AlertDialog.Action disabled={startDialogIsLoading} type="submit">Start Session</AlertDialog.Action>
			</form>
		</AlertDialog.Footer>
		<Loader show={startDialogIsLoading} />
	</AlertDialog.Content>
</AlertDialog.Root>

<div class="flex justify-between gap-2">
    <ButtonGroup.Root>
        {#if canRetrieveSecret}
            <Button
                variant="outline"
                class="transition-all duration-200 h-auto py-2.5 px-4! bg-white hover:border-blue-200 hover:bg-blue-50 hover:text-blue-500"
                onclick={async () => {
                    retrieveSecretDialogIsOpen = true;
                    await retrieveSecret();
                }}
            >
                <KeyRound class="h-4 w-4" />
                Retrieve Secret
            </Button>
        {/if}
        {#if canStart}
            <Button
                variant="outline"
                class="transition-all duration-200 h-auto py-2.5 px-4! bg-white hover:border-blue-200 hover:bg-blue-50 hover:text-blue-500"
                onclick={() => (startDialogIsOpen = true)}
            >
                <Play class="h-4 w-4" />
                Start Session
            </Button>
        {/if}
        {#if canCancel}
            <Button
                variant="outline"
                class="transition-all duration-200 h-auto py-2.5 px-4! bg-white hover:border-red-200 hover:bg-red-50 hover:text-red-500"
                onclick={() => (cancelDialogIsOpen = true)}
            >
                <ClipboardX class="h-4 w-4" />
                Cancel Session
            </Button>
        {/if}
        {#if canEnd}
            <Button
                variant="outline"
                class="transition-all duration-200 h-auto py-2.5 px-4! bg-white hover:border-red-200 hover:bg-red-50 hover:text-red-500"
                onclick={() => (endDialogIsOpen = true)}
            >
                <MonitorX class="h-4 w-4" />
                End Session
            </Button>
        {/if}
        {#if canTerminate}
            <Button
                variant="outline"
                class="transition-all duration-200 h-auto py-2.5 px-4! bg-white hover:border-red-200 hover:bg-red-50 hover:text-red-500"
                onclick={() => (terminateDialogIsOpen = true)}
            >
                <MonitorX class="h-4 w-4" />
                Terminate Session
            </Button>
        {/if}
    </ButtonGroup.Root>
</div>
