<script lang="ts">
    import * as InputOTP from '$ui/input-otp';
    import { Button } from '$ui/button';
    import { REGEXP_ONLY_DIGITS } from 'bits-ui';
    import { slide } from 'svelte/transition';
    import * as Form from '$ui/form';
    import { superForm } from 'sveltekit-superforms';
    import { zodClient } from 'sveltekit-superforms/adapters';
    import { toast } from 'svelte-sonner';
    import { twoFactorCodeSchema } from '$validations/auth';
    import Loader from '$components/loader.svelte';

    type Props = {
        data: any;
        qrCode: string | null;
        isCurrentUser: boolean;
        showTwoFactorSetup: boolean;
        isSubmitting: boolean;
        onSuccess: () => Promise<void>;
    };

    let { 
        data = $bindable(),
        qrCode,
        isSubmitting = $bindable(),
        isCurrentUser, 
        showTwoFactorSetup = $bindable(),
		onSuccess = async () => {}
    } : Props = $props();

    const form = superForm(data, {
		validators: zodClient(twoFactorCodeSchema),
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

    function handleVerifyTwoFactor(formData: FormData) {
        const code = formData.get('code');
        console.log(code);
    }
</script>

<!-- {#if $submitting}
    <Loader show={$submitting} />
{/if} -->
<div class="flex flex-col gap-6 items-start" transition:slide>
    {#if qrCode}
        <img src={qrCode} alt="QR Code" class="w-56 border border-gray-100 rounded shadow" />
    {:else}
        <p>No QR code available</p>
    {/if}
    <div class="text-sm text-muted-foreground">
        Download Google Authenticator (or similar app) on your phone, then scan this QR code with the app. Once scanned, enter the 6-digit code shown in your authenticator app below and click verify to complete your two-factor authentication setup.
    </div>
    <form action="?/verifyTwoFactor" method="POST" use:enhance>
        <input type="hidden" name="code" value={$formData.code} />
        <div class="flex gap-3 items-start">
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
                class="h-10 hover:bg-blue-50 hover:text-blue-500 hover:border-blue-200 transition-all duration-200">
                Verify OTP Code
            </Button>
            {#if !isCurrentUser}
                <Button 
                    onclick={() => {
                        showTwoFactorSetup = false;
                        form.reset();
                    }}
                    class="h-10"
                    variant="outline">
                    Cancel
                </Button>
            {/if}
        </div>
    </form>
</div>