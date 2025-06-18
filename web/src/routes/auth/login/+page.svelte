<script lang="ts">
	import * as Form from '$ui/form';
	import { Input } from '$ui/input';
	import { Button } from '$ui/button';
	import { loginSchema } from '$validations/auth';
	import { superForm } from 'sveltekit-superforms';
	import { zodClient } from 'sveltekit-superforms/adapters';
	import { toast } from 'svelte-sonner';
	import { Loader2 } from '@lucide/svelte';
	import { goto } from '$app/navigation';

	let { data } = $props();

	const form = superForm(data.form, {
		validators: zodClient(loginSchema),
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
		<form method="POST" use:enhance class="max-w-xl space-y-6">
			<Form.Field {form} name="email">
				<Form.Control>
					{#snippet children({ props })}
						<Form.Label>Email</Form.Label>
						<Input {...props} type="email" bind:value={$formData.email} />
					{/snippet}
				</Form.Control>
				<Form.FieldErrors />
			</Form.Field>
			<Form.Field {form} name="password">
				<Form.Control>
					{#snippet children({ props })}
						<Form.Label>Password</Form.Label>
						<Input {...props} type="password" bind:value={$formData.password} />
					{/snippet}
				</Form.Control>
				<Form.FieldErrors />
			</Form.Field>
			<Button type="submit" disabled={$submitting}>
				{#if $delayed}
					<Loader2 className="size-4 animate-spin" />
				{/if}
				Login
			</Button>
		</form>
	</div>
</div>
