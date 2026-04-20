<script lang="ts">
	import * as Form from '$ui/form';
	import { Input } from '$ui/input';
	import { Button } from '$ui/button';
	import { LoginSchema } from '$validations/auth';
	import { superForm } from 'sveltekit-superforms';
	import { zod4Client } from 'sveltekit-superforms/adapters';
	import { toast } from 'svelte-sonner';
	import { LoaderCircle } from '@lucide/svelte';
	import { goto } from '$app/navigation';
	import * as Card from '$ui/card';
    import { Separator } from '$ui/separator';

	let { data } = $props();

	const form = superForm(data.form, {
		validators: zod4Client(LoginSchema),
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
            <Card.Header class="px-8">
                <Card.Title class="text-xl font-bold tracking-tight">Welcome Back</Card.Title>
                <Card.Description>Sign in to your account</Card.Description>
            </Card.Header>
            <Card.Content>

                <form method="POST" use:enhance class="space-y-6">
                    <Form.Field {form} name="email">
                        <Form.Control>
                            {#snippet children({ props })}
                                <Form.Label>Email</Form.Label>
                                <Input {...props} type="email" bind:value={$formData.email} disabled={$submitting} />
                            {/snippet}
                        </Form.Control>
                        <Form.FieldErrors />
                    </Form.Field>
                    <Form.Field {form} name="password">
                        <Form.Control>
                            {#snippet children({ props })}
                                <Form.Label>Password</Form.Label>
                                <Input
                                    {...props}
                                    type="password"
                                    bind:value={$formData.password}
                                    disabled={$submitting}
                                />
                            {/snippet}
                        </Form.Control>
                        <Form.FieldErrors />
                        <!-- <div class="text-right">
                            <a href="/auth/forgot-password" class="text-primary hover:underline font-medium text-xs">Forgot Password?</a>
                        </div> -->
                    </Form.Field>
                    
                    <Button type="submit" disabled={$submitting} class="w-full">
                        {#if $submitting}
                            <LoaderCircle className="size-4 animate-spin" />
                        {/if}
                        Sign In
                    </Button>
                </form>
                <!-- <Separator class="my-6 bg-slate-100" />
                <div class="text-xs text-muted-foreground text-center">
                    Don't have an account? <a href="/auth/register" class="text-primary hover:underline font-medium">Request Access</a>
                </div> -->
                
            </Card.Content>
        </Card.Root>

	</div>
</div>
