<script lang="ts">
	import type { HTMLAttributes } from 'svelte/elements';
	import { Label } from '$ui/label/index.js';
	import { Input } from '$ui/input/index.js';
	import { Button } from '$ui/button/index.js';
	import { cn, type WithElementRef } from '$lib/utils.js';
	import { goto } from '$app/navigation';
	import { toast } from 'svelte-sonner';
	import { authService } from '$lib/client/services/auth.js';
	import { authStore } from '$lib/client/stores/auth.js';
	import type { ApiError } from '$lib/shared/types/error.js';

	let {
		ref = $bindable(null),
		class: className,
		...restProps
	}: WithElementRef<HTMLAttributes<HTMLDivElement>> = $props();

	let isLoading = $state(false);
	let email = $state('heanluen@gmail.com');
	let password = $state('qweqwe');
	let errors = $state<Record<string, string[]>>({});

	async function handleSubmit(event: SubmitEvent) {
		event.preventDefault();

		if (isLoading) return;

		isLoading = true;
		errors = {};
		authStore.setLoading(true);

		try {
			const result = await authService.login({ email, password });

			// Update auth store with user data
			authStore.setUser(result.user);

			toast.success('Successfully logged in!');

			// Redirect to dashboard
			await goto('/dashboard');
		} catch (error) {
			const apiError = error as ApiError;

			if (apiError.status === 422 && apiError.errors) {
				errors = apiError.errors;
			} else {
				toast.error(apiError.message || 'Login failed. Please try again.');
			}
		} finally {
			isLoading = false;
			authStore.setLoading(false);
		}
	}
</script>

<div class={cn('flex flex-col gap-6', className)} bind:this={ref} {...restProps}>
	<form onsubmit={handleSubmit}>
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
	</form>
</div>
