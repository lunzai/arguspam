<script lang="ts">
	import type { ApiAssetCollection } from '$lib/resources/asset.js';
	import type { Asset } from '$models/asset';
	import { PageTitle } from '$components/page-title';
	import GridView from './grid-view.svelte';

	let { data }: { data: any } = $props();
	let assetCollection = $derived(data.assetCollection as ApiAssetCollection);
	
    let list = $derived(assetCollection.data.map((item) => item.attributes) as Asset[]);
</script>

<PageTitle 
    title="Request Access" 
    description="Submit a time-bound privileged access request for a database asset. AI risk scoring is applied automatically before approval." 
/>

{#if list.length > 0}
	<GridView list={list} />
{:else}
	<div class="flex items-center justify-center h-full">
		<p class="text-slate-300 text-sm font-bold uppercase tracking-widest">No asset assigned to you.</p>
	</div>
{/if}
