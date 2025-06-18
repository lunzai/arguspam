<script lang="ts">
	import AppSidebar from '$components/sidebar/app-sidebar.svelte';
	import * as Breadcrumb from '$ui/breadcrumb/index.js';
	import { Separator } from '$ui/separator/index.js';
	import * as Sidebar from '$ui/sidebar/index.js';
	import { page } from '$app/state';
	let { children } = $props();

	interface BreadcrumbItem {
		label: string;
		href?: string;
		isActive?: boolean;
	}

	// Route to label mapping for better breadcrumb names
	const ROUTE_LABELS: Record<string, string> = {
		dashboard: 'Dashboard',
		users: 'Users',
		roles: 'Roles',
		permissions: 'Permissions',
		organizations: 'Organizations',
		orgs: 'Organizations',
		sessions: 'Sessions',
		audits: 'Audits',
		requests: 'Requests',
		assets: 'Assets',
		settings: 'Settings',
		profile: 'Profile',
		account: 'Account',
		'user-groups': 'User Groups',
		'session-audits': 'Session Audits',
		'user-access-restrictions': 'Access Restrictions',
		grants: 'Grants',
		accounts: 'Accounts'
	};

	function getSegmentLabel(segment: string): string {
		return (
			ROUTE_LABELS[segment] ||
			segment
				.split('-')
				.map((word) => word.charAt(0).toUpperCase() + word.slice(1))
				.join(' ')
		);
	}

	function generateBreadcrumbs(currentPage: typeof page): BreadcrumbItem[] {
		const { route, params } = currentPage;

		// Handle root/dashboard
		if (route.id === '/(protected)' || route.id === '/(protected)/dashboard') {
			return [
				{ label: 'Home', href: '/' },
				{ label: 'Dashboard', isActive: true }
			];
		}

		// Parse route segments (remove route groups like (protected))
		const segments =
			route.id?.split('/').filter((segment) => segment && !segment.startsWith('(')) || [];

		const breadcrumbs: BreadcrumbItem[] = [{ label: 'Home', href: '/' }];

		let currentPath = '';

		segments.forEach((segment, index) => {
			const isLast = index === segments.length - 1;

			// Handle dynamic segments like [id]
			if (segment.startsWith('[') && segment.endsWith(']')) {
				const paramName = segment.slice(1, -1);
				const paramValue = params[paramName];

				if (paramValue) {
					currentPath += `/${paramValue}`;
					const previousSegment = segments[index - 1];
					const entityLabel = previousSegment ? getSegmentLabel(previousSegment) : 'Item';
					breadcrumbs.push({
						label: `${entityLabel} #${paramValue}`,
						href: isLast ? undefined : currentPath,
						isActive: isLast
					});
				}
			} else {
				currentPath += `/${segment}`;
				breadcrumbs.push({
					label: getSegmentLabel(segment),
					href: isLast ? undefined : currentPath,
					isActive: isLast
				});
			}
		});

		return breadcrumbs;
	}

	const breadcrumbs = $derived(generateBreadcrumbs(page));
</script>

<Sidebar.Provider>
	<AppSidebar />
	<Sidebar.Inset>
		<header
			class="flex
			h-16 shrink-0 items-center gap-2 border-b border-gray-200
			transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12"
		>
			<div class="flex items-center gap-2 px-4">
				<Sidebar.Trigger class="-ml-1" />
				<Separator orientation="vertical" class="mr-2 data-[orientation=vertical]:h-4" />
				<Breadcrumb.Root>
					<Breadcrumb.List>
						{#each breadcrumbs as item, index (item.label)}
							{#if index > 0}
								<Breadcrumb.Separator class="hidden md:block" />
							{/if}
							<Breadcrumb.Item class="hidden md:block">
								{#if item.href && !item.isActive}
									<Breadcrumb.Link href={item.href}>{item.label}</Breadcrumb.Link>
								{:else}
									<Breadcrumb.Page>{item.label}</Breadcrumb.Page>
								{/if}
							</Breadcrumb.Item>
						{/each}
					</Breadcrumb.List>
				</Breadcrumb.Root>
			</div>
		</header>
		<div class="flex flex-1 flex-col p-4">
			{@render children()}
			<!-- <div class="grid auto-rows-min gap-4 md:grid-cols-3">
				<div class="bg-muted/50 aspect-video rounded-xl"></div>
				<div class="bg-muted/50 aspect-video rounded-xl"></div>
				<div class="bg-muted/50 aspect-video rounded-xl"></div>
			</div> -->
			<!-- <div class="bg-muted/50 min-h-[100vh] flex-1 rounded-xl md:min-h-min"></div> -->
		</div>
	</Sidebar.Inset>
</Sidebar.Provider>
