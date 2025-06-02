<script lang="ts">
	import type { HTMLAttributes } from "svelte/elements";
	import { Label } from "$ui/label/index.js";
	import { Input } from "$ui/input/index.js";
	import { Button } from "$ui/button/index.js";
	import { cn, type WithElementRef } from "$lib/utils.js";
	import { goto } from "$app/navigation";
	import { toast } from "svelte-sonner";

	let {
		ref = $bindable(null),
		class: className,
		...restProps
	}: WithElementRef<HTMLAttributes<HTMLDivElement>> = $props();

	let isLoading = $state(false);
	let email = $state('qwe@qwe.com');
	let password = $state('qweqwe');

	async function handleSubmit(event: SubmitEvent) {
		event.preventDefault();
	}
</script>

<div class={cn("flex flex-col gap-6", className)} bind:this={ref} {...restProps}>
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
					/>
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
					/>
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
