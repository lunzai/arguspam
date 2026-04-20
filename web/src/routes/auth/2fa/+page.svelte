<script lang="ts">
	import * as Form from '$ui/form';
	import { REGEXP_ONLY_DIGITS } from 'bits-ui';
	import { Button } from '$ui/button';
	import { TwoFactorVerifySchema } from '$validations/auth';
	import { superForm } from 'sveltekit-superforms';
	import { zod4Client } from 'sveltekit-superforms/adapters';
	import { toast } from 'svelte-sonner';
	import { LoaderCircle } from '@lucide/svelte';
	import { goto } from '$app/navigation';
	import * as InputOTP from '$ui/input-otp';
	import * as Card from '$ui/card';
    import { ArrowLeft, LockKeyholeOpen } from '@lucide/svelte';

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


<div class="flex-1 flex items-center justify-center">
	<div class="w-full max-w-sm space-y-8">
		<div class="flex flex-col items-center gap-4">
			<div class="flex size-16 items-center justify-center rounded-md">
				<img src="/logo.png" alt="ArgusPAM" />
			</div>
			<span class="sr-only">ArgusPAM</span>
			<h1 class="text-2xl font-extrabold tracking-wide text-on-surface uppercase">ArgusPAM</h1>
		</div>

        <Card.Root class="@container/card rounded-xl py-8 border-0 shadow-[0_20px_50px_-12px_rgba(23,28,35,0.08)]">
            <Card.Header class="px-8 text-center">
                <Card.Title class="text-xl font-bold tracking-tight">Two-Factor Authentication</Card.Title>
                <Card.Description>Enter the 6-digit code from your authenticator app</Card.Description>
            </Card.Header>
            <Card.Content>

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
                                    <InputOTP.Group class="space-x-1.5">
                                        {#each cells as cell (cell)}
                                            <InputOTP.Slot {cell} class="border-slate-300 rounded! border" />
                                        {/each}
                                    </InputOTP.Group>
                                {/snippet}
                            </InputOTP.Root>
                        </Form.Control>
                        <Form.FieldErrors />
                    </Form.Field>
                    <Button type="submit" disabled={$submitting} class="w-full">
                        {#if $submitting}
                            <LoaderCircle className="size-4 animate-spin" />
                        {/if}
                        Verify OTP <LockKeyholeOpen className="size-4" />
                    </Button>
                    <div class="">
                        <a href="/auth/login" class="group text-primary hover:underline font-medium text-sm flex items-center gap-1">
                            <ArrowLeft class="size-3 transition-all group-hover:-translate-x-1" strokeWidth={3} /> Back to Login
                        </a>
                    </div>
                </form>
                
            </Card.Content>
        </Card.Root>

	</div>
</div>