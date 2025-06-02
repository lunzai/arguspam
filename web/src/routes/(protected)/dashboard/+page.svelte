<script lang="ts">
	import { onMount } from 'svelte';
	import { 
		user, 
		isAuthenticated, 
		isEmailVerified, 
		isTwoFactorEnabled, 
		isTwoFactorConfirmed 
	} from '$lib/stores/auth';
	import { apiService } from '$lib/services/api';
	import { Button } from "$ui/button/index.js";
	import { toast } from "svelte-sonner";

	let userData = $state(null);
	let isLoading = $state(false);

	// Example of using the API service
	async function fetchCurrentUser() {
		isLoading = true;
		try {
			const response = await apiService.getCurrentUser();
			userData = response.data.data.attributes;
			toast.success('User data fetched successfully');
		} catch (error) {
			toast.error('Failed to fetch user data');
			console.error('Error:', error);
		} finally {
			isLoading = false;
		}
	}

	onMount(() => {
		if ($isAuthenticated) {
			console.log('User from store:', $user);
		}
	});
</script>

<div class="max-w-4xl mx-auto">
	<h1 class="text-3xl font-bold text-gray-900 mb-8">Dashboard</h1>
	
	{#if $isAuthenticated}
		<div class="grid gap-6">
			<!-- User Info Card -->
			<div class="bg-white rounded-lg shadow p-6">
				<h2 class="text-xl font-semibold mb-4">User Information (from store)</h2>
				{#if $user}
					<div class="grid grid-cols-2 gap-4 text-sm">
						<div>
							<span class="font-medium text-gray-600">ID:</span>
							<span class="ml-2">{$user.id}</span>
						</div>
						<div>
							<span class="font-medium text-gray-600">Name:</span>
							<span class="ml-2">{$user.name}</span>
						</div>
						<div>
							<span class="font-medium text-gray-600">Email:</span>
							<span class="ml-2">{$user.email}</span>
						</div>
						<div>
							<span class="font-medium text-gray-600">Status:</span>
							<span class="ml-2 capitalize">{$user.status}</span>
						</div>
						<div>
							<span class="font-medium text-gray-600">Email Verified:</span>
							<span class="ml-2">
								{#if $isEmailVerified}
									<span class="text-green-600 font-medium">✓ Verified</span>
								{:else}
									<span class="text-red-600 font-medium">✗ Not Verified</span>
								{/if}
							</span>
						</div>
						<div>
							<span class="font-medium text-gray-600">Two Factor:</span>
							<span class="ml-2">
								{#if $isTwoFactorEnabled}
									{#if $isTwoFactorConfirmed}
										<span class="text-green-600 font-medium">✓ Enabled & Confirmed</span>
									{:else}
										<span class="text-yellow-600 font-medium">⚠ Enabled but not confirmed</span>
									{/if}
								{:else}
									<span class="text-gray-500">Disabled</span>
								{/if}
							</span>
						</div>
						{#if $user.email_verified_at}
							<div class="col-span-2">
								<span class="font-medium text-gray-600">Email Verified At:</span>
								<span class="ml-2">{new Date($user.email_verified_at).toLocaleString()}</span>
							</div>
						{/if}
						{#if $user.two_factor_confirmed_at}
							<div class="col-span-2">
								<span class="font-medium text-gray-600">Two Factor Confirmed At:</span>
								<span class="ml-2">{new Date($user.two_factor_confirmed_at).toLocaleString()}</span>
							</div>
						{/if}
					</div>
				{/if}
			</div>

			<!-- API Test Card -->
			<div class="bg-white rounded-lg shadow p-6">
				<div class="flex items-center justify-between mb-4">
					<h2 class="text-xl font-semibold">API Test</h2>
					<Button onclick={fetchCurrentUser} disabled={isLoading}>
						{#if isLoading}
							<span class="i-lucide-loader-2 animate-spin mr-2"></span>
							Loading...
						{:else}
							Fetch User Data
						{/if}
					</Button>
				</div>
				
				{#if userData}
					<div class="mt-4 p-4 bg-gray-50 rounded">
						<h3 class="font-medium mb-2">Data from API:</h3>
						<pre class="text-sm text-gray-700 overflow-auto">{JSON.stringify(userData, null, 2)}</pre>
					</div>
				{/if}
			</div>

			<!-- Instructions Card -->
			<div class="bg-blue-50 rounded-lg p-6">
				<h2 class="text-xl font-semibold text-blue-900 mb-4">How to Use</h2>
				<div class="space-y-3 text-blue-800">
					<p><strong>For API requests:</strong> Use the <code class="bg-blue-100 px-2 py-1 rounded">apiService</code> from <code class="bg-blue-100 px-2 py-1 rounded">$lib/services/api</code></p>
					<p><strong>For user data:</strong> Use the <code class="bg-blue-100 px-2 py-1 rounded">user</code> store from <code class="bg-blue-100 px-2 py-1 rounded">$lib/stores/auth</code></p>
					<p><strong>For auth state:</strong> Use stores like <code class="bg-blue-100 px-2 py-1 rounded">isAuthenticated</code>, <code class="bg-blue-100 px-2 py-1 rounded">isEmailVerified</code>, <code class="bg-blue-100 px-2 py-1 rounded">isTwoFactorEnabled</code></p>
					<p><strong>Token:</strong> Automatically included in all API requests via cookies</p>
				</div>
			</div>
		</div>
	{:else}
		<div class="text-center py-12">
			<h2 class="text-2xl font-semibold text-gray-900 mb-4">Welcome to ArgusPAM</h2>
			<p class="text-gray-600 mb-6">Please log in to access the application.</p>
			<a href="/auth/login" class="text-blue-600 hover:text-blue-800 underline">Go to Login</a>
		</div>
	{/if}
</div>