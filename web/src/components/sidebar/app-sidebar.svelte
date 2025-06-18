<script lang="ts">
	import * as Sidebar from '$ui/sidebar';
	import NavMain from '$components/sidebar/nav-main.svelte';
	import NavUser from '$components/sidebar/nav-user.svelte';
	import OrgSwitcher from '$components/sidebar/org-switcher.svelte';
	import type { ComponentProps } from 'svelte';

	let {
		ref = $bindable(null),
		collapsible = 'icon',
		...restProps
	}: ComponentProps<typeof Sidebar.Root> = $props();
</script>

<Sidebar.Root {collapsible} {...restProps}>
	<Sidebar.Header>
		<OrgSwitcher />
	</Sidebar.Header>
	<Sidebar.Content>
		<NavMain />
	</Sidebar.Content>
	<Sidebar.Footer>
		<NavUser />
	</Sidebar.Footer>
	<Sidebar.Rail />
</Sidebar.Root>
