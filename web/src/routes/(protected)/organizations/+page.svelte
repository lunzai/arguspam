<script lang="ts">
	import DataTable from './data-table.svelte';
	import { OrgColumnDef } from './columns';

	let { data } = $props();
	const { orgCollection } = data;
	const orgs = orgCollection?.data.map((org) => org.attributes) ?? [];
</script>

<h1 class="pb-4 text-2xl font-medium">Organizations</h1>

<DataTable data={orgs} columns={OrgColumnDef} />
