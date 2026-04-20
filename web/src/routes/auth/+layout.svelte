<script lang="ts">
	import Footer from '$components/footer';

	let { children } = $props();
</script>

<div class="min-h-screen bg-background flex flex-col">
    <div class="flex-1 flex flex-col">
        {@render children()}
    </div>
    <Footer />
</div>
