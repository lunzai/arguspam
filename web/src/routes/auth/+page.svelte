<script lang="ts">
	import { goto } from '$app/navigation';
	import { onMount } from 'svelte';
	import { PUBLIC_AUTH_LOGIN_PATH } from '$env/static/public';

	onMount(() => {
		goto(PUBLIC_AUTH_LOGIN_PATH);
	});
</script>

<div class="flex min-h-screen items-center justify-center">
	<div class="h-32 w-32 animate-spin rounded-full border-t-2 border-b-2 border-gray-900"></div>
</div>
