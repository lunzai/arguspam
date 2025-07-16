<script lang="ts">
	import * as InputOTP from '$ui/input-otp';
	import { Button } from '$ui/button';
	import { REGEXP_ONLY_DIGITS } from 'bits-ui';
	import { slide } from 'svelte/transition';
	import * as Form from '$ui/form';
	import { superForm } from 'sveltekit-superforms';
	import { zodClient } from 'sveltekit-superforms/adapters';
	import { toast } from 'svelte-sonner';
	import { TwoFactorCodeSchema } from '$validations/auth';

	type Props = {
		data: any;
		qrCode: string | null;
		isCurrentUser: boolean;
		showTwoFactorSetup: boolean;
		isSubmitting: boolean;
		onSuccess: () => Promise<void>;
	};

	let {
		data,
		qrCode,
		isSubmitting = $bindable(),
		isCurrentUser,
		showTwoFactorSetup = $bindable(),
		onSuccess = async () => {}
	}: Props = $props();

	const form = superForm(data, {
		validators: zodClient(TwoFactorCodeSchema),
		delayMs: 100,
		async onUpdate({ form, result }) {
			if (!form.valid) {
				return;
			}
			if (result.type === 'success') {
				toast.success(result.data.message);
				await onSuccess();
				showTwoFactorSetup = false;
			} else if (result.type === 'failure') {
				toast.error(result.data.error);
			}
		}
	});

	$effect(() => {
		isSubmitting = $submitting;
	});

	const { form: formData, enhance, submitting } = form;

	function handleVerifyTwoFactor() {}
</script>

<div class="flex flex-col items-start gap-6" transition:slide>
	{#if qrCode}
		<img src={qrCode} alt="QR Code" class="w-56 rounded border border-gray-100 shadow" />
	{:else}
		<p>No QR code available</p>
	{/if}
	<div class="text-muted-foreground text-sm">
		Download Google Authenticator (or similar app) on your phone, then scan this QR code with the
		app. Once scanned, enter the 6-digit code shown in your authenticator app below and click verify
		to complete your two-factor authentication setup.
	</div>
	<form action="?/verifyTwoFactor" method="POST" use:enhance>
		<input type="hidden" name="code" value={$formData.code} />
		<div class="flex items-start gap-3">
			<Form.Field {form} name="code">
				<Form.Control>
					<InputOTP.Root
						maxlength={6}
						pushPasswordManagerStrategy="none"
						pattern={REGEXP_ONLY_DIGITS}
						bind:value={$formData.code}
					>
						{#snippet children({ cells })}
							<InputOTP.Group>
								{#each cells as cell (cell)}
									<InputOTP.Slot {cell} />
								{/each}
							</InputOTP.Group>
						{/snippet}
					</InputOTP.Root>
				</Form.Control>
				<Form.FieldErrors />
			</Form.Field>
			<Button
				type="submit"
				variant="outline"
				class="h-10 transition-all duration-200 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-500"
			>
				Verify OTP Code
			</Button>
			{#if !isCurrentUser}
				<Button
					onclick={() => {
						showTwoFactorSetup = false;
						form.reset();
					}}
					class="h-10"
					variant="outline"
				>
					Cancel
				</Button>
			{/if}
		</div>
	</form>
</div>
