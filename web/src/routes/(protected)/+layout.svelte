<script lang="ts">
	import AppSidebar from '$components/sidebar/app-sidebar.svelte';
	import * as Breadcrumb from '$ui/breadcrumb';
	import { Separator } from '$ui/separator';
	import * as Sidebar from '$ui/sidebar';
	import { page } from '$app/state';
	import { toast } from 'svelte-sonner';
	import { afterNavigate } from '$app/navigation';
	import { layoutStore } from '$lib/stores/layout';

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

	afterNavigate(() => {
		const user = $layoutStore.user;
		if (user.two_factor_enabled && !user.two_factor_confirmed_at) {
			toast.warning('Please verify your two-factor authentication to continue.');
		}
	});

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
				const paramKey = paramName as keyof typeof params;
				const paramValue = params[paramKey];

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
	<Sidebar.Inset class="min-w-0 overflow-hidden">
		<header class="flex h-16 shrink-0 items-center gap-2 border-b px-4">
			<Sidebar.Trigger class="-ml-1" />
			<Separator orientation="vertical" class="mr-2 h-4" />
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
		</header>
		<div class="flex min-w-0 flex-1 flex-col gap-6 overflow-hidden px-6 py-4">
			{@render children()}
		</div>
	</Sidebar.Inset>
</Sidebar.Provider>
