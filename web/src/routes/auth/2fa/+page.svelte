<script lang="ts">
	import * as Form from '$ui/form';
	import { REGEXP_ONLY_DIGITS } from 'bits-ui';
	import { Button } from '$ui/button';
	import { TwoFactorVerifySchema } from '$validations/auth';
	import { superForm } from 'sveltekit-superforms';
	import { zod4Client } from 'sveltekit-superforms/adapters';
	import { toast } from 'svelte-sonner';
	import { Loader2 } from '@lucide/svelte';
	import { goto } from '$app/navigation';
	import * as InputOTP from '$ui/input-otp';

	let { data } = $props();
	data.form.data.temp_key = data.tempKey;
	const form = superForm(data.form, {
		validators: zod4Client(TwoFactorVerifySchema),
		delayMs: 100,
		resetForm: false,
		onUpdate({ form, result }) {
			if (!form.valid) {
				return;
			}
			if (result.type === 'success') {
				toast.success(form.message);
				return goto('/');
			} else if (result.type === 'failure') {
				toast.error(result.data.error);
			}
		}
	});

	const { form: formData, enhance, delayed, submitting } = form;
</script>

<div class="bg-background flex min-h-svh flex-col items-center justify-center gap-6 p-6 md:p-10">
	<div class="w-full max-w-sm space-y-6">
		<div class="flex flex-col items-center gap-6">
			<div class="flex size-24 items-center justify-center rounded-md">
				<img src="/logo.png" alt="ArgusPAM" />
			</div>
			<span class="sr-only">ArgusPAM</span>
			<h1 class="text-xl font-bold">Welcome to ArgusPAM</h1>
		</div>
		<form
			method="POST"
			use:enhance
			class="flex max-w-xl flex-col items-center justify-center space-y-6"
		>
			<input type="hidden" name="temp_key" value={data.tempKey} />
			<input type="hidden" name="code" value={$formData.code} />
			<Form.Field {form} name="code" class="flex flex-col items-center justify-center">
				<Form.Control>
					<InputOTP.Root
						maxlength={6}
						pushPasswordManagerStrategy="none"
						pattern={REGEXP_ONLY_DIGITS}
						bind:value={$formData.code}
						disabled={$submitting}
					>
						{#snippet children({ cells })}
							<InputOTP.Group>
								{#each cells as cell (cell)}
									<InputOTP.Slot {cell} class="border-gray-300" />
								{/each}
							</InputOTP.Group>
						{/snippet}
					</InputOTP.Root>
				</Form.Control>
				<Form.FieldErrors />
			</Form.Field>
			<Button type="submit" disabled={$submitting}>
				{#if $submitting}
					<Loader2 className="size-4 animate-spin" />
				{/if}
				Verify OTP
			</Button>
		</form>
	</div>
</div>
