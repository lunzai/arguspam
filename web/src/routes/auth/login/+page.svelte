<script lang="ts">
	import LoginForm from "$components/auth/login-form.svelte";
	import type { PageData, ActionData } from './$types';

	let { data, form }: { data: PageData; form: ActionData } = $props();

	// Convert ActionData to the format expected by LoginForm
	const formData = form ? {
		email: form.email,
		error: form.error,
		errors: form.errors
	} : undefined;
</script>

<div class="bg-background flex min-h-svh flex-col items-center justify-center gap-6 p-6 md:p-10">
	<div class="w-full max-w-sm">
		<LoginForm form={formData} />
	</div>
</div>