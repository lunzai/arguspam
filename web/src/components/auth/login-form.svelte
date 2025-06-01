<script lang="ts">
	import type { HTMLAttributes } from "svelte/elements";
	import { Label } from "$ui/label/index.js";
	import { Input } from "$ui/input/index.js";
	import { Button } from "$ui/button/index.js";
	import { cn, type WithElementRef } from "$lib/utils.js";
	import { enhance } from '$app/forms';
	import { toast } from "svelte-sonner";

	interface FormData {
		email?: string;
		error?: string;
		errors?: Record<string, string[]>;
	}

	let {
		ref = $bindable(null),
		class: className,
		form,
		...restProps
	}: WithElementRef<HTMLAttributes<HTMLDivElement> & { form?: FormData }> = $props();

	let isLoading = $state(false);
</script>

<div class={cn("flex flex-col gap-6", className)} bind:this={ref} {...restProps}>
	<form method="POST" action="?/login" use:enhance={() => {
		isLoading = true;
		
		return async ({ result, update }: { result: any; update: any }) => {
			if (result.type === 'success') {
				toast.success('Login successful');
			} else if (result.type === 'failure') {
				if (result.data?.error) {
					toast.error(result.data.error);
				} else if (result.data?.errors) {
					// Handle validation errors
					const firstError = Object.values(result.data.errors)[0];
					if (firstError && Array.isArray(firstError)) {
						toast.error(firstError[0]);
					}
				}
			} else if (result.type === 'redirect') {
				toast.success('Login successful');
			}
			
			isLoading = false;
			await update();
		};
	}}>
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
						value={form?.email || 'heanluen@gmail.com'}
						disabled={isLoading}
						class={form?.errors?.email ? "border-destructive" : ""}
					/>
					{#if form?.errors?.email}
						<p class="text-sm text-destructive">{form.errors.email[0]}</p>
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
						value="password"
						disabled={isLoading}
						class={form?.errors?.password ? "border-destructive" : ""}
					/>
					{#if form?.errors?.password}
						<p class="text-sm text-destructive">{form.errors.password[0]}</p>
					{/if}
				</div>
				<div>
					<a href="/auth/forget-password" class="text-sm text-muted-foreground">Forgot password?</a>
				</div>
				<Button type="submit" class="w-full" disabled={isLoading}>
					{#if isLoading}
						<span class="i-lucide-loader-2 animate-spin mr-2"></span>
						Logging in...
					{:else}
						Login
					{/if}
				</Button>
			</div>
		</div>
	</form>
</div>
