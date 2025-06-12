<script lang="ts">
	// import type { HTMLAttributes } from 'svelte/elements';
	// import { cn, type WithElementRef } from '$lib/utils';
	// import { Label } from '$ui/label/index.js';
	// import { Input } from '$ui/input/index.js';
	// import { Button } from '$ui/button/index.js';
	// import { enhance } from '$app/forms';
	// import { goto } from '$app/navigation';
	// import { toast } from 'svelte-sonner';
	// // import type { ApiError } from '$types/error.js';
	// import { orgStore } from '$stores/org';
	import * as Form from '$ui/form';
	import { Input } from '$ui/input';
	import { Button } from '$ui/button';
	import { loginSchema, type Login } from '$validations/auth';
	import { type SuperValidated, type Infer, superForm } from 'sveltekit-superforms';
	import { zodClient } from 'sveltekit-superforms/adapters';
	import { toast } from 'svelte-sonner';
	import SuperDebug from 'sveltekit-superforms';

	let { 
		data 
	}: { 
		data: { form: SuperValidated<Login> } 
	} = $props();

	const form = superForm(data.form, {
		validators: zodClient(loginSchema)
	});

	const { form: formData, enhance, errors, message } = form;

	if (message) {
		toast.success(message);
	}

	// # TO REMOVE
	if ($formData.email === '') {
		$formData.email = 'heanluen@gmail.com';
	}
	if ($formData.password === '') {
		$formData.password = 'password';
	}

	// let isLoading = $state(false);
	// let email = $state('heanluen@gmail.com');
	// let password = $state('password');
	// let errors = $state<Record<string, string[]>>({});

	// async function handleSubmit(event: SubmitEvent) {
	// 	event.preventDefault();

	// 	if (isLoading) return;

	// 	isLoading = true;
	// 	errors = {};

	// 	try {
	// 		const result = await authService.login({ email, password });
	// 		authStore.setUser(result.data.user);
	// 		orgStore.setOrgs(result.data.orgs);
	// 		orgStore.setCurrentOrgId(result.data.currentOrgId);
	// 		toast.success('Successfully logged in!');
	// 		await goto('/dashboard');
	// 	} catch (error) {
	// 		const apiError = error as ApiError;
	// 		if (apiError.status === 422 && apiError.errors) {
	// 			errors = apiError.errors;
	// 		} else {
	// 			toast.error(apiError.message || 'Login failed. Please try again.');
	// 		}
	// 	} finally {
	// 		isLoading = false;
	// 	}
	// }
</script>

<div class="space-y-6">
	<div class="flex flex-col items-center gap-6">
		<div class="flex size-24 items-center justify-center rounded-md">
			<img src="/logo.png" alt="ArgusPAM" />
		</div>
		<span class="sr-only">ArgusPAM</span>
		<h1 class="text-xl font-bold">Welcome to ArgusPAM</h1>
	</div>
	<form method="POST" use:enhance class="space-y-6 max-w-xl">
		<Form.Field {form} name="email">
			<Form.Control>
				<Form.Label>Email</Form.Label>
				<Input bind:value={$formData.email} />
			</Form.Control>
			<Form.FieldErrors />
		</Form.Field>
		<Form.Field {form} name="password">
			<Form.Control>
				<Form.Label>Password</Form.Label>
				<Input type="password" bind:value={$formData.password} />
			</Form.Control>
			<Form.FieldErrors />
		</Form.Field>
		<Button type="submit">Login</Button>
	</form>
</div>

<SuperDebug {form} />
<!-- 	
	<form method="post" use:enhance>
		<div class="flex flex-col gap-6">
			<div class="flex flex-col items-center gap-2">
				<div class="flex size-24 items-center justify-center rounded-md">
					<img src="/logo.png" alt="ArgusPAM" />
				</div>
				<span class="sr-only">ArgusPAM</span>
				<h1 class="text-xl font-bold">Welcome to ArgusPAM</h1>
			</div>
			<div class="flex flex-col gap-6">
				<div class="grid gap-3">
					<Label for="email">Email</Label>
					<Input
						id="email"
						name="email"
						type="email"
						placeholder="me@example.com"
						required
						bind:value={email}
						disabled={isLoading}
						class={errors.email ? 'border-destructive' : ''}
					/>
					{#if errors.email}
						<div class="text-destructive text-sm">
							{#each errors.email as error}
								<div>{error}</div>
							{/each}
						</div>
					{/if}
				</div>
				<div class="grid gap-3">
					<Label for="password">Password</Label>
					<Input
						id="password"
						name="password"
						type="password"
						placeholder="********"
						required
						bind:value={password}
						disabled={isLoading}
						class={errors.password ? 'border-destructive' : ''}
					/>
					{#if errors.password}
						<div class="text-destructive text-sm">
							{#each errors.password as error}
								<div>{error}</div>
							{/each}
						</div>
					{/if}
				</div>
				<div>
					<a href="/auth/forget-password" class="text-muted-foreground text-sm">Forgot password?</a>
				</div>
				<Button type="submit" class="w-full" disabled={isLoading}>
					{#if isLoading}
						<span class="i-lucide-loader-2 mr-2 animate-spin"></span>
						Logging in...
					{:else}
						Login
					{/if}
				</Button>
			</div>
		</div>
	</form> -->
<!-- </div> -->
