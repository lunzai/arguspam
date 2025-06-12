<script lang="ts">
	import type { PageData } from './$types';
	import { authStore } from '$stores/auth';
	import { orgStore } from '$stores/org';
	import { Card, CardContent, CardHeader, CardTitle } from '$ui/card';
	import { Badge } from '$ui/badge;

	let { data }: { data: PageData } = $props();

	// Get reactive store states
	const authState = authStore;
	const orgState = orgStore;

	// Get current org object
	const currentOrg = $derived(orgState.getCurrentOrg($orgState));
</script>

<div class="space-y-6 py-4">
	<h1 class="text-3xl font-bold">Dashboard</h1>

	<div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
		<!-- Server-side User Data -->
		<Card>
			<CardHeader>
				<CardTitle>Server-side User Data</CardTitle>
			</CardHeader>
			<CardContent class="space-y-4">
				{#if data.user}
					<div class="grid gap-2">
						<div><strong>ID:</strong> {data.user.id}</div>
						<div><strong>Name:</strong> {data.user.name}</div>
						<div><strong>Email:</strong> {data.user.email}</div>
						<div>
							<strong>Status:</strong>
							<Badge variant={data.user.status === 'active' ? 'default' : 'secondary'}>
								{data.user.status}
							</Badge>
						</div>
						<div>
							<strong>Email Verified:</strong>
							<Badge variant={data.user.email_verified_at ? 'default' : 'destructive'}>
								{data.user.email_verified_at ? 'Yes' : 'No'}
							</Badge>
						</div>
						<div>
							<strong>2FA Enabled:</strong>
							<Badge variant={data.user.two_factor_enabled ? 'default' : 'secondary'}>
								{data.user.two_factor_enabled ? 'Yes' : 'No'}
							</Badge>
						</div>
						<div>
							<strong>Last Login:</strong>
							{data.user.last_login_at
								? new Date(data.user.last_login_at).toLocaleString()
								: 'Never'}
						</div>
						<div><strong>Created:</strong> {new Date(data.user.created_at).toLocaleString()}</div>
						<div>
							<strong>Updated:</strong>
							{data.user.updated_at
								? new Date(data.user.updated_at).toLocaleString()
								: 'Not available'}
						</div>
					</div>
				{:else}
					<p class="text-muted-foreground">No user data available</p>
				{/if}
			</CardContent>
		</Card>

		<!-- Client-side Auth Store -->
		<Card>
			<CardHeader>
				<CardTitle>Client-side Auth Store</CardTitle>
			</CardHeader>
			<CardContent class="space-y-4">
				<div class="grid gap-2">
					<div>
						<strong>Is Authenticated:</strong>
						<Badge variant={$authState.isAuthenticated ? 'default' : 'destructive'}>
							{$authState.isAuthenticated ? 'Yes' : 'No'}
						</Badge>
					</div>
					<div><strong>Store User:</strong></div>
					{#if $authState.user}
						<div class="ml-4 grid gap-1 text-sm">
							<div>ID: {$authState.user.id}</div>
							<div>Name: {$authState.user.name}</div>
							<div>Email: {$authState.user.email}</div>
							<div>Status: {$authState.user.status}</div>
						</div>
					{:else}
						<p class="text-muted-foreground ml-4">No user in store</p>
					{/if}
				</div>
			</CardContent>
		</Card>

		<!-- Current Org Debug Info -->
		<Card>
			<CardHeader>
				<CardTitle>Current Organization</CardTitle>
			</CardHeader>
			<CardContent class="space-y-4">
				<div class="grid gap-2">
					<div>
						<strong>Current Org ID:</strong>
						<Badge variant={$orgState.currentOrgId ? 'default' : 'secondary'}>
							{$orgState.currentOrgId || 'None'}
						</Badge>
					</div>
					<div><strong>Current Org Details:</strong></div>
					{#if currentOrg}
						<div class="ml-4 grid gap-1 text-sm">
							<div>ID: {currentOrg.id}</div>
							<div>Name: {currentOrg.name}</div>
							<div>Description: {currentOrg.description || 'None'}</div>
							<div>
								Status: <Badge variant={currentOrg.status === 'active' ? 'default' : 'secondary'}>
									{currentOrg.status}
								</Badge>
							</div>
						</div>
					{:else}
						<p class="text-muted-foreground ml-4">No current org selected</p>
					{/if}

					<div><strong>Available Orgs ({$orgState.orgs.length}):</strong></div>
					{#if $orgState.orgs.length > 0}
						<div class="ml-4 grid gap-1 text-sm">
							{#each $orgState.orgs as org}
								<div class="flex items-center gap-2">
									<Badge variant={org.id === $orgState.currentOrgId ? 'default' : 'outline'}>
										{org.id}
									</Badge>
									<span>{org.name}</span>
									<Badge
										variant={org.status === 'active' ? 'default' : 'secondary'}
										class="text-xs"
									>
										{org.status}
									</Badge>
								</div>
							{/each}
						</div>
					{:else}
						<p class="text-muted-foreground ml-4">No orgs available</p>
					{/if}
				</div>
			</CardContent>
		</Card>
	</div>

	<!-- Raw JSON Debug -->
	<Card>
		<CardHeader>
			<CardTitle>Debug Information</CardTitle>
		</CardHeader>
		<CardContent>
			<div class="grid gap-4 md:grid-cols-3">
				<div>
					<h4 class="mb-2 font-medium">Server Data (JSON):</h4>
					<pre class="bg-muted max-h-96 min-h-96 overflow-auto rounded-md p-4 text-sm"><code
							>{JSON.stringify(data, null, 2)}</code
						></pre>
				</div>
				<div>
					<h4 class="mb-2 font-medium">Auth Store State (JSON):</h4>
					<pre class="bg-muted max-h-96 min-h-96 overflow-auto rounded-md p-4 text-sm"><code
							>{JSON.stringify($authState, null, 2)}</code
						></pre>
				</div>
				<div>
					<h4 class="mb-2 font-medium">Org Store State (JSON):</h4>
					<pre class="bg-muted max-h-96 min-h-96 overflow-auto rounded-md p-4 text-sm"><code
							>{JSON.stringify($orgState, null, 2)}</code
						></pre>
				</div>
			</div>
		</CardContent>
	</Card>
</div>
