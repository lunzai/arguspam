<script lang="ts">
	import type { HTMLAttributes } from 'svelte/elements';
	import { Label } from '$ui/label/index.js';
	import { Input } from '$ui/input/index.js';
	import { Button } from '$ui/button/index.js';
	import { cn, type WithElementRef } from '$lib/utils';
	import { goto } from '$app/navigation';
	import { toast } from 'svelte-sonner';
	import { authService } from '$lib/services/client/auth.js';
	import { authStore } from '$lib/stores/auth.js';
	import type { ApiError } from '$lib/types/error.js';

	let {
		ref = $bindable(null),
		class: className,
		form,
		...restProps
	}: WithElementRef<HTMLAttributes<HTMLDivElement> & { form?: FormData }> = $props();

	let isLoading = $state(false);
	let email = $state('heanluen@gmail.com');
	let password = $state('password');
	let errors = $state<Record<string, string[]>>({});

	async function handleSubmit(event: SubmitEvent) {
		event.preventDefault();

		if (isLoading) return;

		isLoading = true;
		errors = {};

		try {
			const result = await authService.login({ email, password });
			authStore.setUser(result.data.user);
			toast.success('Successfully logged in!');
			await goto('/dashboard');
		} catch (error) {
			console.log('login formcatch error', error);
			const apiError = error as ApiError;
			if (apiError.status === 422 && apiError.errors) {
				errors = apiError.errors;
			} else {
				toast.error(apiError.message || 'Login failed. Please try again.');
			}
		} finally {
			isLoading = false;
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
						name="email"
						type="email"
						placeholder="me@example.com"
						required
						value={form?.email || ''}
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
						value=""
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
