<script lang="ts">
	import { Loader2 } from '@lucide/svelte/icons';
</script>

<div class="absolute inset-0 z-10 flex items-center justify-center bg-white/80 backdrop-blur-sm">
	<div class="flex flex-col items-center space-y-2">
		<Loader2 class="text-primary h-8 w-8 animate-spin" />
		<p class="text-sm text-gray-500">Loading...</p>
	</div>
</div>
